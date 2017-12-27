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
    $db = \Drupal::database();
    $sql = "SELECT uid FROM users_field_data ufd WHERE ufd.status = 1 AND uid != 1 AND uid != 0";

    $results = $db->query($sql)->fetchAll();
    foreach ($results as $worker) {
      $user = User::load($worker->uid);
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

      $sql = "SELECT field_objetivo_cifra_value as cifra FROM node__field_objetivo_cifra c INNER JOIN node__field_objetivo_trabajador t on c.entity_id = t.entity_id  INNER JOIN node__field_objetivo_fecha_inicio fe on c.entity_id = fe.entity_id
            INNER JOIN node__field_objetivo_fecha_final ff on c.entity_id = ff.entity_id WHERE field_objetivo_trabajador_target_id = :uid AND field_objetivo_fecha_inicio_value < :now
            AND field_objetivo_fecha_final_value > :now";

      $objetivo = $db->query($sql, array(':uid' => $worker->uid, ':now' => date('Y-m-d H:i:s')))->fetchField();
      // No objetivo cifra? add a link to create new one for this user.
      if (!$objetivo) {
        $options = [
          'query' => [
            'uid' => $worker->uid,
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
      $url = Url::fromRoute('entity.user.canonical', ['user' => $worker->uid]);
      $worker = Link::fromTextAndUrl($user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value, $url);

      $role = '';
      if ($user->hasRole('carbray_administrator')) {
        $role = 'Administrador';
      }
      if ($user->hasRole('worker')) {
        $role = 'Trabajador';
      }
      if ($user->hasRole('secretaria')) {
        $role = 'Secretaria';
      }
      $rows[] = array(
        $worker,
        $user->getEmail(),
        Markup::create($departamento_nombre),
        $objetivo,
        $role,
        ($user->status->value == 1) ? t('Activo') : t('Inactivo'),
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
      '#cache' => [
        'max-age' => 0,
      ],
    );
  }
}