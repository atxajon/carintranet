<?php

namespace Drupal\carbray\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;



/**
 * Provides a MiDepartamento Block.
 *
 * @Block(
 *   id = "departamentos",
 *   admin_label = @Translation("Departamentos"),
 *   category = @Translation("Trabajadores"),
 * )
 */
class Departamentos extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $db = \Drupal::database();
    $sql = "SELECT tid FROM taxonomy_term_field_data WHERE vid= 'departamento'";
    $departamentos_tids = $db->query($sql)->fetchCol();

    foreach ($departamentos_tids as $departamento_tid) {
      $departamento_term = Term::load($departamento_tid);
      $url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $departamento_tid]);
      $dept_link = Link::fromTextAndUrl($departamento_term->label(), $url);

      // @todo: find the jefe.

      // @todo: load a form that allows changing workers in a departamento and make them jefes.

      $rows[] = array(
        $dept_link,
        'jefe',
        'Editar jefe',
      );
    }

    $header = array(
      'Departamento',
      'Jefe',
      'Asignar jefe',
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