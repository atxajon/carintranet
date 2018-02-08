<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewExpedienteForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;


/**
 * AddExpedienteHours form.
 */
class AddExpedienteHours extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_add_expediente_hours';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $expediente_nid = 0) {
    // Disable caching on this form.
    $form_state->setCached(FALSE);

    $form['expediente_nid'] = array(
      '#type' => 'hidden',
      '#default_value' => $expediente_nid,
    );

    $form['horas'] = array(
      '#title' => 'Numero de horas que se añaden',
      '#type' => 'textfield',
      '#required' => TRUE,
    );

    $form['tipo_horas'] = [
      '#type' => 'radios',
      '#title' => t('Tipo de horas'),
      '#options' => array(0 => $this->t('De cortesía'), 1 => $this->t('Normales')),
//      '#default_value' => 1,
      '#required' => TRUE,
    ];

    // Does this expediente have a pack de horas set? if so pass it to js timer file.
//    $expediente = Node::load($expediente_nid);
//    $pack = $expediente->get('field_expediente_pack_minutos')->value;




    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Añadir horas',
      '#attributes' => array('class' => array('btn-primary', 'add-hours')),
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

    $expediente_nid = $form_state->getValue('expediente_nid');

    // If it's an expediente with pack de horas update remaining time.
    // When this actuacion started we had originally stored $actuacion_started_minutes.
    $actuacion_started_minutes = $is_pack;
    // The absolute time passed on this current actuacion is:
    $minutes_passed_on_this_actuacion = $actuacion_started_minutes - $timer;

    $expediente = Node::load($expediente_nid);
    // Currently stored pack minutes (we need to check against this, as another worker could have done an actuacion in paralel while this worker submits his!
    $current_pack_minutes = $expediente->get('field_expediente_pack_minutos')->value;

    $updated_pack_remaining_minutes = $current_pack_minutes - $minutes_passed_on_this_actuacion;

    // Store the subtracted minutes in hours for the remaining time in the pack.
    $expediente->set('field_expediente_pack_minutos', $updated_pack_remaining_minutes);
    $expediente->save();

    // $timer instead of being the countdown value, takes the elapsed minutes value.
    $timer = $minutes_passed_on_this_actuacion;

    drupal_set_message('Actuacion ' . $title . ' ha sido creada');
  }
}
