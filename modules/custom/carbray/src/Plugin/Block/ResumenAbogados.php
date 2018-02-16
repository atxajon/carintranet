<?php

namespace Drupal\carbray\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Render\Markup;



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

      // Captaciones activas are the ones that are not archived AND the ones that do not have an expediente yet.
      $count_captaciones_activas = \Drupal::database()->query('SELECT count(cc.entity_id)  
FROM users u 
INNER JOIN node__field_captacion_captador cc on cc.field_captacion_captador_target_id = u.uid 
INNER JOIN node__field_captacion_estado_captacion ec on ec.entity_id = cc.entity_id 
WHERE u.uid = :uid
AND cc.entity_id NOT IN (
	SELECT ec.field_expediente_captacion_target_id FROM node__field_expediente_captacion ec) 
AND field_captacion_estado_captacion_target_id != :estado_archived', array(':uid' => $worker->uid, ':estado_archived' => CAPTACION_ARCHIVADA))->fetchField();

      $count_captaciones_archivadas = \Drupal::database()->query('SELECT count(cc.entity_id)  
FROM users u 
INNER JOIN node__field_captacion_captador cc on cc.field_captacion_captador_target_id = u.uid 
INNER JOIN node__field_captacion_estado_captacion ec on ec.entity_id = cc.entity_id 
WHERE u.uid = :uid
AND ec.field_captacion_estado_captacion_target_id  = :estado_archived', array(':uid' => $worker->uid, ':estado_archived' => CAPTACION_ARCHIVADA))->fetchField();


      $count_expedientes_published = \Drupal::database()->query('SELECT count(er.field_expediente_responsable_target_id)
FROM users u
INNER JOIN user__roles ur on u.uid = ur.entity_id
INNER JOIN node__field_expediente_responsable er on er.field_expediente_responsable_target_id = u.uid
INNER JOIN node_field_data nfd on nfd.nid = er.entity_id
WHERE u.uid = :uid AND nfd.status = 1', array(':uid' => $worker->uid))->fetchField();

      $count_expedientes_archived = \Drupal::database()->query('SELECT count(er.field_expediente_responsable_target_id)
FROM users u
INNER JOIN user__roles ur on u.uid = ur.entity_id
INNER JOIN node__field_expediente_responsable er on er.field_expediente_responsable_target_id = u.uid
INNER JOIN node_field_data nfd on nfd.nid = er.entity_id
WHERE u.uid = :uid AND nfd.status = 0', array(':uid' => $worker->uid))->fetchField();


      $rows[] = array(
        $worker_name,
        $count_captaciones_activas,
        $count_captaciones_archivadas,
        $count_expedientes_published,
        $count_expedientes_archived,
      );
    }

    $header = array(
      'Nombre:',
      'Captaciones en curso',
      'Captaciones archivadas',
      'Expedientes en Curso',
      'Expedientes Archivados',
    );

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