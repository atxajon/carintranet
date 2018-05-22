<?php

namespace Drupal\carbray_facturacion\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Render\Markup;

class Facturacion extends ControllerBase {

  public function FacturasPorPagar() {
    $build['pre'] = [
      '#markup' => '<div class="admin-block">',
    ];

    $form = \Drupal::formBuilder()->getForm('Drupal\carbray_facturacion\Form\FacturasForm');

    $build['form'] = [
      '#markup' => render($form),
    ];
    $build['post'] = [
      '#markup' => '</div>',
    ];
    return $build;
  }

  public function FacturasPagadas() {
    $paid = TRUE;
    $factura_ids = get_facturas([], $paid);
    $rows = [];
    foreach ($factura_ids as $factura_id) {
      $factura_node = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($factura_id);
      $factura_captacion = $factura_node->get('field_factura')->getValue();
      $captacion_node = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($factura_captacion[0]['target_id']);
      $captacion_uid = $captacion_node->get('field_captacion_cliente')
        ->getValue();
      $cliente_data = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->load($captacion_uid[0]['target_id']);

      $iva = ($factura_node->get('field_factura_iva')->value == 1) ? 'Con IVA' : 'Sin IVA';
      $rows[] = array(
        'cliente' => print_cliente_link($cliente_data, FALSE),
        'captador' => print_cliente_captadores_responsables($captacion_node->get('field_captacion_captador')
          ->getValue()),
        'nif' => $factura_node->get('field_factura_nif')->value,
        'iva' => $iva,
        'precio' => number_format($factura_node->get('field_factura_precio')->value, 2, ',', '.') . '€',
        'fecha' => date('d-m-Y H:i:s', $factura_node->created->value),
        'fecha_captacion' => date('d-m-Y H:i:s', $captacion_node->created->value),
      );
    }

    $header = array(
      'cliente' => t('Cliente'),
      'captador' => t('Captador'),
      'nif' => t('NIF'),
      'iva' => t('IVA'),
      'precio' => t('Precio'),
      'fecha' => t('Fecha creacion factura'),
      'fecha_captacion' => t('Fecha creacion captación'),
    );
    $build['pre'] = [
      '#markup' => '<div class="admin-block">',
    ];

    $build['table'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('Ninguna factura pagada.'),
    );
    $build['post'] = [
      '#markup' => '</div>',
    ];

    // Disable caching...
    $build['#cache']['max-age'] = 0;

    return $build;
  }

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

    $header = [
      ['data' => 'Fecha factura','field' => 'fecha_factura'],
      ['data' => 'Numero factura','field' => 'numero_factura'],
      ['data' => 'Captador','field' => 'captador'],
      ['data' => 'Importe factura (B.I.)','field' => 'importe_factura', 'class' => ['text-right']],
      ['data' => 'Porcentaje base imponible','field' => 'perc_imponible', 'class' => ['text-center']],
      ['data' => 'Total reparto comision','field' => 'total_reparto_comision', 'class' => ['text-right']],
      ['data' => 'Porcentaje comision','field' => 'porcentaje_comision', 'class' => ['text-center']],
      ['data' => 'Comision','field' => 'comision', 'class' => ['text-right']],
      ['data' => 'Fecha cobro factura','field' => 'fecha_cobro'],
      ['data' => 'Comentarios','field' => 'comentarios', 'class' => ['comentarios-col']],
      ['data' => 'Editar','field' => 'editar'],
    ];

    $acumulated_total_facturas = 0;
    $acumulated_total_reparto_comision = 0;
    $acumulated_total_comision = 0;
    $my_facturas_registradas = get_my_facturas_registradas(\Drupal::currentUser()->id());
    $last_nid = 0;
    foreach ($my_facturas_registradas as $my_factura_registrada) {
      // Skip duplicates for facturas that have multiple captadores.
      if ($last_nid == $my_factura_registrada->nid) {
        continue;
      }
      $mi_comision = $my_factura_registrada->field_factura_precio_value * $my_factura_registrada->comision;
      $perc_comision = 0.05;
      $total_reparto_comision = $mi_comision * $perc_comision;

      $form = \Drupal::formBuilder()
        ->getForm('Drupal\carbray_facturacion\Form\EditRegistroForm', $my_factura_registrada->registro_id, 6068, 4630);

      $edit_button = [
        '#theme' => 'button_modal',
        '#unique_id' => 'add-hours-expediente-nid-' . 6068,
        '#button_text' => 'Editar registro',
        '#button_classes' => 'btn btn-primary',
        '#modal_title' => t('Editar registro'),
        '#modal_content' => $form,
        '#has_plus' => FALSE,
      ];

      $rows[] = [
        'data' => [
          date('d-m-Y', $my_factura_registrada->factura_created),
          $my_factura_registrada->title,
          $my_factura_registrada->field_captacion_captador_target_id,
          [
            'data' => number_format($my_factura_registrada->field_factura_precio_value, 2, ',', '.') . '€',
            'class' => ['text-right'],
          ],
          [
            'data' => $my_factura_registrada->comision * 100 . '%',
            'class' => ['text-center'],
          ],
          [
            'data' => number_format($mi_comision, 2, ',', '.') . '€',
            'class' => ['text-right'],
          ],
          [
            'data' => $perc_comision * 100 . '%',
            'class' => ['text-center'],
          ],
          [
            'data' => number_format($total_reparto_comision, 2, ',', '.') . '€',
            'class' => ['text-right'],
          ],
          date('d-m-Y',$my_factura_registrada->fecha_cobro),
          Markup::create($my_factura_registrada->descripcion),
          render($edit_button),
        ],
        'class' => [
          'row_class',
        ],
      ];

      $acumulated_total_facturas += $my_factura_registrada->field_factura_precio_value;
      $acumulated_total_reparto_comision += $total_reparto_comision;
      $acumulated_total_comision += $mi_comision;
      $last_nid = $my_factura_registrada->nid;
    }
    // Adds totals row.
    $rows[] = [
      Markup::create('<b>Total:</b>'),
      '',
      '',
      [
        'data' => Markup::create('<b>' . number_format($acumulated_total_facturas,   2 , ',', '.') . '€</b>'),
        'class' => ['text-right'],
      ],
      '',
      [
        'data' => Markup::create('<b>' . number_format($acumulated_total_comision,  2 , ',', '.') . '€</b>'),

        'class' => ['text-right'],
      ],
      '',
      [
        'data' => Markup::create('<b>' . number_format($acumulated_total_reparto_comision, 2 , ',', '.') . '€</b>'),


        'class' => ['text-right'],
      ],
      '',
      '',
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
