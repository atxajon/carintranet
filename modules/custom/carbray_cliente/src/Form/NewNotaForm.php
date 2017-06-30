<?php
/**
 * @file
 * Contains \Drupal\carbray_cliente\Form\NewNotaForm.
 */
namespace Drupal\carbray_cliente\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * NewNotaForm form.
 */
class NewNotaForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'new_nota';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['nota'] = array(
      '#type' => 'textarea',
      '#title' => 'Nota',
    );
    $form['uid'] = array(
      '#type' => 'hidden',
      '#value' => 22,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear nota',
      '#attributes' => array('class' => array('btn-primary')),
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
    $nota = $form_state->getValue('nota');
    $uid = $form_state->getValue('uid');

    $user = User::load($uid);
    // Adding a new value to a multivalue field.
    $user->field_notas->appendItem($nota);
    $user->save();

    $uid = $user->id();
    drupal_set_message('Nueva nota para cliente con uid: ' . $uid . ' ha sido creada');
  }
}