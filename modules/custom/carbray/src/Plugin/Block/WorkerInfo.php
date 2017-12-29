<?php

namespace Drupal\carbray\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;



/**
 * Provides an WorkerInfo Block.
 *
 * @Block(
 *   id = "worker_info",
 *   admin_label = @Translation("Worker Info"),
 *   category = @Translation("Worker Info"),
 * )
 */
class WorkerInfo extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = User::load(\Drupal::currentUser()->id());
    $name = $user->get('field_nombre')->value .  '<br> ' . $user->get('field_apellido')->value;
    $department_tids = $user->get('field_departamento')->getValue();
    $department = '';
    if ($department_tids) {
      foreach ($department_tids as $department_tid) {
        $department_term = Term::load($department_tid['target_id']);
        $department .= $department_term->name->value . ' Dept. <br>';
      }
    }

    $build['worker_info'] = [
      '#markup' => '<div class="worker-info-container"><h1 class="text-center">' . $name . '</h1>' . '<h4 class="text-center">' . $department . '</h4></div>',
    ];
    $build['#cache']['max-age'] = 0;
    return $build;
  }
}