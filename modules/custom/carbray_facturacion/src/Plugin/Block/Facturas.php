<?php

namespace Drupal\carbray_facturacion\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Facturas sin pagar' block.
 *
 * @Block(
 *  id = "facturas",
 *  admin_label = @Translation("Facturas sin pagar"),
 * )
 */
class Facturas extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\carbray_facturacion\Form\FacturasForm');
    return $form;
  }
}
