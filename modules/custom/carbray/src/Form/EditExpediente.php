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
 * EditExpediente form.
 */
class EditExpediente extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_expediente';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $form['#attributes']['class'][] = 'form-in-modal';
    $form['expediente_nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );

    $internal_users = get_carbray_workers(TRUE);
    $internal_users_options = [];
    foreach ($internal_users as $uid => $email) {
      $user = User::load($uid);
      $internal_users_options[$uid] = $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value;
    }
    $current_responsable = get_expediente_responsable($nid);
    $form['responsable'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Responsable(s)',
      '#options' => $internal_users_options,
      '#default_value' => $current_responsable,
      '#multiple' => TRUE,
      '#required' => TRUE,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Guardar expediente',
      '#attributes' => array('class' => array('btn-primary', 'btn')),
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

    $responsable = $form_state->getValue('responsable');
    // $captador strangely adds uid 0 for every non selected captador checkbox;
    // let's clean those up.
    $selected_responsable = array();
    foreach ($responsable as $responsable_id => $value) {
      if ($value == 0) {
        continue;
      }
      $selected_responsable[$responsable_id] = $value;
    }

    $nid = $form_state->getValue('expediente_nid');

    $expediente = Node::load($nid);
    $expediente->set('field_expediente_responsable', $selected_responsable);
    $expediente->save();

    drupal_set_message('Expediente guardado.');
  }
}