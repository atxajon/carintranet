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
      ->getForm('Drupal\carbray_calendar\Form\CalendarFilters');
    $build['filters'] = [
      '#markup' => render($form),
    ];

    $current_user_roles = \Drupal::currentUser()->getRoles();
    if (in_array('carbray_administrator', $current_user_roles)) {
      // A carbray admin queries for all calendar data.
      $uid = 0;
    }
    else {
      // A non carbray admin queries for only their specific calendar data.
      $uid = \Drupal::currentUser()->id();
    }

    $actuaciones = get_calendar_actuaciones($uid);

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
      $color = 'black';
      if ($actuacion->departamento_tid == 185) {
        $color = 'black';
      }
      elseif ($actuacion->departamento_tid == 186) {
        $color = 'green';
      }
      elseif ($actuacion->departamento_tid == 187) {
        $color = 'blue';
      }
      elseif ($actuacion->departamento_tid == 188) {
        $color = 'red';
      }
      elseif ($actuacion->departamento_tid == 189) {
        $color = 'indigo';
      }
      elseif ($actuacion->departamento_tid == 201) {
        $color = 'pink';
      }

      $data[] = [
        'title' => $actuacion->title,
        'start' => date("c", $actuacion_started), // converting tiemstamp to ISO 8601 https://stackoverflow.com/questions/5322285/how-do-i-convert-datetime-to-iso-8601-in-php/5322309
        'end' => date("c", $actuacion_created),
        'created' => date('d-m-Y H:m:s', $actuacion->created),
        'url' => Url::fromRoute('entity.node.canonical', ['node' => $actuacion->expediente_nid]
        )->toString(),
        'dept_id' => $actuacion->departamento_tid,
        'dept' => $actuacion->departamento,
        'color' => $color,
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