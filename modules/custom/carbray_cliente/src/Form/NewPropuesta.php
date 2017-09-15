<?php

namespace Drupal\carbray_cliente\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;
use Drupal\Component\Plugin\Exception;
use Drupal\Core\Url;
use Drupal\user\Entity\User;


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
    $form['#attributes']['class'][] = 'block';

    // Retrieve propuesta plantilla nid from query string, then load Node to use its content as default values for form elements.
    $propuesta_plantilla_nid = \Drupal::request()->query->get('prop_plantilla');
    try {
      $prop_plantilla_node = Node::load($propuesta_plantilla_nid);
      $prop_plantilla_precio = $prop_plantilla_node->get('field_plantilla_propuesta_precio')->value;
      $prop_plantilla_body = $prop_plantilla_node->get('body')->value;
    }
    catch (Exception $e) {
      \Drupal::logger('carbray_cliente')->error($e->getMessage());
    }

    // Retrieve uid from query string. It'll be used to populate propuesta-user reference.
    $cliente_uid = \Drupal::request()->query->get('uid');
    $user = User::load($cliente_uid);
    $form['cliente_name'] = [
      '#type' => 'textfield',
      '#title' => t('Propuesta para cliente:'),
      '#default_value' => $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value,
      '#disabled' => TRUE,
    ];

    $form['body'] = [
      '#type' => 'text_format',
      '#title' => t('Body'),
      '#default_value' => $prop_plantilla_body,
      '#required' => TRUE,
    ];

    $form['precio'] = [
      '#type' => 'textfield',
      '#title' => t('Precio'),
      '#default_value' => $prop_plantilla_precio,
    ];

    $form['cliente_uid'] = [
      '#type' => 'hidden',
      '#value' => $cliente_uid,
    ];

    $internal_users = get_carbray_workers(TRUE);
    $current_user = \Drupal::currentUser();
    $current_user_uid = $current_user->id();
    $form['equipo'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Equipo',
      '#options' => $internal_users,
      '#default_value' => array($current_user_uid),
      '#multiple' => TRUE,
    );

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
    $body = $form_state->getValue('body');
    $propuesta_plantilla_nid = $form_state->getValue('propuesta_plantilla_nid');
    $cliente_uid = $form_state->getValue('cliente_uid');
    $equipo_members = $form_state->getValue('equipo');
    // $equipo_members strangely adds uid 0 for every non selected captador checkbox;
    // let's clean those up.
    $selected_equipo = array();
    foreach ($equipo_members as $member_id => $value) {
      if ($value == 0) {
        continue;
      }
      $selected_equipo[$member_id] = $value;
    }

    $user = User::load($cliente_uid);
    $now = date('d-m-Y', time());

    // Create a node of type Propuesta with submitted values.
    $title = 'Propuesta para ' . $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value . ' creada el ' . $now;
    Node::create([
      'title' => $title,
      'type' => 'propuesta',
      'body' => $body,
      'field_propuesta_precio' => $precio,
      'field_propuesta_cliente' => $cliente_uid,
      'field_propuesta_plantilla' => $propuesta_plantilla_nid,
      'field_propuesta_equipo' => $selected_equipo,
    ])->save();

    // Handle redirect to user page.
    $user_route = 'entity:user/' . $cliente_uid;
    $url = Url::fromUri($user_route);
    drupal_set_message(t('Propuesta creada'));
    $form_state->setRedirectUrl($url);
  }
}
