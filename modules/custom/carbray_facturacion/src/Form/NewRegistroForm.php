<?php

namespace Drupal\carbray_facturacion\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use \Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * NewRegistroForm form.
 */
class NewRegistroForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'new_registro';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $options = get_facturas_pagadas_mis_clientes(\Drupal::currentUser()->id());
    $form['factura'] = array(
      '#type' => 'select',
      '#title' => 'Para Factura:',
      '#options' => $options,
    );
    if (!$options) {
      $form['factura']['#default_value'] = t('No tienes ninguna factura pagada por registrar');
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Continuar',
      '#attributes' => array('class' => array('btn-primary', 'margin-top-20')),
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
    $factura_nid = $form_state->getValue('factura');
    // Determine what captacion is this factura for.
    $captacion_nid = get_captacion_for_factura($factura_nid);

    // Add a query string param to redirect back to this path.
    $options = [
      'query' => [
        'destination' => \Drupal::service('path.current')->getPath(),
      ],
    ];

    $form_state->setRedirect('carbray_facturacion.create_registro_form', ['factura_nid' => $factura_nid, 'captacion_nid' => $captacion_nid], $options);

    drupal_set_message('Por favor procede a completar el registro', 'warning');
  }
}