<?php

namespace Drupal\carbray\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Render\Markup;

class AdminViewer extends ControllerBase {

  public function WorkerHome($uid) {
    // Pull out Home page content not for the logged in user,
    // but rather the uid collected on path /user/{uid}/home,
    // as this route is reserved for an admin viewing a user's content
    // on their behalf.
    return get_home_content($uid);
  }
}
