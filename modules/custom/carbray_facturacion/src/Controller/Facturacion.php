<?php

namespace Drupal\carbray_facturacion\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\user\Entity\User;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

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
    $build = get_excel_table();
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

  /**
   * Access check: only users of departamento inmigration or litigation can view facturacion conjunta.
   * @param AccountInterface $account
   * @return AccessResult
   */
  public function ExcelDepartamentoAccess(AccountInterface $account) {
    $user = User::load($account->id());
    $user_departamento = $user->get('field_departamento')->getValue();
    $is_allowed = FALSE;
    if ($user_departamento) {
      foreach ($user_departamento as $departamento) {
        if ($departamento['target_id'] == DEPARTAMENTO_INMIGRATION || $departamento['target_id'] == DEPARTAMENTO_LITIGATION) {
          $is_allowed = TRUE;
        }
      }
    }

    return AccessResult::allowedIf($is_allowed);
  }
}
