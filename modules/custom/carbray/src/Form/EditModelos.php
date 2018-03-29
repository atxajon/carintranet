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

    $expediente_modelos = get_expediente_modelos($nid);

    $expediente_modelos_options = [];
    $expediente_modelos_completed = [];
    foreach ($expediente_modelos as $expediente_modelo) {
      $term = Term::load($expediente_modelo['modelos_tid']);
      $expediente_modelos_options[$expediente_modelo['modelos_tid']] = $term->name->value;
      if ($expediente_modelo['completed'] == 1) {
        $expediente_modelos_completed[] = $expediente_modelo['modelos_tid'];
      }
    }

    $form['modelos'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Modelos',
      '#options' => $expediente_modelos_options,
      '#default_value' => $expediente_modelos_completed,
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
    $nid = $form_state->getValue('expediente_nid');
    $modelos = $form_state->getValue('modelos');

    foreach ($modelos as $modelo_tid => $is_selected) {
      // Update modelo_tid to completed.
      $query = \Drupal::database()->update('carbray_expediente_modelos');
      $query->fields([
        'completed' => ($is_selected) ? 1 : 0,
      ]);
      $query->condition('expediente_nid', $nid);
      $query->condition('modelos_tid', $modelo_tid);
      $query->execute();
    }

    drupal_set_message('Lista de modelos actualizada.');
  }
}