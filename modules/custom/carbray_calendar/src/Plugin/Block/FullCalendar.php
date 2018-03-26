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


    $nids = \Drupal::entityQuery('node')->condition('type','actuacion')->execute();
    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    $data = [];
    foreach ($nodes as $node) {
      $expediente_nid = $node->get('field_actuacion_expediente')->getValue();

      // Work out actuacion start and end date;
      // We only have actuacion form submission time (node created value in timestamp)
      // and minutes passed declared by the user.
      // Start and end date can be misleading as often times a worker submits 2 actuaciones for say 30 mins one after the other.
      // They plot on the calendar as overlapping time...
      $actuacion_minutes = $node->get('field_actuacion_tiempo_en_seg')->value;
      $actuacion_seconds = $actuacion_minutes * 60;
      $actuacion_created = $node->created->value;
      $actuacion_started = $actuacion_created - $actuacion_seconds;

      // @todo: get expediente responsable's department.
      $data[] = [
        'title' => $node->label(),
        'start' => date("c", $actuacion_started), //converting tiemstamp to ISO 8601 https://stackoverflow.com/questions/5322285/how-do-i-convert-datetime-to-iso-8601-in-php/5322309
        'end' => date("c", $actuacion_created),
        'url' => Url::fromRoute('entity.node.canonical', ['node' => $expediente_nid[0]['target_id']]
        )->toString(),
        'dept' => 186,
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