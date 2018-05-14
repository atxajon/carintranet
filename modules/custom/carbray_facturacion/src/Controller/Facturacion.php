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
      '#button_classes' => 'btn btn-primary margin-bottom-20 margin-top-10',
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
      'Comision',
      'Fecha cobro factura ',
      'Comentarios',
    );

    $acumulated_total_facturas = 0;
    $acumulated_total_reparto_comision = 0;
    $acumulated_total_comision = 0;
    $my_facturas_registradas = get_my_facturas_registradas(\Drupal::currentUser()->id());
    foreach ($my_facturas_registradas as $my_factura_registrada) {
      $mi_comision = $my_factura_registrada->field_factura_precio_value * $my_factura_registrada->comision;
      $perc_comision = 0.05;
      $total_reparto_comision = $mi_comision * $perc_comision;
      $rows[] = array(
        date('d-m-Y', $my_factura_registrada->factura_created),
        $my_factura_registrada->title,
        $my_factura_registrada->field_nombre_value . ' ' . $my_factura_registrada->field_apellido_value,
        $my_factura_registrada->field_captacion_captador_target_id,
        $my_factura_registrada->field_factura_precio_value . '€',
        $my_factura_registrada->comision * 100 . '%',
        $mi_comision . '€',
        $perc_comision * 100 . '%',
        $total_reparto_comision . '€',
        'fecha cobro factura',
        'Comentarios',
      );
      $acumulated_total_facturas += $my_factura_registrada->field_factura_precio_value;
      $acumulated_total_reparto_comision += $total_reparto_comision;
      $acumulated_total_comision += $mi_comision;
    }
    // @todo: acumulate and add totals as final row.
    $rows[] = [
      Markup::create('<b>Total:</b>'),
      '',
      '',
      '',
      Markup::create('<b>' . number_format($acumulated_total_facturas,   2 , ',', '.') . '€</b>'),
      '',
      Markup::create('<b>' . number_format($acumulated_total_reparto_comision,  2 , ',', '.') . '€</b>'),
      '',
      Markup::create('<b>' . number_format($acumulated_total_comision, 2 , ',', '.') . '€</b>'),
      '',
    ];

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
