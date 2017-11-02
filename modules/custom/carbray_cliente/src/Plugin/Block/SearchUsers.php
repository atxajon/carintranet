<?php

namespace Drupal\carbray_cliente\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'SearchUsers' block.
 *
 * @Block(
 *  id = "search_users_block",
 *  admin_label = @Translation("Search users"),
 * )
 */
class SearchUsers extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $form = \Drupal::formBuilder()->getForm('Drupal\carbray_cliente\Form\SearchUsers');
    return $form;
  }
}
