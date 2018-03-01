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
 * AssignJefe form.
 */
class AssignJefe extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'assign_jefe';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tid = 0, $jefe_uid = 0) {

    $form['#attributes']['id'] = 'assign-jefe-' . $tid;

    $dept_workers = get_departamento_workers($tid);
    $options = [];
    foreach ($dept_workers as $dept_worker) {
      $worker = User::load($dept_worker->uid);
      $options[$dept_worker->uid] = $worker->get('field_nombre')->value . ' ' . $worker->get('field_apellido')->value;
    }
    $form['dept_workers'] = [
      '#type' => 'radios',
      '#options' => $options,
      '#required' => TRUE,
      '#validated' => TRUE,
    ];
    if ($jefe_uid) {
      $form['dept_workers']['#default_value'] = $jefe_uid;
    }

    $form['departamento_current_jefe'] = array(
      '#type' => 'hidden',
      '#value' => $jefe_uid,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Asignar como Jefe',
      '#attributes' => array('class' => ['btn', 'btn-primary']),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $user_input = $form_state->getUserInput();
    $chosen_uid = $user_input['dept_workers'];
    if (!$chosen_uid) {
      $form_state->setErrorByName('dept_workers', 'Selecciona un trabajador de la lista.');
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Needed to resort to using raw and unrecommended getUserInput...
    $user_input = $form_state->getUserInput();
    $chosen_uid = $user_input['dept_workers'];
    $current_jefe_uid = $user_input['departamento_current_jefe'];

    if ($current_jefe_uid) {
      // Downgrade current jefe to non jefe_departamento.
      $worker = User::load($current_jefe_uid);
      if ($worker->hasRole('jefe_departamento')) {
        $worker->removeRole('jefe_departamento');
        $worker->addRole('worker');
        $worker->save();
      }
    }

    // And promote the chosen user as new jefe.
    $chosen_user = User::load($chosen_uid);
    $chosen_user->addRole('jefe_departamento');
    // 'worker' role removal is necessary to avoid duplicated left hand menus...
    $chosen_user->removeRole('worker');
    $chosen_user->save();
    drupal_set_message('Jefe asignado');
  }
}