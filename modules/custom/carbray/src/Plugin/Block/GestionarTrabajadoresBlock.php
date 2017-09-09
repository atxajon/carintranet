<?php

namespace Drupal\carbray\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

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

    $results = db_query('SELECT uid, field_nombre_value as nombre, field_apellido_value as apellido, mail, field_departamento_target_id as tid, roles_target_id as role, status FROM users_field_data ufd INNER JOIN user__roles ur ON ufd.uid = ur. entity_id LEFT JOIN user__field_nombre n on n.entity_id = ufd.uid LEFT JOIN user__field_apellido a on a.entity_id = ufd.uid LEFT JOIN user__field_departamento d on d.entity_id = ufd.uid WHERE ufd.status = 1 AND uid != 1')->fetchAll();
    foreach ($results as $result) {
      if ($result->tid) {
        $departamento_term = \Drupal::entityTypeManager()
          ->getStorage('taxonomy_term')
          ->load($result->tid);
      }
      $objetivo = db_query("SELECT field_objetivo_cifra_value as cifra FROM node__field_objetivo_cifra c INNER JOIN node__field_objetivo_trabajador t on c.entity_id = t.entity_id  INNER JOIN node__field_objetivo_fecha_inicio fe on c.entity_id = fe.entity_id
            INNER JOIN node__field_objetivo_fecha_final ff on c.entity_id = ff.entity_id WHERE field_objetivo_trabajador_target_id = :uid AND field_objetivo_fecha_inicio_value < :now
            AND field_objetivo_fecha_final_value > :now", array(':uid' => $result->uid, ':now' => date('Y-m-d H:i:s')))->fetchField();
      // No objetivo cifra? add a link to create new one for this user.
      if (!$objetivo) {
        $options = [
          'query' => [
            'uid' => $result->uid,
          ],
          'attributes' => [
            'class' => [
              'small',
            ],
          ]
        ];
        $url = Url::fromRoute('carbray.add_objetivo_form', [], $options);
        $objetivo = Link::fromTextAndUrl('Añadir objetivo', $url);
      }
      else {
        $objetivo = number_format($objetivo, 2, ',', '.') . '€';
      }

      // Make worker name surname into a link.
      $url = Url::fromRoute('entity.user.canonical', ['user' => $result->uid]);
      $worker = Link::fromTextAndUrl($result->nombre . ' ' . $result->apellido, $url);

      if ($result->role == 'carbray_administrator') {
        $result->role = 'Administrador';
      }
      if ($result->role == 'worker') {
        $result->role = 'Trabajador';
      }
      $rows[] = array(
        $worker,
        $result->mail,
        ($result->tid) ? $departamento_term->name->value : '',
        $objetivo,
        $result->role,
        $result->status,
      );
    }

    $header = array(
      'Nombre',
      'Email',
      'Departamento',
      'Objetivo actual',
      'Rol',
      'Estado',
    );

    return array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    );
  }
}