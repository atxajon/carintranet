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
      // @todo: get expediente responsable's department.
      $data[] = [
        'title' => $node->label(),
        'start' => date('Y-m-d', $node->created->value),
        'url' => Url::fromRoute('entity.node.canonical', ['node' => $expediente_nid[0]['target_id']]
        )->toString(),
        'dept' => 186,
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