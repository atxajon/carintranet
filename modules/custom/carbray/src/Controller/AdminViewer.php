<?php

namespace Drupal\carbray\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Render\Markup;

class AdminViewer extends ControllerBase {

  public function WorkerHome($uid) {
    $clientes = get_my_clients($uid);
    $build['prefix'] = [
      '#markup' => '<div class="block"><h2>Contactos en captacion</h2>',
    ];
    $build['contactos_captacion'] = contactos_captacion_content($clientes);
    $build['suffix'] = [
      '#markup' => '</div>',
    ];
    return $build;
  }
}
