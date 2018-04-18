<?php

namespace Drupal\carbray_informes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\taxonomy\Entity\Term;


class InformesController extends ControllerBase {

  public function resumen() {
    $build['#attached']['library'][] = 'carbray/tablesorter';
    $build['#attached']['library'][] = 'carbray/carbray_table_sorter';

    $build['div_open'] = [
      '#markup' => '<div class="admin-block">',
    ];

    // Obtain query string parameters to pass them in to the queries.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = array();
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }
    $workers = \Drupal::database()->query('SELECT n.entity_id as uid, field_nombre_value as name, field_apellido_value as surname 
FROM user__field_nombre n 
INNER JOIN users_field_data ufd on ufd.uid = n.entity_id
INNER JOIN user__field_apellido a on n.entity_id = a.entity_id 
INNER JOIN user__roles ur on n.entity_id = ur.entity_id 
WHERE ufd.status = 1
ORDER BY field_apellido_value ASC')->fetchAll();

    foreach ($workers as $worker) {
      // Make worker name surname into a link.
      $url = Url::fromRoute('carbray.worker_home', ['uid' => $worker->uid]);
      $worker_name = Link::fromTextAndUrl($worker->name . ' ' . $worker->surname, $url);

      $count_captaciones_activas = get_count_captaciones_activas($worker->uid, $query_array);
      $count_captaciones_archivadas = get_count_captaciones_archivadas($worker->uid, $query_array);
      $count_expedientes_published = get_count_expedientes_published($worker->uid, $query_array);
      $count_expedientes_archived = get_count_expedientes_archived($worker->uid, $query_array);
      $count_facturas_emitidas = get_count_facturas_emitidas($worker->uid, $query_array);
      $count_facturas_pagadas = get_count_facturas_pagadas($worker->uid, $query_array);

      $rows[] = array(
        $worker_name,
        $count_captaciones_activas,
        $count_captaciones_archivadas,
        $count_expedientes_published,
        $count_expedientes_archived,
        $count_facturas_emitidas,
        $count_facturas_pagadas,
      );
    }

    $header = array(
      'Nombre:',
      'Captaciones en curso',
      'Captaciones archivadas',
      'Expedientes en Curso',
      'Expedientes Archivados',
      'Facturas emitidas',
      'Facturas pagadas',
    );

    $filters_form = \Drupal::formBuilder()
      ->getForm('Drupal\carbray_informes\Form\InformeAbogadosFilters');
    $build['filters'] = [
      '#markup' => render($filters_form),
    ];
    $build['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => ['id' => 'resumen-abogados', 'class' => ['tablesorter']],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    $build['div_close'] = [
      '#markup' => '</div>',
    ];

    return $build;
  }

  public function procedencia() {
    $build['div_open'] = [
      '#markup' => '<div class="admin-block">',
    ];

    // Obtain query string parameters to pass them in to the queries.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = array();
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }


    $procedencia_clientes = get_informe_procedencia_count($query_array);

    $rows = [];
    foreach ($procedencia_clientes as $procedencia_cliente) {
      $rows[] = [
        'name' => ucfirst($procedencia_cliente->name),
        'y' => (float) $procedencia_cliente->amount_count,
        'percent' => (float) round($procedencia_cliente->percent, 2),
      ];
    }

//    $procedencia_clientes = get_informe_procedencia();
//    $current_iteration_cliente_uid = 0;
//    foreach ($procedencia_clientes as $procedencia_cliente) {
//      /**
//       * The query returns duplicated data;
//       * This could be fixed before mysql 5.7 with 'group by = cliente_uid', but now they're enforcing ONLY_FULL_GROUP_BY
//       * and each column needs to be thrown into group by clause. Couldn't get it to work,
//       * so an (ugly) workaround is to check if the current iteration uid is already part of the result set,
//       * and if it is -> skip to next row iteration.
//       */
//      if ($current_iteration_cliente_uid == $procedencia_cliente->cliente_uid) {
//        continue;
//      }
//
//      $rows[] = [
//        'procedencia' => ucfirst($procedencia_cliente->name),
//        'created' => $procedencia_cliente->created,
//        'cliente_uid' => $procedencia_cliente->cliente_uid,
//        'captador_uid' => $procedencia_cliente->captador_uid,
//        'captacion_nid' => $procedencia_cliente->captacion_nid,
//        'dept_tid' => $procedencia_cliente->dept_tid,
//      ];
//      $current_iteration_cliente_uid = $procedencia_cliente->cliente_uid;
//    }

    $markup = '<h3>Reparto</h3><div id="chart"></div>';

    $filters_form = \Drupal::formBuilder()
      ->getForm('Drupal\carbray_informes\Form\InformeProcedenciaFilters');
    $build['filters'] = [
      '#markup' => render($filters_form),
    ];

    $build['chart'] = array(
      '#markup' => $markup,
      '#attached' => array(
        'library' => array(
          'carbray_informes/highcharts',
          'carbray_informes/exporting',
          'carbray_informes/procedencia_piechart',
        ),
        // Pass php var content to js.
        'drupalSettings' => array(
          'procedencia_data' => $rows,
        ),
      ),
    );
    $build['div_close'] = [
      '#markup' => '</div>',
    ];

    return $build;
  }

  function tematica() {
    $build['div_open'] = [
      '#markup' => '<div class="admin-block">',
    ];

    // Obtain query string parameters to pass them in to the queries.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = array();
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }

    $servicios = get_informe_servicios_count($query_array);

    $servicios_data = [];
    $tematicas_tids = [];
    foreach ($servicios as $servicio) {
      // Skip if somebody accidentally tagged an expediente with tematica (1st level parent term in hierarchy) and not servicio (2nd level or child of tematica.
      // Especially valid for old expedientes migrated that may not have been tagged with this hierarchy.
      if ($servicio->parent == 0) {
        continue;
      }
      // Format the array as Highcharts expects it: http://jsfiddle.net/gh/get/library/pure/highcharts/highcharts/tree/master/samples/highcharts/demo/pie-drilldown/
      $servicios_data[] = [
        'name' => ucfirst($servicio->name),
        'id' => $servicio->parent,
        'data' => [
          [
            ucfirst($servicio->name),
            (float) $servicio->amount_count,
          ],
        ],
      ];
      $tematicas_tids[] = $servicio->parent;
    }

    $tematicas = array_count_values($tematicas_tids);
    $count_tematicas_total = count($tematicas_tids);
    foreach ($tematicas as $tematica_tid => $tematica_amount) {
      $term = Term::load($tematica_tid);
      if (!$term) {
        continue;
      }
      $tematicas_data[] = [
        'name' => ucfirst($term->getName()),
        'y' => (float) $tematica_amount,
        'percent' => (float) round($tematica_amount / $count_tematicas_total * 100, 2),
        'drilldown' => (string)$tematica_tid,
      ];
      $servicios_for_tematica = get_informe_servicios_for_tematica($tematica_tid, $query_array);
      $servicios_drilldown_series = [];
      foreach ($servicios_for_tematica as $servicio_for_tematica) {
        $servicios_drilldown_series[] = [
          $servicio_for_tematica->name,
          (int) $servicio_for_tematica->amount_count,
        ];
      }
      $highcharts_drilldown_series[] = [
        'name' => ucfirst($term->getName()),
        'id' => (string)$tematica_tid,
        'data' => $servicios_drilldown_series,
      ];
    }

    $markup = '<h3>Reparto por tematica/servicios</h3><div id="chart"></div>';

    $filters_form = \Drupal::formBuilder()
      ->getForm('Drupal\carbray_informes\Form\InformeProcedenciaFilters');
    $build['filters'] = [
      '#markup' => render($filters_form),
    ];

    $build['chart'] = array(
      '#markup' => $markup,
      '#attached' => array(
        'library' => array(
          'carbray_informes/highcharts',
          'carbray_informes/exporting',
          'carbray_informes/drilldown',
          'carbray_informes/tematicas_piechart',
        ),
        // Pass php var content to js.
        'drupalSettings' => array(
          'tematicas_data' => $tematicas_data,
          'servicios_data' => $highcharts_drilldown_series,
        ),
      ),
    );
    $build['div_close'] = [
      '#markup' => '</div>',
    ];

    return $build;
  }

}
