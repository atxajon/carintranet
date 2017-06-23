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
    // Get current objetivo.
//    $objetivo_nids = \Drupal::entityQuery('node')
//        ->condition('type','objetivos')
//        ->condition('status', 1)
//        ->execute();
//    $objetivo_nodes =  Node::loadMultiple($objetivo_nids);

    // @todo: replace hardcoded val with dynamic selection of valid and active Objetivo.
    $objetivo_node = Node::load(6);
    $objetivo_cifra = $objetivo_node->get('field_objetivo_cifra')->value;
    $objetivo_cifra = (float)$objetivo_cifra;

    // Get total facturas for clients whose admin is viewing this.
    // Get first the uid of currently logged in user.
    $logged_in_worker_uid = \Drupal::currentUser()->id();
    // Then get clients he's responsable of.
    $users_of_worker = \Drupal::entityQuery('user')
        ->condition('field_responsable', $logged_in_worker_uid)
        ->execute();
    $total_facturas = 0;
    $db = \Drupal::database();
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