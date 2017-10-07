<?php
/**
 * @file
 * Contains \Drupal\carbray_cliente\Form\NewNotaForm.
 */
namespace Drupal\carbray_cliente\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;


/**
 * EditCaptadores form.
 */
class EditCaptadores extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_captadores';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $captacion_nid = 0) {

    $internal_users = get_carbray_workers(TRUE);
    $current_captador = get_captacion_captador($captacion_nid);
    $form['captador'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Captador',
      '#options' => $internal_users,
      '#default_value' => $current_captador,
      '#multiple' => TRUE,
      '#required' => TRUE,
    );
    $form['nid'] = array(
      '#type' => 'hidden',
      '#value' => $captacion_nid,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Asignar captadores',
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


    $captacion_nid = $form_state->getValue('nid');

    $captacion_node = Node::load($captacion_nid);
//    // Adding a new value to a multivalue field.
//    $user->field_notas->appendItem($nota);
    $captacion_node->set('field_captacion_captador', $selected_captador);
    $captacion_node->save();

    drupal_set_message('Captadores asignados');
  }
}