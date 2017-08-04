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
    );

    $form['nombre'] = array(
      '#type' => 'textfield',
      '#title' => 'Nombre',
      '#size' => '20',
    );
    $form['apellido'] = array(
      '#type' => 'textfield',
      '#title' => 'Apellido',
      '#size' => '20',
    );
    $form['email'] = array(
      '#type' => 'textfield',
      '#title' => 'Email',
      '#size' => '20',
    );
    $countries = \Drupal::service('country_manager')->getList();
    $form['pais'] = array(
      '#type' => 'select',
      '#title' => 'Pais',
      '#options' => $countries,
    );

    // @todo: añadir campo identificacion.

    $db = \Drupal::database();
    $sql = 'SELECT uid, mail FROM users_field_data ufd INNER JOIN user__roles ur ON ufd.uid = ur. entity_id';
    $internal_users = $db->query($sql)->fetchAllKeyed();

    $form['captador'] = array(
      '#type' => 'select',
      '#title' => 'Captador',
      '#empty_option' => ' - Selecciona captador - ',
      '#options' => $internal_users,
      '#multiple' => TRUE,
    );

    $form['responsable'] = array(
      '#type' => 'select',
      '#title' => 'Responsable',
      '#empty_option' => ' - Selecciona responsables - ',
      '#options' => $internal_users,
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
    $pais = $form_state->getValue('pais');
    $fase = $form_state->getValue('fase');
    $captador = $form_state->getValue('captador');
    $responsable = $form_state->getValue('responsable');

    $user = \Drupal\user\Entity\User::create();

    // Mandatory settings.
    $user->setPassword(user_password());
    $user->setEmail($email);

    // Make Username ('Display name') same as email. This is later used in the system for presentation purposes and it's easier to pinpoint what user refers to.
    $user->setUsername($email);

    // Optionals.
    $user->enforceIsNew();
    $user->set('field_fase', $fase);
    $user->set('field_pais', $pais);
    $user->set('field_nombre', $nombre);
    $user->set('field_apellido', $apellido);
    $user->set('field_captador', $captador);
    $user->set('field_responsable', $responsable);

    // Let's keep the user as Blocked by default, until internal admin activates it.
    // $user->activate();

    // More optionals to be considered...
    // $user->set("init", $details->mail);
    // $user->set("langcode", $lang);
    // $user->set("preferred_langcode", $lang);
    // $user->set("preferred_admin_langcode", $lang);
    // $user->set("timezone", 'Pacific/Wallis');

    $user->save();

    $uid = $user->id();
    drupal_set_message('Cliente con uid: ' . $uid . ' ha sido creado');
//    $form_state['redirect'] = '<front>';
  }
}