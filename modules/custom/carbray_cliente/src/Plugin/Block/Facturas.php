<?php

namespace Drupal\carbray_cliente\Plugin\Block;

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
    $form = \Drupal::formBuilder()->getForm('Drupal\carbray_cliente\Form\FacturasForm');
    return $form;
  }
}
