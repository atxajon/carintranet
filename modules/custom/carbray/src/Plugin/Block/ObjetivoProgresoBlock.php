<?php

namespace Drupal\carbray\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides an Objetivos progreso Block.
 *
 * @Block(
 *   id = "objetivo_progreso",
 *   admin_label = @Translation("Objetivo progreso"),
 *   category = @Translation("Objetivo progreso"),
 * )
 */
class ObjetivoProgresoBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $db = \Drupal::database();
    $logged_in_worker_uid = \Drupal::currentUser()->id();
    
    // Get current objetivo for worker.
    // @todo this code is working for when only 1 objetivo assigned currently for a user;
    // need to test it when more are added; perhaps validate node submission when there is an existing objetivo for a user to alert admin on node creation??
    $now = date('Y-m-d H:i:s');
    $sql = "SELECT field_objetivo_cifra_value
            FROM node__field_objetivo_cifra c
            INNER JOIN node__field_objetivo_trabajador t on c.entity_id = t.entity_id
            INNER JOIN node__field_objetivo_fecha_inicio fe on c.entity_id = fe.entity_id
            INNER JOIN node__field_objetivo_fecha_final ff on c.entity_id = ff.entity_id
            WHERE t.field_objetivo_trabajador_target_id = :uid
            AND field_objetivo_fecha_inicio_value < :now
            AND field_objetivo_fecha_final_value > :now";
    $objetivo_cifra = $db->query($sql, array(':uid' => $logged_in_worker_uid, ':now' => $now))->fetchField();

    // Get total facturas for clients whose admin is the logged in user viewing this.
    // First get clients he's responsable of.
    $users_of_worker = \Drupal::entityQuery('user')
      ->condition('field_responsable', $logged_in_worker_uid)
      ->execute();
    $total_facturas = 0;
    foreach($users_of_worker as $user_of_worker) {
      // Get client's expediente to work out his bill cost.
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'expediente')
        ->condition('field_expediente_cliente', $user_of_worker);
      $expedientes = $query->execute();
      // If client has expedientes loop through them and look at their referenced factura costs.
      if ($expedientes) {
        foreach($expedientes as $expediente) {
          $sql = "SELECT field_cifra_factura_value FROM node__field_cifra_factura c INNER JOIN node__field_factura_expediente e ON c.entity_id = e.entity_id WHERE e.field_factura_expediente_target_id = :expediente_nid;";
          $factura_cifra = $db->query($sql, array(':expediente_nid' => $expediente))->fetchField(0);
          $total_facturas += $factura_cifra;
        }
      }
    }
    // @todo: remove hardcoded!
    $total_facturas = 3000.12;
    $total_facturas = (float)$total_facturas;
    $percent = $total_facturas / $objetivo_cifra * 100;
    // Don't let percent exceed 100% when objetivo is achieved.
    $percent = ($percent > 100) ? 100 : $percent;

    // @todo: Get current objetivo for departamento.

    return array(
      '#theme' => 'carbray_progress_bar',
      '#animate' => FALSE,
      '#large' => TRUE,
      '#percent' => $percent,
      '#objetivo_cifra' => $objetivo_cifra,
      '#facturado' => $total_facturas,
    );
  }
}