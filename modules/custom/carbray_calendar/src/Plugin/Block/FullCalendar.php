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

    $actuaciones = \Drupal::database()->query("SELECT field_actuacion_tiempo_en_seg_value as minutes, nid, field_actuacion_expediente_target_id as expediente_nid, created, title, field_departamento_target_id as departamento_tid
FROM node_field_data nfd
INNER JOIN node__field_actuacion_expediente ac on nfd.nid = ac.entity_id
INNER JOIN node__field_actuacion_tiempo_en_seg t on nfd.nid = t.entity_id
INNER JOIN user__field_departamento d on nfd.uid = d.entity_id
WHERE type = 'actuacion'")->fetchAll();
    $data = [];
    foreach ($actuaciones as $actuacion) {

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
        'url' => Url::fromRoute('entity.node.canonical', ['node' => $actuacion->expediente_nid]
        )->toString(),
        'dept' => $actuacion->departamento_tid,
        'color' => $color,
//        'allDay' => false,
      ];
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