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

    $header = array(
      'Fecha factura',
      'Numero factura',
      'Cliente',
      'Captador',
      'Importe factura (B.I.)',
      'Porcentaje base imponible',
      'Total reparto comision',
      'Porcentaje comision',
      'Fecha cobro factura ',
      'Comentarios',
    );

    $my_facturas_registradas = get_my_facturas_registradas(\Drupal::currentUser()->id());
    foreach ($my_facturas_registradas as $my_factura_registrada) {
      $rows[] = array(
        date('d-m-Y', $my_factura_registrada->factura_created),
        'Numero factura',
        $my_factura_registrada->field_nombre_value . ' ' . $my_factura_registrada->field_apellido_value,
        $my_factura_registrada->field_captacion_captador_target_id,
        $my_factura_registrada->field_factura_precio_value,
        'porcentaje aqui',
        'total repartyo comision',
        '% comision',
        'fecha cobro factura',
        'Comentarios',
      );
    }
    // @todo: acumulate and add totals as final row.

    $build['tabla_excel_facturacion'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => [
        'max-age' => 0,
      ],
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
