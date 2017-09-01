<?php

namespace Drupal\carbray_cliente\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;
use Drupal\Component\Plugin\Exception;
use Drupal\Core\Url;


/**
 * Class NewPropuesta.
 */
class NewPropuesta extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'new_propuesta';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Retrieve propuesta plantilla nid from query string, then load Node to use its content as default values for form elements.
    $propuesta_plantilla_nid = \Drupal::request()->query->get('prop_plantilla');
    try {
      $prop_plantilla_node = Node::load($propuesta_plantilla_nid);
      $prop_plantilla_precio = $prop_plantilla_node->get('field_plantilla_propuesta_precio')->value;
    }
    catch (Exception $e) {
      \Drupal::logger('carbray_cliente')->error($e->getMessage());
    }
    $form['precio'] = [
      '#type' => 'textfield',
      '#title' => t('Precio'),
      '#default_value' => $prop_plantilla_precio,
      '#required' => TRUE,
    ];

    // Retrieve uid from query string. It'll be used to populate propuesta-user reference.
    $cliente_uid = \Drupal::request()->query->get('uid');
    $form['cliente_uid'] = [
      '#type' => 'hidden',
      '#value' => $cliente_uid,
    ];

    $form['propuesta_plantilla_nid'] = [
      '#type' => 'hidden',
      '#value' => $propuesta_plantilla_nid,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Crear propuesta'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $precio = $form_state->getValue('precio');
    $propuesta_plantilla_nid = $form_state->getValue('propuesta_plantilla_nid');
    $cliente_uid = $form_state->getValue('cliente_uid');

    // Create a node of type Propuesta with submitted values.
    $title = 'Propuesta para uid: ' . $cliente_uid . ' y plantilla nid: ' . $propuesta_plantilla_nid;
    Node::create([
      'title' => $title,
      'type' => 'propuesta',
      'field_propuesta_precio' => $precio,
      'field_propuesta_plantilla' => $propuesta_plantilla_nid,
    ])->save();

    // Handle redirect to user page.
    $user_route = 'entity:user/' . $cliente_uid;
    $url = Url::fromUri($user_route);
    drupal_set_message(t('Propuesta creada'));
    $form_state->setRedirectUrl($url);
  }
}
