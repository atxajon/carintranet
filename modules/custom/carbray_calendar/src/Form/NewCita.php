<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray_calendar\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;


/**
 * CalendarFilters form.
 */
class NewCita extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'new_cita';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
//    $form['#attributes']['class'][] = 'margin-left-20';


    $form['title'] = array(
      '#title' => 'Titulo',
      '#type' => 'textfield',
      '#required' => TRUE,
    );

    $internal_users = get_carbray_workers(TRUE);
    $internal_users_options = [];
    foreach ($internal_users as $uid => $email) {
      $user = User::load($uid);
      $internal_users_options[$uid] = $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value;
    }
    $current_user = \Drupal::currentUser();
    $current_user_uid = $current_user->id();
    $form['invitado'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Invitad@(s)',
      '#options' => $internal_users_options,
      '#default_value' => array($current_user_uid),
      '#multiple' => TRUE,
    );
    $form['fecha_inicio'] = array(
      '#type' => 'datetime',
      '#title' => 'Fecha/hora',
      '#size' => '20',
      '#default_value' => DrupalDateTime::createFromTimestamp(time()),
      '#required' => TRUE,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Crear cita'),
      '#attributes' => array('class' => array('btn-success', 'margin-top-20')),
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
    $title = $form_state->getValue('title');
    $fecha_inicio = $form_state->getValue('fecha_inicio');
    $formatted_fecha_inicio = $fecha_inicio->format('Y-m-d\TH:i:s');
    $invitado = $form_state->getValue('invitado');
    // $invitado strangely adds uid 0 for every non selected invitado checkbox;
    // let's clean those up.
    $selected_invitado = array();
    foreach ($invitado as $invitado_id => $value) {
      if ($value == 0) {
        continue;
      }
      $selected_invitado[$invitado_id] = $value;
    }

    $cita = Node::create(['type' => 'cita']);
    $cita->set('title', $title);
    $cita->set('field_cita_invitado', $selected_invitado);
    $cita->set('field_cita_hora', $formatted_fecha_inicio);
    $cita->enforceIsNew();
    $cita->save();
    drupal_set_message('Cita creada');
  }
}