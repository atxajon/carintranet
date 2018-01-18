<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;


/**
 * EditCaptacion form.
 */
class EditCaptacion extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_captacion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $form['#attributes']['class'][] = 'form-in-modal';
    $form['captacion_nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );

    $internal_users = get_carbray_workers(TRUE);
    $internal_users_options = [];
    foreach ($internal_users as $uid => $email) {
      $user = User::load($uid);
      $internal_users_options[$uid] = $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value;
    }
    $current_captador = get_captacion_captador($nid);
    $form['captador'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Captador',
      '#options' => $internal_users_options,
      '#default_value' => $current_captador,
      '#multiple' => TRUE,
      '#required' => TRUE,
    );


    // Query for all estados de captaction terms.
    $estados_options = get_taxonomy_terms_options('estado_de_captacion');

    // Get this captacion's estado de captacion.
    $db = \Drupal::database();
    $sql = "SELECT field_captacion_estado_captacion_target_id FROM node__field_captacion_estado_captacion WHERE entity_id = :nid";
    $default_estado = $db->query($sql, array(':nid' => $nid))->fetchCol();

    $form['estado'] = array(
      '#type' => 'select',
      '#title' => 'Cambiar estado',
      '#empty_option' => ' - Selecciona estado captacion - ',
      '#options' => $estados_options,
    );
    if ($default_estado) {
      $form['estado']['#default_value'] = $default_estado;
    }

    // Query for all ciudades de ejecucion terms.
    $ciudades_options = get_taxonomy_terms_options('provincia_de_ejecucion');
    // Get this captacion's ciudad.
    $sql = "SELECT field_captacion_ciudad_target_id FROM node__field_captacion_ciudad WHERE entity_id = :nid";
    $default_ciudad = $db->query($sql, array(':nid' => $nid))->fetchCol();

    $form['ciudad'] = array(
      '#type' => 'select',
      '#title' => 'Ciudad de ejecucion',
      '#empty_option' => ' - Selecciona ciudad - ',
      '#options' => $ciudades_options,
    );
    if ($default_ciudad) {
      $form['ciudad']['#default_value'] = $default_ciudad;
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Guardar captacion',
      '#attributes' => array('class' => array('btn-primary', 'btn')),
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

    $captador = $form_state->getValue('captador');
    // $captador strangely adds uid 0 for every non selected captador checkbox;
    // let's clean those up.
    $selected_captador = array();
    foreach ($captador as $captador_id => $value) {
      if ($value == 0) {
        continue;
      }
      $selected_captador[$captador_id] = $value;
    }

    $nid = $form_state->getValue('captacion_nid');
    $ciudad = $form_state->getValue('ciudad');
    $estado = $form_state->getValue('estado');

    $captacion = Node::load($nid);
    $captacion->set('field_captacion_estado_captacion', $estado);
    $captacion->set('field_captacion_captador', $selected_captador);
    $captacion->set('field_captacion_ciudad', $ciudad);
    $captacion->save();

    drupal_set_message('Captacion guardada.');
  }
}