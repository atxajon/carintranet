<?php

namespace Drupal\carbray\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;



/**
 * Provides a MiDepartamento Block.
 *
 * @Block(
 *   id = "ver_mi_departamento",
 *   admin_label = @Translation("Mi departamento"),
 *   category = @Translation("Trabajadores"),
 * )
 */
class MiDepartamento extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = User::load(\Drupal::currentUser()->id());
    $my_deptm = $user->get('field_departamento')->getValue();
    $my_deptm = $my_deptm[0]['target_id'];
    $departamento_term = Term::load($my_deptm);
    $my_workers = get_departamento_workers_with_names($my_deptm);

    foreach ($my_workers as $my_worker) {
      // Make worker name surname into a link.
      $url = Url::fromRoute('carbray.worker_home', ['uid' => $my_worker->uid]);
      $worker_name = Link::fromTextAndUrl($my_worker->name . ' ' . $my_worker->surname, $url);

      $count_captaciones_activas = get_count_captaciones_activas($my_worker->uid);
      $count_captaciones_archivadas = get_count_captaciones_archivadas($my_worker->uid);
      $count_expedientes_published = get_count_expedientes_published($my_worker->uid);
      $count_expedientes_archived = get_count_expedientes_archived($my_worker->uid);
      $count_facturas_emitidas = get_count_facturas_emitidas($my_worker->uid);
      $count_facturas_pagadas = get_count_facturas_pagadas($my_worker->uid);

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

    $build['deptm_name'] = [
      '#markup' => '<h3>' . $departamento_term->label() . '</h3>',
    ];
    $build['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }
}