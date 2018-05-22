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
    $sql = "SELECT * FROM carbray_facturas_registro WHERE factura_nid = :factura_nid AND author_uid = :author_uid";
    $registro = $db->query($sql, array(':factura_nid' => $factura_nid, ':author_uid' => \Drupal::currentUser()->id()))->fetchObject();


    $form['factura'] = array(
      '#type' => 'textfield',
      '#title' => 'Factura',
      '#default_value' => $factura_node->title->value,
      '#disabled' => TRUE,
    );

    $form['importe'] = array(
      '#type' => 'textfield',
      '#title' => 'Importe',
      '#default_value' => $factura_node->get('field_factura_precio')->value,
    );

    $form['fecha'] = array(
      '#type' => 'date',
//      '#type' => 'datetime',
      '#title' => 'Fecha de factura',
//      '#default_value' => date('d-m-Y', $factura_node->created->value),
      '#default_value' => DrupalDateTime::createFromTimestamp($factura_node->created->value),
    );

    $form['base_imponible'] = array(
      '#type' => 'number',
      '#title' => 'Base imponible (en %)',
      '#default_value' => $registro->comision,
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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $factura_nid = $form_state->getValue('factura_nid');
    $captacion_nid = $form_state->getValue('captacion_nid');
    $base_imponible = $form_state->getValue('base_imponible');
    $notas = $form_state->getValue('notas');
    if (is_array($notas)) {
      $notas = $notas['value'];
    }
    $comision = $base_imponible / 100;
    \Drupal::logger('$notas')->notice(print_r($notas, TRUE));


    // Update carbray_facturas_registro table with base imponible.
    $success = \Drupal::database()->update('carbray_facturas_registro')
      ->fields([
        'comision' => $comision,
        'descripcion' => $notas,
      ])
      ->condition('factura_nid', $factura_nid)
      ->condition('author_uid', \Drupal::currentUser()->id())
      ->condition('captacion_nid', $captacion_nid)
      ->execute();
    if (!$success) {
      $form_state->setRebuild();
    }

    drupal_set_message('Base imponible añadida');
  }
}