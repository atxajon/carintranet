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
 * NewClientForm form.
 */
class NewClientForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_new_client';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'block';

    $entityManager = \Drupal::service('entity_field.manager');
    $fields = $entityManager->getFieldStorageDefinitions('user', 'user');

    $countries = \Drupal::service('country_manager')->getList();
    // We need to sort translated countries ignoring their accents.
    uasort($countries,"sort_alphabetically");
    $form['pais'] = array(
      '#type' => 'select',
      '#title' => 'Pais',
      '#options' => $countries,
      '#empty_option' => ' - Sin especificar - ',
    );

    $persona_options = options_allowed_values($fields['field_persona_juridica']);
    $form['persona'] = array(
      '#type' => 'select',
      '#title' => 'Persona juridica',
      '#options' => $persona_options,
      '#empty_option' => ' - Elige - ',
      '#required' => TRUE,
    );
    $form['nombre'] = array(
      '#type' => 'textfield',
      '#title' => 'Nombre',
      '#size' => '20',
      '#required' => TRUE,
    );
    $form['apellido'] = array(
      '#type' => 'textfield',
      '#title' => 'Apellido (Importante)',
      '#size' => '20',
    );
    $form['email'] = array(
      '#type' => 'email',
      '#title' => 'Email (Muy Importante)',
      '#size' => '20',
    );
    $form['telefono'] = array(
      '#type' => 'textfield',
      '#title' => 'Telefono (Muy Importante)',
      '#size' => '20',
    );


    $options = options_allowed_values($fields['field_procedencia']);
    $form['procedencia'] = array(
      '#type' => 'select',
      '#title' => 'Procedencia',
      '#options' => $options,
      '#empty_option' => ' - Selecciona procedencia - ',
    );


    // Field cliente_cuenta only shows for workers of Tax department.
    $user = User::load(\Drupal::currentUser()->id());
    $departamentos = $user->get('field_departamento')->getValue();
    foreach ($departamentos as $departamento) {
      if ($departamento['target_id'] == DEPARTAMENTO_TAX) {
        $form['cliente_cuota'] = [
          '#type' => 'radios',
          '#title' => t('Cliente cuota'),
          '#options' => array(
            0 => $this->t('No'),
            1 => $this->t('Si')
          ),
          '#default_value' => 0,
        ];
        break;
      }
    }

    $internal_users = get_carbray_workers(TRUE);
    $internal_users_options = [];
    foreach ($internal_users as $uid => $email) {
      $user = User::load($uid);
      $internal_users_options[$uid] = $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value;
    }
    $current_user = \Drupal::currentUser();
    $current_user_uid = $current_user->id();
    $form['captador'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Captador',
      '#options' => $internal_users_options,
      '#default_value' => array($current_user_uid),
      '#multiple' => TRUE,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear cliente',
      '#attributes' => array('class' => array('btn-success')),
    );

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $telefono = $form_state->getValue('telefono');

    if (!$email && !$telefono) {
      $form_state->setErrorByName('email', t('Por favor introduce email o telefono del cliente'));
    }
    if ($email) {
      if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
        $form_state->setErrorByName('email', t('El email %mail no es válido.', array('%mail' => $email)));
      }
      $found_email = email_already_in_system($email);
      if ($found_email) {
        $form_state->setErrorByName('email', t('Ya hay un cliente en el sistema con este email, por favor verifica que no sea un duplicado.'));
      }
    }

    if ($telefono && !$email) {
      $telefono = str_replace(' ','',$telefono);
      $found_phone = phone_already_in_system($telefono);
      if ($found_phone) {
        $form_state->setErrorByName('telefono', t('Ya hay un cliente en el sistema con este telefono, por favor verifica que no sea un duplicado.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nombre = $form_state->getValue('nombre');
    $apellido = $form_state->getValue('apellido');
    $email = $form_state->getValue('email');
    // If no email for a client populate it with nombre + apellido + placeholder.
    $email = ($email) ? $email : 'sin_email@' . $nombre . '_' . time() . '.com';
    $telefono = $form_state->getValue('telefono');
    $pais = $form_state->getValue('pais');
    $procedencia = $form_state->getValue('procedencia');
    $persona = $form_state->getValue('persona');
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

    $cliente_cuota = $form_state->getValue('cliente_cuota');


    $user = User::create();

    // Mandatory settings.
    $user->setPassword(user_password());
    $user->setEmail($email);

    // Make Username ('Display name') same as email. This is later used in the system for presentation purposes and it's easier to pinpoint what user refers to.
    $user->setUsername($email);

    // Optionals.
    $user->enforceIsNew();
    $user->set('field_pais', $pais);
    $user->set('field_telefono', $telefono);
    $user->set('field_nombre', $nombre);
    $user->set('field_apellido', $apellido);
    $user->set('field_procedencia', $procedencia);
    $user->set('field_persona_juridica', $persona);
//    $user->set('field_captador', $selected_captador);

    // Let's keep the user as Blocked by default, until internal admin activates it.
    // $user->activate();

    // More optionals to be considered...
    $user->set('init', $email);
    // $user->set("langcode", $lang);
    // $user->set("preferred_langcode", $lang);
    // $user->set("preferred_admin_langcode", $lang);
    // $user->set("timezone", 'Pacific/Wallis');

    $user->save();
    $uid = $user->id();


    // Create a Captacion for the new client.
    $now = date('d-m-Y', time());
    $title = 'Captacion ' . $now . ' para cliente: ' . $nombre . ' ' . $apellido;
    $captacion = Node::create(['type' => 'captacion']);
    $captacion->set('title', $title);
    $captacion->set('field_captacion_cliente', $uid);
    $captacion->set('field_captacion_captador', $selected_captador);
    if ($cliente_cuota) {
      $captacion->set('field_captacion_cliente_cuenta', $cliente_cuota);
    }
    $captacion->enforceIsNew();
    $captacion->save();

    // Create an entry on custom table carbray_user_captacion_expediente.
    \Drupal::database()->insert('carbray_user_captacion_expediente')
      ->fields([
        'uid',
        'captacion_nid',
      ])
      ->values(array(
        $uid,
        $captacion->id(),
      ))
      ->execute();

    // Create an entry on {carbray_captacion_changes_log} with default estado.
    $values_to_save = [
      'nid' => $captacion->id(),
      // For simplicity and to avoid errors instead of making 'previous_status' 0 let's give it the default estado.
      'previous_status' => ESTADO_CAPTACION_DEFECTO,
      'new_status' => ESTADO_CAPTACION_DEFECTO,
    ];
    log_estado_captacion_change($values_to_save);

    drupal_set_message('Cliente ' . $nombre . ' ' . $apellido . ' con uid: ' . $uid . ' ha sido creado');
    $form_state->setRedirectUrl(_carbray_redirecter());
  }
}