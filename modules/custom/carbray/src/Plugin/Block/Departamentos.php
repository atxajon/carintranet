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

      $jefe_uid = \Drupal::database()->query("SELECT uid
FROM users_field_data ufd
INNER JOIN user__roles ur on ufd.uid = ur.entity_id
INNER JOIN user__field_departamento d on d.entity_id = ufd.uid
WHERE roles_target_id = 'jefe_departamento'
AND field_departamento_target_id = :tid", [':tid' => $departamento_tid])->fetchField();

      $jefe_user = ($jefe_uid) ? User::load($jefe_uid) : '';

      // @todo: load a form that allows changing workers in a departamento and make them jefes.

      $rows[] = array(
        $dept_link,
        ($jefe_user) ? $jefe_user->get('field_nombre')->value . ' ' . $jefe_user->get('field_apellido')->value : '',
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