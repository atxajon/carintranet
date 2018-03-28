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
use Drupal\taxonomy\Entity\Term;

/**
 * EditModelos form.
 */
class EditModelos extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_modelos';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $form['expediente_nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );


    $expediente_node = Node::load($nid);
    $expediente_modelos = $expediente_node->get('field_expediente_modelos')->getValue();

    $modelos_options = [];
    foreach ($expediente_modelos as $expediente_modelo) {
      $term = Term::load($expediente_modelo['target_id']);
      $modelos_options[$expediente_modelo['target_id']] = $term->name->value;
    }
    $form['modelos'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Modelos',
      '#options' => $modelos_options,
//      '#default_value' => $current_responsable,
      '#multiple' => TRUE,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Actualizar',
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

    $modelos = $form_state->getValue('modelos');
    // $captador strangely adds uid 0 for every non selected captador checkbox;
    // let's clean those up.
    $selected_modelos = array();
    foreach ($modelos as $modelo_tid => $value) {
      if ($value == 0) {
        continue;
      }
      $selected_modelos[$modelo_tid] = $value;
    }

    $nid = $form_state->getValue('expediente_nid');

    $expediente = Node::load($nid);
    $expediente->set('field_expediente_modelos', $selected_modelos);
    $expediente->save();

    drupal_set_message('Lista de modelos actualizada.');
  }
}