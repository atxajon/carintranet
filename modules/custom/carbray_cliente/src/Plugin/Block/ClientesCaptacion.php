<?php

namespace Drupal\carbray_cliente\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;


/**
 * Provides a 'ClientesCaptacion' block.
 *
 * @Block(
 *  id = "clientes_captacion",
 *  admin_label = @Translation("Clientes captacion"),
 * )
 */
class ClientesCaptacion extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $logged_in_uid = \Drupal::currentUser()->id();
    $clientes = get_my_clients($logged_in_uid);
    $build = contactos_captacion_content($clientes);
    return $build;
  }
}
