<?php

namespace Drupal\carbray\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;


/**
 * Provides a ResumenAbogados Block.
 *
 * @Block(
 *   id = "ver_captaciones",
 *   admin_label = @Translation("Resumen abogados"),
 *   category = @Translation("Trabajadores"),
 * )
 */
class ResumenAbogados extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build['#attached']['library'][] = 'carbray/tablesorter';
    $build['#attached']['library'][] = 'carbray/carbray_table_sorter';

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

      $count_captaciones_activas = get_count_captaciones_activas($worker->uid);
      $count_captaciones_archivadas = get_count_captaciones_archivadas($worker->uid, $query_array);
      $count_expedientes_published = get_count_expedientes_published($worker->uid, $query_array);
      $count_expedientes_archived = get_count_expedientes_archived($worker->uid, $query_array);
      $count_facturas_emitidas = get_count_facturas_emitidas($worker->uid);
      $count_facturas_pagadas = get_count_facturas_pagadas($worker->uid);

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
      ->getForm('Drupal\carbray\Form\ResumenAbogadosFilters');
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

    return $build;
  }
}