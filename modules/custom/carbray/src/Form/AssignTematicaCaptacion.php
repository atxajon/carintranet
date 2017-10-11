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
class AssignTematicaCaptacion extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'assign_tematica_captacion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $captacion_nid = 0) {
    $form['#attributes']['id'] = 'assign-tematica-captacion-nid' . $captacion_nid;
    $form['#attributes']['class'][] = 'form-in-modal';

    $form['captacion'] = array(
      '#type' => 'hidden',
      '#default_value' => $captacion_nid,
    );

    $tematica_parent_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('tematicas', 0, 1);
    foreach ($tematica_parent_terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    $form['tematica'] = array(
      '#title' => 'Tematica',
      '#type' => 'select',
      '#empty_option' => ' - Selecciona tematica - ',
      '#options' => $term_data,
      '#ajax' => array(
        'callback' => '::serviciosCallback',
        'wrapper' => 'servicios-wrapper-' . $captacion_nid,
        'effect' => 'fade',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => 'Cargando servicios asociados...',
        ),
      ),
      '#required' => TRUE,
    );

    $tematica_tid = $form_state->getValue('tematica');
    $servicios = ($tematica_tid) ? get_children_of_parent_term($tematica_tid, 'tematicas') : '';

    $servicios_options = [];
    if ($servicios) {
      foreach ($servicios as $servicio) {
        $servicios_options[$servicio->tid] = $servicio->name;
      }
    }

    $form['servicios_wrapper_' . $captacion_nid] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'servicios-wrapper-' . $captacion_nid],
      '#states' => array(
        'visible' => array(
          ':input[name="tematica"]' => array('filled' => TRUE),
        ),
      ),
    ];

    $form['servicios_wrapper_' . $captacion_nid]['servicios'] = [
      '#type' => 'select',
      '#title' => $this->t('Servicios'),
      '#options' => $servicios_options,
      '#empty_option' => ' - Selecciona servicio - ',
      '#required' => TRUE,
    ];

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Asignar tematica',
      '#attributes' => array('class' => array('btn-primary', 'btn-sm', 'margin-top-20')),
    );
    return $form;
  }

  /**
   * Implements callback for Ajax event on tematica selection.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current state of form.
   *
   * @return array
   *   Color selection section of the form.
   */
  public function serviciosCallback(array &$form, FormStateInterface &$form_state) {
    $form_state->setRebuild(TRUE);
    $captacion_nid = $form['captacion']['#default_value'];
    return $form['servicios_wrapper_' . $captacion_nid];
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

    $captacion_nid = $form_state->getValue('captacion');
    $values = $form_state->getValues();

    $captacion = Node::load($captacion_nid);
    $captacion->field_captacion_tematica->appendItem($values['servicios']);
    $captacion->save();

    drupal_set_message('Nueva tematica asignada a captacion.');
  }
}