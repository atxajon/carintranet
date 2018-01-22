<?php

namespace Drupal\carbray_calendar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\user\Entity\User;



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
    $user = User::load(\Drupal::currentUser()->id());


    $build['fullcalendar'] = [
      '#markup' => '<form id="school"><select id=\'school_selector\'>
      <option value=\'all\'>All schools</option>
      <option value=\'1\'>school 1</option>
      <option value=\'2\'>school 2</option>
</select></form><div id="calendar"></div>',
      '#attached' => array(
        'library' => array(
          'carbray_calendar/fullcalendar',
        ),
      ),
    ];
    $build['#cache']['max-age'] = 0;
    return $build;
  }
}