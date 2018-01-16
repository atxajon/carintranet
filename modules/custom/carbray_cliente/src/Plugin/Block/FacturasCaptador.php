<?php

namespace Drupal\carbray_cliente\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Facturas captador' block.
 *
 * @Block(
 *  id = "facturas_captador",
 *  admin_label = @Translation("Facturas de mis clientes"),
 * )
 */
class FacturasCaptador extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $factura_ids = get_facturas_mis_clientes(\Drupal::currentUser()->id()
    );
    $rows = [];
    foreach ($factura_ids as $factura_id) {
      $factura_node = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($factura_id->entity_id);
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
        'nif' => $factura_node->get('field_factura_nif')->value,
        'cliente' => print_cliente_link($cliente_data, FALSE),
        'proforma' => ($factura_node->get('field_factura_proforma')->value) ? t('Proforma') : 'Factura',
        'captador' => print_cliente_captadores_responsables($captacion_node->get('field_captacion_captador')
          ->getValue()),
        'iva' => $iva,
        'precio' => $factura_node->get('field_factura_precio')->value,
        'pagada' => ($factura_node->get('field_factura_pagada')->value) ? 'Pagada' : 'No pagada',
        'fecha' => date('d-m-Y H:i:s', $factura_node->created->value),
        'fecha_captacion' => date('d-m-Y H:i:s', $captacion_node->created->value),
      );
    }

    $header = array(
      'nif' => t('NIF'),
      'cliente' => t('Cliente'),
      'proforma' => t('Proforma / Factura'),
      'captador' => t('Captador'),
      'iva' => t('IVA'),
      'precio' => t('Precio'),
      'pagada' => t('Pagada?'),
      'fecha' => t('Fecha creacion factura'),
      'fecha_captacion' => t('Fecha creacion captación'),
    );
    $build['heading'] = [
      '#markup' => '<h2>Facturas de mis clientes</h2>',
    ];
    $build['table'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('Ninguna factura aún.'),
    );
    // Disable caching...
    $build['#cache']['max-age'] = 0;

    return $build;
  }
}
