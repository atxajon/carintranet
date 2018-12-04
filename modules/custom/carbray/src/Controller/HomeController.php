<?php

namespace Drupal\carbray\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Render\Markup;

class HomeController extends ControllerBase {

  public function WorkerHome() {
    $build['prefix'] = [
      '#markup' => '<div class="block margin-bottom-20">',
    ];
    $form = \Drupal::formBuilder()->getForm('Drupal\carbray\Form\HomeDataForm', \Drupal::currentUser()->id());
    $build['form'] = [
      '#type' => 'markup',
      '#markup' => render($form),
    ];
    $build['suffix'] = [
      '#markup' => '</div>',
    ];

    // Obtain query string date values.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = [];
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }


    $logged_in_uid = \Drupal::currentUser()->id();
    $leads_captacion = get_my_clients_count($logged_in_uid, $query_array);
    $leads_produccion = get_my_clients_count($logged_in_uid, $query_array, 'produccion');

    $build['figures'] = array(
      '#theme' => 'figures_highlight',
      '#leads_recibidos_count' => $leads_captacion,
      '#leads_captacion_count' => $leads_captacion,
      '#leads_produccion_count' => $leads_produccion,
      '#facturacion_total' => 10002,
    );



    return $build;
  }
}
