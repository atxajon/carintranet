<?php
/**
 * @file
 * Contains \Drupal\carbray_cliente\Form\NewNotaForm.
 */
namespace Drupal\carbray_cliente\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
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
    $form['nif'] = array(
      '#type' => 'textfield',
      '#title' => 'NIF',
      '#required' => TRUE,
    );
    $form['precio'] = array(
      '#type' => 'number',
      '#title' => 'Precio',
      '#default_value' => 0,
      '#min' => 0,
      '#step' => 0.01,
    );
    $form['iva'] = [
      '#type' => 'radios',
      '#title' => t('IVA'),
      '#options' => array(0 => $this->t('Sin IVA'), 1 => $this->t('Con IVA')),
      '#default_value' => 1,
      '#required' => TRUE,
    ];
    $form['captacion_nid'] = array(
      '#type' => 'hidden',
      '#value' => $captacion_nid,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear factura',
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
    $nif = $form_state->getValue('nif');
    $precio = $form_state->getValue('precio');
    $iva = $form_state->getValue('iva');
    $captacion_nid = $form_state->getValue('captacion_nid');

    $factura_node = Node::create(['type' => 'factura']);
    $factura_node->set('title', 'Factura para captacion id ' . $captacion_nid);
    $factura_node->set('field_factura_nif', $nif);
    $factura_node->set('field_factura_iva', $iva);
    $factura_node->set('field_factura_precio', $precio);
    $factura_node->set('field_factura', $captacion_nid);
    $factura_node->enforceIsNew();
    $factura_node->save();

    // Send email to notify users with role secretaria.

    drupal_set_message('Factura creada');
  }
}