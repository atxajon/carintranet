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
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;



/**
 * NewWorkerForm form.
 */
class NewWorkerForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_new_worker';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'block';
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
      '#required' => TRUE,
    );
    $form['telefono'] = array(
      '#type' => 'textfield',
      '#title' => 'Telefono',
      '#size' => '20',
    );


    $departamento_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('departamento');
    $options = [];
    foreach ($departamento_terms as $term) {
      $options[$term->tid] = $term->name;
    }
    $form['departamentos'] = array(
      '#title' => 'Departamento(s)',
      '#type' => 'checkboxes',
      '#empty_option' => ' - Selecciona departamento - ',
      '#options' => $options,
      '#multiple' => TRUE,
    );

    // Only expose internal worker pertinent roles: worker, etc...
    $roles = user_role_names();
    $allowed_roles = [
      'worker',
      'secretaria',
      'carbray_administrator',
      'jefe_departamento',
    ];
    $role_options = [];
    foreach ($roles as $role_name => $role_value) {
      if (!in_array($role_name, $allowed_roles)) {
        continue;
      }
      $role_options[$role_name] = $role_value;
    }
    $form['role'] = array(
      '#title' => 'Rol',
      '#type' => 'select',
      '#empty_option' => ' - Selecciona rol - ',
      '#options' => $role_options,
      '#required' => TRUE,
    );


    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear trabajador',
      '#attributes' => array('class' => array('btn-success')),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $unique_email = email_already_in_system($email);
    if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
      $form_state->setErrorByName('email', t('The email address %mail is not valid.', array('%mail' => $email)));
    }
    if($unique_email){
      $form_state->setErrorByName('email', t('A user with email address %mail already exists in the system. Please use a different email.', array('%mail' => $email)));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $nombre = $form_state->getValue('nombre');
    $apellido = $form_state->getValue('apellido');
    $email = $form_state->getValue('email');
    $role = $form_state->getValue('role');
    $departamentos = $form_state->getValue('departamentos');
    // If no email for a client populate it with nombre + apellido + placeholder.
    $email = ($email) ? $email : 'sin_email@' . $nombre . '_' . $apellido . '.com';
    $telefono = $form_state->getValue('telefono');

    $user = User::create();

    // Mandatory settings.
    $user->setPassword(user_password());
    $user->setEmail($email);

    foreach ($departamentos as $departamento) {
      $user->field_departamento->appendItem($departamento);
    }

    // Make Username ('Display name') same as email. This is later used in the system for presentation purposes and it's easier to pinpoint what user refers to.
    $user->setUsername($email);

    // Optionals.
    $user->enforceIsNew();
    $user->set('field_telefono', $telefono);
    $user->set('field_telefono', $telefono);
    $user->set('field_nombre', $nombre);
    $user->set('field_apellido', $apellido);

    // Let's activate the worker by default.
    $user->activate();

    // More optionals to be considered...
    $user->set('init', $email);
    $user->addRole($role);
    // $user->set("langcode", $lang);
    // $user->set("preferred_langcode", $lang);
    // $user->set("preferred_admin_langcode", $lang);
    // $user->set("timezone", 'Pacific/Wallis');

    $user->save();

    // Send email with one time log in link to worker.
    _user_mail_notify('status_activated', $user, $langcode = NULL);

    $uid = $user->id();

    drupal_set_message('Trabajador ' . $nombre . ' ' . $apellido . ' con uid: ' . $uid . ' ha sido creado');
    $url = Url::fromUri('base:/gestionar-trabajadores');
    $form_state->setRedirectUrl($url);
  }
}