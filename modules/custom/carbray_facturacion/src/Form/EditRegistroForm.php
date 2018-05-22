<?php

namespace Drupal\carbray_facturacion\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;


/**
 * EditRegistroForm form.
 */
class EditRegistroForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_registro_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $registro_id = 0, $factura_nid = 0, $captacion_nid = 0) {

    $captacion_node = Node::load($captacion_nid);
    $factura_node = Node::load($factura_nid);
    $captacion_uid = $captacion_node->get('field_captacion_cliente')
      ->getValue();
    $cliente_data = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->load($captacion_uid[0]['target_id']);

    $db = \Drupal::database();
    $sql = "SELECT * FROM carbray_facturas_registro WHERE id = :registro_id";
    $registro = $db->query($sql, array(':registro_id' => $registro_id))->fetchObject();

    $form['factura'] = array(
      '#type' => 'textfield',
      '#title' => 'Factura',
      '#default_value' => $factura_node->title->value,
      '#disabled' => TRUE,
    );

    $form['importe'] = array(
      '#type' => 'textfield',
      '#title' => 'Importe',
      '#default_value' => number_format($factura_node->get('field_factura_precio')->value, 2, ',', '.') . '€',
      '#disabled' => TRUE,
    );

//    $form['fecha'] = array(
////      '#type' => 'date',
//      '#type' => 'datetime',
//      '#title' => 'Fecha de factura',
////      '#default_value' => date('d-m-Y', $factura_node->created->value),
//      '#default_value' => DrupalDateTime::createFromTimestamp($factura_node->created->value),
//    );

    $form['base_imponible'] = array(
      '#type' => 'number',
      '#title' => 'Base imponible (en %)',
      '#default_value' => $registro->comision *100,
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
      '#default_value' => ($registro->descripcion) ? $registro->descripcion : '',
    );

    $form['registro_id'] = array(
      '#type' => 'hidden',
      '#value' => $registro_id,
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
      '#value' => 'Actualizar registro en tabla excel',
      '#attributes' => array('class' => array('btn-primary', 'margin-top-20')),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $factura_nid = $form_state->getValue('factura_nid');

    $db = \Drupal::database();
    $sql = "SELECT * FROM carbray_facturas_registro WHERE factura_nid = :factura_nid";
    $registros = $db->query($sql, array(':factura_nid' => $factura_nid))->fetchAll();
    // Loop through registros for this factura and accumulate comision.
    $existing_comision = 0;
    foreach ($registros as $registro) {
      $existing_comision .= $registro->comision;
    }

    $existing_comision = (int)$existing_comision * 100;

    $base_imponible = $form_state->getValue('base_imponible');
    $total_comision = $base_imponible + $existing_comision;
    if ($total_comision > 100) {
      $form_state->setErrorByName('base_imponible', t('Base imponible excede el 100%. Esta factura actualmente tiene un ' . $existing_comision . '% de comision.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $registro_id = $form_state->getValue('registro_id');

    $base_imponible = $form_state->getValue('base_imponible');
    $notas = $form_state->getValue('notas');
    if (is_array($notas)) {
      $notas = $notas['value'];
    }
    $comision = $base_imponible / 100;

    // Update carbray_facturas_registro table with base imponible.
    $success = \Drupal::database()->update('carbray_facturas_registro')
      ->fields([
        'comision' => $comision,
        'descripcion' => $notas,
      ])
      ->condition('id', $registro_id)
      ->execute();
    if (!$success) {
      $form_state->setRebuild();
    }

    drupal_set_message('Registro actualizado.');
  }
}