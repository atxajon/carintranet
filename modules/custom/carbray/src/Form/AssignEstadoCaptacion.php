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
 * NewClientForm form.
 */
class AssignEstadoCaptacion extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'assign_estado_captacion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $db = \Drupal::database();

    // Query for all estados de captaction terms.
    $sql = "SELECT tid FROM taxonomy_term_field_data WHERE vid= 'estado_de_captacion'";
    $estados_tids = $db->query($sql)->fetchCol();

    $estados_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadMultiple($estados_tids);

    foreach ($estados_terms as $estado_term) {
      $options[$estado_term->id()] = $estado_term->name->value;
    }

    // Get this user's estado de captacion.
    $sql = "SELECT field_captacion_estado_captacion_target_id FROM node__field_captacion_estado_captacion WHERE entity_id = :nid";
    $default_estado = $db->query($sql, array(':nid' => $nid))->fetchCol();

    $form['captacion_nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );
    $form['estado'] = array(
      '#type' => 'select',
      '#title' => 'Cambiar estado',
      '#empty_option' => ' - Selecciona estado captacion - ',
      '#options' => $options,
    );
    if ($default_estado) {
      $form['estado']['#default_value'] = $default_estado;
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Asignar como nuevo estado',
      '#attributes' => array('class' => array('btn-primary', 'btn-sm')),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $estado = $form_state->getValue('estado');
    if (!$estado) {
      $form_state->setErrorByName('estado', t('Selecciona un estado de captacion.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // @todo: following should work but always return first uid on the page;
    // Needed to resort to using raw and unrecommended getUserInput...
    $user_input = $form_state->getUserInput();
    $nid = $user_input['captacion_nid'];
    $estado = $form_state->getValue('estado');

    $captacion = Node::load($nid);
    $previous_status = $captacion->get('field_captacion_estado_captacion')->getValue();
    $captacion->set('field_captacion_estado_captacion', $estado);
    $captacion->save();

    if ($previous_status[0]['target_id'] != $estado) {
      // Save changes to custom captacion changes log.
      $values_to_save = [
        'nid' => $nid,
        'previous_status' => $previous_status[0]['target_id'],
        'new_status' => $estado,
      ];
      log_estado_captacion_change($values_to_save);
    }


    drupal_set_message('Captacion con nid: ' . $nid . ' ha sido asignado nuevo estado de captacion');
  }
}