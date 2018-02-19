<?php

namespace Drupal\carbray\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Render\Markup;

class AdminViewer extends ControllerBase {

  public function WorkerHome($uid) {
    $clientes = get_my_clients($uid);
    $build['captacion_prefix'] = [
      '#markup' => '<div class="block margin-bottom-20"><h2>Contactos en captacion</h2>',
    ];
    $build['contactos_captacion'] = contactos_captacion_content($clientes);
    $build['suffix'] = [
      '#markup' => '</div>',
    ];
    $build['expedientes_prefix'] = [
      '#markup' => '<div class="block"><h2>Clientes en produccion</h2>',
    ];

    $clientes = get_my_clients($uid, 'produccion');
    $build['contactos_expedientes'] = clientes_expedientes_content($clientes);
    $build['expedientes_suffix'] = [
      '#markup' => '</div>',
    ];
    return $build;
  }
}
