<?php

namespace Drupal\carbray_calendar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\user\Entity\User;
use Drupal\Core\Url;


/**
 * Provides an FullCalendar Block.
 *
 * @Block(
 *   id = "full_calendar",
 *   admin_label = @Translation("Full Calendar"),
 *   category = @Translation("Full Calendar"),
 * )
 */
class FullCalendar extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $form = \Drupal::formBuilder()
      ->getForm('Drupal\carbray_calendar\Form\NewCita');
    $build['anadir_cita'] = [
      '#theme' => 'button_modal',
      '#unique_id' => 'anadir-cita',
      '#button_text' => 'Crear cita',
      '#button_classes' => 'btn btn-primary margin-bottom-20',
      '#modal_title' => t('Nueva cita'),
      '#modal_content' => $form,
      '#has_plus' => TRUE,
    ];

    $current_user_roles = \Drupal::currentUser()->getRoles();
    $uid = \Drupal::currentUser()->id();
    $tid = [];

    if (in_array('administrator', $current_user_roles) || in_array('carbray_administrator', $current_user_roles)) {
      // An admin sees the edit colours form link.
      $build['edit_colours'] = [
        '#markup' => '<a class="btn btn-primary pull-right" href="/editar-colores">Editar colores</a>',
      ];

      // A carbray admin queries for all calendar data.
      $uid = 0;
      // And displays filters.
      $form = \Drupal::formBuilder()
        ->getForm('Drupal\carbray_calendar\Form\CalendarFilters', $current_user_roles);
      $build['filters'] = [
        '#markup' => render($form),
      ];
    }
    elseif (in_array('jefe_departamento', $current_user_roles)) {
      // Jefe departamento queries for their department data.
      $user = User::load(\Drupal::currentUser()->id());
      $my_deptms = $user->get('field_departamento')->getValue();
      foreach ($my_deptms as $my_deptm) {
        $tid[] = $my_deptm['target_id'];
      }
      $form = \Drupal::formBuilder()
        ->getForm('Drupal\carbray_calendar\Form\CalendarFilters', $current_user_roles, $tid);
      $build['filters'] = [
        '#markup' => render($form),
      ];
    }

    $actuaciones = get_calendar_actuaciones($uid, $tid);

    $current_iteration_nid = 0;
    $data = [];
    foreach ($actuaciones as $actuacion) {

      /**
       * The actuaciones sql query returns duplicated data, because a user can be in multiple departments;
       * If they do they return an actuacion row for each department they're in.
       * This could be fixed before mysql 5.7 with 'group by = nid', but now they're enforcing ONLY_FULL_GROUP_BY
       * and each column needs to be thrown into group by clause. Couldn't get it to work,
       * so an (ugly) workaround is to check if the current iteration nid is already part of the result set,
       * and if it is -> skip to next row iteration.
       */
      if ($current_iteration_nid == $actuacion->nid) {
        continue;
      }

      // Work out actuacion start and end date;
      // We only have actuacion form submission time (node created value in timestamp)
      // and minutes passed declared by the user.
      // Start and end date can be misleading as often times a worker submits 2 actuaciones for say 30 mins one after the other.
      // They plot on the calendar as overlapping time...
      $actuacion_minutes = $actuacion->minutes;
      $actuacion_seconds = $actuacion_minutes * 60;
      $actuacion_created = $actuacion->created;
      $actuacion_started = $actuacion_created - $actuacion_seconds;

      $data[] = [
        'title' => $actuacion->title,
        'start' => date("c", $actuacion_started), // converting tiemstamp to ISO 8601 https://stackoverflow.com/questions/5322285/how-do-i-convert-datetime-to-iso-8601-in-php/5322309
        'end' => date("c", $actuacion_created),
        'created' => date('d-m-Y H:m:s', $actuacion->created),
        'url' => Url::fromRoute('entity.node.canonical', ['node' => $actuacion->expediente_nid]
        )->toString(),
        'dept_id' => $actuacion->departamento_tid,
        'dept' => $actuacion->departamento,
        'color' => DEPARTMENT_COLOURS[$actuacion->departamento_tid],
        'author' => $actuacion->nombre . ' ' . $actuacion->apellido,
        'author_uid' => $actuacion->author,
//        'allDay' => false,
      ];
      $current_iteration_nid = $actuacion->nid;
    }

    $build['fullcalendar'] = [
      '#markup' => '<div id="calendar"></div>',
      '#attached' => array(
        'library' => array(
          'carbray_calendar/fullcalendar',
        ),
        'drupalSettings' => array(
          'data' => $data,
        ),
      ),
    ];

    $build['#cache']['max-age'] = 0;
    return $build;
  }
}