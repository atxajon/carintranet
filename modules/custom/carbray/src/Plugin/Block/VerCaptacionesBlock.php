<?php

namespace Drupal\carbray\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Render\Markup;



/**
 * Provides an Gestionar trabajadores Block.
 *
 * @Block(
 *   id = "ver_captaciones",
 *   admin_label = @Translation("Ver captaciones trabajadores"),
 *   category = @Translation("Trabajadores"),
 * )
 */
class VerCaptacionesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $results = get_carbray_workers();
    foreach ($results as $worker_uid => $worker_email) {
      $user = User::load($worker_uid);
      $departamento_nombre = '';
      $departamento_terms = $user->get('field_departamento')->getValue();
      if ($departamento_terms) {
        foreach ($departamento_terms as $dep_term) {
          if ($dep_term['target_id']) {
            $term = Term::load($dep_term['target_id']);
            $departamento_nombre .= $term->name->value . Markup::create('<br>');
          }
        }
      }

      // Make worker name surname into a link.
      $url = Url::fromRoute('carbray.worker_home', ['uid' => $worker_uid]);
      $worker = Link::fromTextAndUrl($user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value, $url);

      $rows[] = array(
        $worker,
        Markup::create($departamento_nombre),
      );
    }

    $header = array(
      'Captaciones de:',
      'Departamento',
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