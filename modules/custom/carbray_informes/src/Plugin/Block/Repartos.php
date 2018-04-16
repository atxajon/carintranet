<?php

namespace Drupal\carbray_informes\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Informes' admin block.
 *
 * @Block(
 *  id = "repartos",
 *  admin_label = @Translation("Repartos"),
 * )
 */
class Repartos extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
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
        'y' => (float)$procedencia_cliente->amount_count,
        'percent' => (float)round($procedencia_cliente->percent, 2),
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

    $markup = '<h3>Reparto</h3><div id="procedencia-chart"></div>';

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

    return $build;
  }

}
