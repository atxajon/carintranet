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
 * ToggleUserStatus form.
 */
class ToggleUserStatus extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'toggle_user_status';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uid = NULL) {
    $user = User::load($uid);
    $status = $user->get('status')->value;
    $status_text = ($status) ? 'activo' : 'inactivo';
    $button_text = ($status) ? 'Desactivar trabajador' : 'Activar trabajador';
    $form['system_status'] = array(
      '#markup' => 'Trabajador está <b>' . $status_text . '</b><br>',
    );

    $form['uid'] = array(
      '#type' => 'hidden',
      '#value'  => $uid,
    );
    $form['status'] = array(
      '#type' => 'hidden',
      '#value'  => $status,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $button_text,
      '#attributes' => array('class' => array('btn-warning', 'btn-sm')),
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

    $status = $form_state->getValue('status');
    $uid = $form_state->getValue('uid');

    $user = User::load($uid);
    $new_status = ($status) ? 0 : 1;
    $user->set('status', $new_status);
    $user->save();

    drupal_set_message('El usuario ha cambiado su estado de sistema');
  }
}