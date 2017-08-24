<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

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
    $entityManager = \Drupal::service('entity_field.manager');
    $fields = $entityManager->getFieldStorageDefinitions('user');
    $options = options_allowed_values($fields['field_fase']);
    $form['#attributes']['class'][] = 'block';
    $form['fase'] = array(
      '#type' => 'select',
      '#title' => 'Fase',
      '#options' => $options,
      '#default_value' => array('captacion'),
      '#disabled' => TRUE,
    );

    $form['nombre'] = array(
      '#type' => 'textfield',
      '#title' => 'Nombre',
      '#size' => '20',
      '#required' => TRUE,
    );
    $form['apellido'] = array(
      '#type' => 'textfield',
      '#title' => 'Apellido',
      '#size' => '20',
      '#required' => TRUE,
    );
    $form['email'] = array(
      '#type' => 'textfield',
      '#title' => 'Email',
      '#size' => '20',
    );
    $form['telefono'] = array(
      '#type' => 'textfield',
      '#title' => 'Telefono',
      '#size' => '20',
    );
    $countries = \Drupal::service('country_manager')->getList();
    $form['pais'] = array(
      '#type' => 'select',
      '#title' => 'Pais',
      '#options' => $countries,
    );

    // @todo: añadir campo identificacion??

    $internal_users = get_carbray_workers();
    $current_user = \Drupal::currentUser();
    $current_user_uid = $current_user->id();
    $form['captador'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Captador',
      '#options' => $internal_users,
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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nombre = $form_state->getValue('nombre');
    $apellido = $form_state->getValue('apellido');
    $email = $form_state->getValue('email');
    // If no email for a client populate it with nombre + apellido + placeholder.
    $email = ($email) ? $email : 'sin_email@' . $nombre . '_' . $apellido . '.com';
    $telefono = $form_state->getValue('telefono');
    $pais = $form_state->getValue('pais');
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

    $user = \Drupal\user\Entity\User::create();

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
    $user->set('field_captador', $selected_captador);

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
    drupal_set_message('Cliente ' . $nombre . ' ' . $apellido . ' con uid: ' . $uid . ' ha sido creado');
    $form_state->setRedirectUrl(_carbray_redirecter());
  }
}