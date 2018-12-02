<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Url;



/**
 * HomeDataForm form.
 */
class HomeDataForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'home_data_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uid = 0) {
    // Obtain query string values to set default_value in filters.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = [];
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }

    $form['dates'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['class-here'],
      ],
      '#prefix' => '<div class="row margin-top-20">',
    ];

    $form['dates']['date_from'] = array(
      '#type' => 'datelist',
      '#date_format' => 'd-m-Y',
      '#date_flexible' => 0,
      '#date_increment' => 1,
      '#date_year_range' => '-3:+0',
      '#date_part_order' => array('day', 'month', 'year'),
      '#date_time_element' => 'none',
      '#title' => t('Desde'),
      '#default_value' => isset($query_array['date_from']) ? DrupalDateTime::createFromTimestamp($query_array['date_from']) : '',
//      '#prefix' => '<div class="clearfix">',
//      '#suffix' => '</div>',
    );
    $form['dates']['date_to'] = array(
      '#type' => 'datelist',
      '#date_format' => 'd-m-Y',
      '#date_flexible' => 0,
      '#date_increment' => 1,
      '#date_year_range' => '-3:+0',
      '#date_part_order' => array('day', 'month', 'year'),
      '#date_time_element' => 'none',
      '#title' => t('Hasta'),
      '#default_value' => isset($query_array['date_to']) ? DrupalDateTime::createFromTimestamp($query_array['date_to']) : '',
//      '#prefix' => '<div class="pull-left">',
//      '#suffix' => '</div>',
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Filtrar por fechas'),
      '#attributes' => array('class' => ['margin-bottom-20', 'btn-primary', 'filter']),
      '#suffix' => '</div>',

    ];

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