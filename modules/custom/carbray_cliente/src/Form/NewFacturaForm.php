<?php
/**
 * @file
 * Contains \Drupal\carbray_cliente\Form\NewNotaForm.
 */
namespace Drupal\carbray_cliente\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;


/**
 * NewFacturaForm form.
 */
class NewFacturaForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'new_factura';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $captacion_nid = 0) {
    $captacion_node = Node::load($captacion_nid);
    $captacion_uid = $captacion_node->get('field_captacion_cliente')
      ->getValue();
    $cliente_data = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->load($captacion_uid[0]['target_id']);

    $form['cliente'] = array(
      '#type' => 'textfield',
      '#title' => 'Cliente',
      '#default_value' => $cliente_data->get('field_nombre')->value . ' ' . $cliente_data->get('field_apellido')->value,
      '#disabled' => TRUE,
    );
    $form['email'] = array(
      '#type' => 'textfield',
      '#title' => 'Email',
      '#default_value' => $cliente_data->getEmail(),
      '#disabled' => TRUE,
    );
    $form['telefono'] = array(
      '#type' => 'textfield',
      '#title' => 'Telefono',
      '#default_value' => $cliente_data->get('field_telefono')->value,
      '#disabled' => TRUE,
    );
    $form['nif'] = array(
      '#type' => 'textfield',
      '#title' => 'NIF',
      '#required' => TRUE,
    );
    $form['servicio'] = array(
      '#title' => 'Servicio',
      '#type' => 'text_format',
      '#format' => 'basic_html',
      '#rows' => 5,
    );
    $form['precio'] = array(
      '#type' => 'number',
      '#title' => 'Coste',
      '#default_value' => 0,
      '#min' => 0,
      '#step' => 0.01,
    );
    $form['iva'] = [
      '#type' => 'radios',
      '#title' => t('IVA 21%'),
      '#options' => array(0 => $this->t('Sin IVA'), 1 => $this->t('Con IVA')),
      '#default_value' => 1,
      '#required' => TRUE,
    ];
    $form['importe_total'] = array(
      '#type' => 'number',
      '#title' => 'Importe total',
      '#default_value' => 0,
      '#min' => 0,
      '#step' => 0.01,
    );
    $form['captacion_nid'] = array(
      '#type' => 'hidden',
      '#value' => $captacion_nid,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear factura',
      '#attributes' => array('class' => array('btn-primary', 'margin-top-20')),
    );

    $form['#attached']['library'][] = 'carbray/factura_calculator';

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
    $nif = $form_state->getValue('nif');
    $precio = $form_state->getValue('precio');
    $iva = $form_state->getValue('iva');
    $total = $form_state->getValue('importe_total');
    $servicio = $form_state->getValue('servicio');
    $captacion_nid = $form_state->getValue('captacion_nid');

    $factura_node = Node::create(['type' => 'factura']);
    $factura_node->set('title', 'Factura para captacion id ' . $captacion_nid);
    $factura_node->set('field_factura_nif', $nif);
    $factura_node->set('field_factura_iva', $iva);
    $factura_node->set('field_factura_precio', $total);
    $factura_node->set('field_factura', $captacion_nid);
    $factura_node->enforceIsNew();
    $factura_node->save();

    // Send email to notify users with role secretaria.
    $secretarias = get_carbray_workers(TRUE, 'secretaria');
    foreach ($secretarias as $secretaria) {
      $to = $secretaria;
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'bm_emailer';
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $mailManager->mail($module, 'notify_secretaria_new_factura', $to, $langcode);
    }

    drupal_set_message('Factura creada');
  }
}