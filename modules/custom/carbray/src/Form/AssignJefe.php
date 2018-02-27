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
  public function buildForm(array $form, FormStateInterface $form_state, $tid = 0) {

    $form['departamento_tid'] = array(
      '#type' => 'hidden',
      '#value' => $tid,
    );
    $dept_workers = get_departamento_workers($tid);
    $options = [];
    foreach ($dept_workers as $dept_worker) {
      $worker = User::load($dept_worker->uid);
      $options[$dept_worker->uid] = $worker->get('field_nombre')->value . ' ' . $worker->get('field_apellido')->value;
      // Work out if this user is the jefe of departamento.
      $jefe_uid = 0;
      $worker_roles = $worker->getRoles();
      if (in_array('jefe_departamento', $worker_roles)) {
        $jefe_uid = $dept_worker->uid;
      }
    }

    $form['dept_workers'] = [
      '#type' => 'radios',
      '#options' => $options,
      '#required' => TRUE,
    ];
    if ($jefe_uid) {
      $form['dept_workers']['#default_value'] = $jefe_uid;
    }

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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->getValue('dept_workers');
    $user = User::load($uid);
    $user->addRole('jefe_departamento');
    $user->save();
    drupal_set_message('Jefe asignado');
  }
}