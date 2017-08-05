<?php

namespace Drupal\carbray\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an Gestionar trabajadores Block.
 *
 * @Block(
 *   id = "gestionar_trabajadores",
 *   admin_label = @Translation("Gestionar trabajadores"),
 *   category = @Translation("Trabajadores"),
 * )
 */
class GestionarTrabajadoresBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $results = db_query('SELECT field_nombre_value as nombre, field_apellido_value as apellido, mail, field_departamento_target_id as tid FROM users_field_data ufd INNER JOIN user__roles ur ON ufd.uid = ur. entity_id LEFT JOIN user__field_nombre n on n.entity_id = ufd.uid LEFT JOIN user__field_apellido a on a.entity_id = ufd.uid LEFT JOIN user__field_departamento d on d.entity_id = ufd.uid LEFT JOIN node__field_objetivo_trabajador ot on ot.field_objetivo_trabajador_target_id = ufd.uid')->fetchAll();
    foreach ($results as $result) {
      if ($result->tid) {
        $departamento_term = \Drupal::entityTypeManager()
          ->getStorage('taxonomy_term')
          ->load($result->tid);
      }

      $rows[] = array(
        $result->nombre . ' ' . $result->apellido,
        $result->mail,
        ($result->tid) ? $departamento_term->name->value : '',
//        $result->cifra,
      );
    }

    $header = array(
      'Nombre',
      'Email',
      'Departamento',
      'Objetivo cifra',
    );

    return array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    );
  }
}