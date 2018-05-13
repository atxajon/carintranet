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
      '#prefix' => '<div class="clearfix">',
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
      '#suffix' => '</div>',
    );

    $form['factura_nid'] = array(
      '#type' => 'hidden',
      '#value' => $factura_nid,
    );
    $form['captacion_nid'] = array(
      '#type' => 'hidden',
      '#value' => $captacion_nid,
    );
    $form['captador_uid'] = array(
      '#type' => 'hidden',
      '#value' => $uid = \Drupal::currentUser()->id(),
    );
    $form['captacion_date'] = array(
      '#type' => 'hidden',
      '#value' => $captacion_node->created->value,
    );


    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear registro',
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



    drupal_set_message('Nueva nota creada');
  }
}