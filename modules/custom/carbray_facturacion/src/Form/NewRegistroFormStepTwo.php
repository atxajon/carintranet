<?php

namespace Drupal\carbray_facturacion\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;


/**
 * NewRegistroFormStepTwo form.
 */
class NewRegistroFormStepTwo extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'new_registro_step_two';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $factura_nid = 0, $captacion_nid = 0) {

// Get captacion nid from url arg.
//    $captacion_nid = \Drupal::request()->query->get('captacion_nid');
    $captacion_node = Node::load($captacion_nid);
    $captador_uid = $captacion_node->get('field_captacion_captador')->getValue();
    $factura_node = Node::load($factura_nid);
    $captacion_uid = $captacion_node->get('field_captacion_cliente')
      ->getValue();
    $cliente_data = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->load($captacion_uid[0]['target_id']);

    $form['factura'] = array(
      '#type' => 'textfield',
      '#title' => 'Factura',
      '#default_value' => $factura_node->title->value,
      '#disabled' => TRUE,
    );

    $form['cliente'] = array(
      '#type' => 'textfield',
      '#title' => 'Cliente',
      '#default_value' => $cliente_data->get('field_nombre')->value . ' ' . $cliente_data->get('field_apellido')->value,
      '#disabled' => TRUE,
      '#prefix' => '<div class="clearfix">',
    );

    $form['importe'] = array(
      '#type' => 'textfield',
      '#title' => 'Importe',
      '#default_value' => $factura_node->get('field_factura_precio')->value,
      '#disabled' => TRUE,
    );

    $form['fecha'] = array(
      '#type' => 'textfield',
      '#title' => 'Fecha de factura',
      '#default_value' => date('d-m-Y', $factura_node->created->value),
      '#disabled' => TRUE,
    );

    foreach ($captador_uid as $capta_uid) {
      $captador = User::load($capta_uid['target_id']);
      $form['captador_' . $capta_uid['target_id']] = array(
        '#type' => 'textfield',
        '#title' => 'Captador',
        '#default_value' => $captador->get('field_nombre')->value . ' ' . $captador->get('field_apellido')->value,
        '#disabled' => TRUE,
      );
    }

    $form['base_imponible'] = array(
      '#type' => 'number',
      '#title' => 'Base imponible (en %)',
      '#default_value' => 0,
      '#min' => 0,
      '#max' => 100,
      '#step' => 0.01,
      '#required' => TRUE,
    );

    $form['notas'] = array(
      '#type' => 'text_format',
      // ‘textarea’ (if no ckeditor for html formatting needed)
      '#title' => 'Descripcion',
      '#format' => 'basic_html',
      '#rows' => 2,
    );

    $form['factura_nid'] = array(
      '#type' => 'hidden',
      '#value' => $factura_nid,
    );
    $form['captacion_nid'] = array(
      '#type' => 'hidden',
      '#value' => $captacion_nid,
    );

    $form['captacion_date'] = array(
      '#type' => 'hidden',
      '#value' => $captacion_node->created->value,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear registro en tabla excel',
      '#attributes' => array('class' => array('btn-primary', 'margin-top-20')),
    );

    // @todo: boton cancelar para volver...
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
    $factura_nid = $form_state->getValue('factura_nid');
    $base_imponible = $form_state->getValue('base_imponible');
    $notas = $form_state->getValue('notas');
    $comision = $base_imponible / 100;

    // Update carbray_facturas table with base imponible.
    $success = \Drupal::database()->update('carbray_facturas')
      ->fields(['comision' => $comision])
      ->condition('factura_nid', $factura_nid)
      ->execute();

    drupal_set_message('Base imponible añadida');
  }
}