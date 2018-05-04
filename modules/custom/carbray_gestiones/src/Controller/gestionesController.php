<?php

namespace Drupal\carbray_gestiones\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Render\Markup;
use Drupal\user\Entity\User;

class gestionesController extends ControllerBase {

  public function trabajadores() {
    $db = \Drupal::database();
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

      $sql = "SELECT field_objetivo_cifra_value as cifra FROM node__field_objetivo_cifra c INNER JOIN node__field_objetivo_trabajador t on c.entity_id = t.entity_id  INNER JOIN node__field_objetivo_fecha_inicio fe on c.entity_id = fe.entity_id
            INNER JOIN node__field_objetivo_fecha_final ff on c.entity_id = ff.entity_id WHERE field_objetivo_trabajador_target_id = :uid AND field_objetivo_fecha_inicio_value < :now
            AND field_objetivo_fecha_final_value > :now";

      $objetivo = $db->query($sql, array(':uid' => $worker_uid, ':now' => date('Y-m-d H:i:s')))->fetchField();
      // No objetivo cifra? add a link to create new one for this user.
      if (!$objetivo) {
        $options = [
          'query' => [
            'uid' => $worker_uid,
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
      $url = Url::fromRoute('entity.user.canonical', ['user' => $worker_uid]);
      $worker = Link::fromTextAndUrl($user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value, $url);

      // Make captaciones link.
      $captaciones_url = Url::fromRoute('carbray.worker_home', ['uid' => $worker_uid]);
      $captaciones_link = Link::fromTextAndUrl('Ver captaciones', $captaciones_url);

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
      if ($user->hasRole('jefe_departamento')) {
        $role = 'Jefe Departamento';
      }
      $rows[] = array(
        $worker,
        $captaciones_link,
        $user->getEmail(),
        Markup::create($departamento_nombre),
        $objetivo,
        $role,
        ($user->status->value == 1) ? t('Activo') : t('Inactivo'),
      );
    }

    $header = array(
      'Nombre',
      'Captaciones',
      'Email',
      'Departamento',
      'Objetivo actual',
      'Rol',
      'Estado',
    );

    $build['open'] = [
      '#markup' => '<div class="admin-block shadow">',
    ];
    $build['new_worker_link'] = [
      '#markup' => '<a href="/crear-trabajador" class="btn btn-primary margin-bottom-20 margin-top-20"><span class="glyphicon glyphicon-plus-sign"></span>Crear nuevo trabajador</a>',
    ];
    $build['table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    $build['close'] = [
      '#markup' => '</div>',
    ];

    return $build;
  }

  public function plantillas() {
    $block = \Drupal::entityTypeManager()->getStorage('block')->load('views_block__propuesta_plantillas_block_1');
    $build['myblock'] =  \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
    return $build;
  }
}
