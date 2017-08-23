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
  public function buildForm(array $form, FormStateInterface $form_state, $uid = NULL) {
    $db = \Drupal::database();

    // Query for all other departmaentos but current tid's one.
    $sql = "SELECT tid FROM taxonomy_term_field_data WHERE vid= 'estado_de_captacion'";
    $estados_tids = $db->query($sql)->fetchCol();

    $estados_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadMultiple($estados_tids);

    foreach ($estados_terms as $estado_term) {
      $options[$estado_term->id()] = $estado_term->name->value;
    }


    $form['cliente_uid'] = array(
      '#type' => 'hidden',
      '#value' => $uid,
    );
    $form['estado'] = array(
      '#type' => 'select',
      '#title' => 'Estado captacion',
      '#empty_option' => ' - Selecciona estado captacion - ',
      '#options' => $options,
      '#multiple' => TRUE,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Asignar estado',
      '#attributes' => array('class' => array('btn-success', 'btn-xs')),
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

    // @todo: following should work but always return first uid on the page;
    // Needed to resort to using raw and unrecommended getUserInput...
    // $uid = $form_state->getValue('cliente_uid');
    $user_input = $form_state->getUserInput();
    $uid = $user_input['cliente_uid'];
    $estado = $form_state->getValue('estado');

    $user = \Drupal\user\Entity\User::load($uid);
    $user->set('field_user_estado_de_captacion', $estado);
    $user->save();

    $nombre = $user->get('field_nombre')->value;
    $apellido = $user->get('field_apellido')->value;
    $nombre_apellido = $nombre . ' ' . $apellido;

    drupal_set_message('Cliente ' . $nombre_apellido . ' con uid: ' . $uid . ' ha sido asignado nuevo estado de captacion');
  }
}