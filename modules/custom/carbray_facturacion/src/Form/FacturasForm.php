<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray_facturacion\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Url;



class FacturasForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facturas_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Adding checkboxes to a table using tableselect: https://www.drupal.org/node/945102

    // Work out filter values from query string.
    $qs_defaults = [];
    $qs = \Drupal::request()->query->all();
    if ($qs) {
      foreach ($qs as $key => $value) {
        $qs_defaults[$key] = $value;
      }
    }

    $departamentos = get_taxonomy_terms_options('departamento');
    $form['departamento_id'] = array(
      '#type' => 'select',
      '#title' => 'Departamento',
      '#prefix' => '<div class="clearfix factura-filters margin-bottom-20">',
      '#options' => $departamentos,
      '#empty_option' => 'Todos los departamentos',
      '#default_value' => (isset($qs_defaults['departamento'])) ? $qs_defaults['departamento'] : '',
    );
    $workers = get_carbray_workers(TRUE);
    $form['captador_id'] = array(
      '#type' => 'select',
      '#title' => 'Captador',
      '#options' => $workers,
      '#empty_option' => 'Tod@s l@s captadores',
      '#default_value' => (isset($qs_defaults['captador'])) ? $qs_defaults['captador'] : '',
    );

    $form['search'] = array(
      '#type' => 'submit',
      '#value' => 'Buscar',
      '#attributes' => array('class' => array('btn-primary')),
      '#submit' => array('::buscarFactura'),
      '#suffix' => '</div>',
    );

    $factura_ids = get_facturas($qs_defaults);

    $options = [];
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
      $options[$factura_id] = array(
        'cliente' => print_cliente_link($cliente_data, FALSE),
        'proforma' => ($factura_node->get('field_factura_proforma')->value) ? t('Proforma') : 'Factura',
        'captador' => print_cliente_captadores_responsables($captacion_node->get('field_captacion_captador')
            ->getValue()),
        'nif' => $factura_node->get('field_factura_nif')->value,
        'iva' => $iva,
        'precio' => $factura_node->get('field_factura_precio')->value,
        'fecha' => date('d-m-Y H:i:s', $factura_node->created->value),
        'fecha_captacion' => date('d-m-Y H:i:s', $captacion_node->created->value),
      );
    }

    $header = array(
      'cliente' => t('Cliente'),
      'proforma' => t('Proforma / Factura'),
      'captador' => t('Captador'),
      'nif' => t('NIF'),
      'iva' => t('IVA'),
      'precio' => t('Precio'),
      'fecha' => t('Fecha creacion factura'),
      'fecha_captacion' => t('Fecha creacion captación'),
    );

    $form['table'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#js_select' => FALSE, // Don't want the select all checbox at the header.
      '#empty' => t('No se encontraron facturas sin pagar.'),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Marcar facturas como pagadas'),
    );

    $form['delete'] = array(
      '#type' => 'submit',
      '#value' => 'Eliminar proforma',
      '#attributes' => array('class' => array('btn-danger', 'delete-proforma')),
      '#submit' => array('::deleteProforma'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Any unchecked items will be given a value of 0, checked items will be given a value of the item key.
    // We can use the array_filter function to give us only the selected items.
    $unpaid_factura_ids = array_filter($form_state->getValue('table'));
    foreach ($unpaid_factura_ids as $unpaid_factura_id) {
      // Update factura node field pagada to true.
      $factura_node = Node::load($unpaid_factura_id);
      $factura_node->set('field_factura_pagada', 1);
      $factura_node->save();

      // Notify abogados by email.
      $factura_captacion = $factura_node->get('field_factura')->getValue();
      $captacion_node = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->load($factura_captacion[0]['target_id']);

      $captacion_uid = $captacion_node->get('field_captacion_cliente')
        ->getValue();
      $cliente_data = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->load($captacion_uid[0]['target_id']);
      $params = [
        'cliente' => print_cliente_link($cliente_data, FALSE),
        'nif' => $factura_node->get('field_factura_nif')->value,
      ];

      $captacion_captadores = $captacion_node->get('field_captacion_captador')->getValue();
      foreach ($captacion_captadores as $captacion_captador) {
        $captador_user = User::load($captacion_captador['target_id']);
        $captador_email = $captador_user->getEmail();
        $to = $captador_email;
        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'carbray_mailer';
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $sent = $mailManager->mail($module, 'notify_captador_factura_paid', $to, $langcode, $params);
        $mssg = ($sent) ? 'Email sent to abogado captador as a factura has been marked as paid by secretaria' : '';
        \Drupal::logger('carbray')->warning($mssg);
      }

    }
    drupal_set_message('Facturas marcadas como pagadas.');
  }

  public function deleteProforma(array &$form, FormStateInterface $form_state) {
    $unpaid_factura_ids = array_filter($form_state->getValue('table'));
    foreach ($unpaid_factura_ids as $unpaid_factura_id) {
      $factura = Node::load($unpaid_factura_id);
      $factura->delete();
    }

    drupal_set_message('Proforma(s) eliminadas.');
  }

  public function buscarFactura(array &$form, FormStateInterface $form_state) {
    $departamento_id = $form_state->getValue('departamento_id');
    $captador_id = $form_state->getValue('captador_id');

    $options = [];
    if ($departamento_id) {
      $options['query'] = ['departamento' => $departamento_id];
    }
    if ($captador_id) {
      $options['query'][] = ['captador' => $captador_id];

    }

    $url = Url::fromUri('internal:/node/1748', $options);
    $form_state->setRedirectUrl($url);
  }
}