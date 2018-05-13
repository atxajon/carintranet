<?php

namespace Drupal\carbray_facturacion\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Render\Markup;

class Facturacion extends ControllerBase {

  public function Excel() {
    $build['pre'] = [
      '#markup' => '<div class="admin-block">',
    ];

    $new_registro_form = \Drupal::formBuilder()
      ->getForm('Drupal\carbray_facturacion\Form\NewRegistroForm');
    $build['new_registro'] = [
      '#theme' => 'button_modal',
      '#unique_id' => 'anadir-nuevo-registro',
      '#button_text' => 'Nuevo Registro',
      '#button_classes' => 'btn btn-primary',
      '#modal_title' => t('Nuevo registro'),
      '#modal_content' => $new_registro_form,
      '#has_plus' => TRUE,
    ];

    $build['tabla_excel_facturacion'] = [
      '#markup' => '<h2>Excel facturacion</h2>',
    ];
    $build['post'] = [
      '#markup' => '</div>',
    ];
    return $build;
  }

  public function newRegistro($factura_nid = 0, $captacion_nid = 0) {
    $build['pre'] = [
      '#markup' => '<div class="admin-block">',
    ];

    $new_registro_form = \Drupal::formBuilder()
      ->getForm('Drupal\carbray_facturacion\Form\NewRegistroFormStepTwo', $factura_nid, $captacion_nid);
    $build['new_registro'] = [
      '#markup' => render($new_registro_form),
    ];

    $build['post'] = [
      '#markup' => '</div>',
    ];
    return $build;
  }
}
