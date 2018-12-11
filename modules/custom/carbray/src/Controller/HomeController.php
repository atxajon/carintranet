<?php

namespace Drupal\carbray\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

class HomeController extends ControllerBase {

  public function WorkerHome() {
    $logged_in_uid = \Drupal::currentUser()->id();
    return get_home_content($logged_in_uid);
  }
}
