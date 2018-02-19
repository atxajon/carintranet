<?php

namespace Drupal\carbray_cliente\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ClientesProduccion' block.
 *
 * @Block(
 *  id = "clientes_produccion",
 *  admin_label = @Translation("Clientes produccion"),
 * )
 */
class ClientesProduccion extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $logged_in_uid = \Drupal::currentUser()->id();
    $clientes = get_my_clients($logged_in_uid, 'produccion');
    $build = clientes_expedientes_content($clientes);
    return $build;
  }
}
