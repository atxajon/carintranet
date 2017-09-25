<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewExpedienteForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;


/**
 * NewExpedienteForm form.
 */
class NewExpedienteForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_new_expediente';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'block';

    // Get cliente uid from url query string.
    $uid = \Drupal::request()->query->get('uid');

    $form['cliente'] = array(
      '#type' => 'hidden',
      '#default_value' => (isset($uid)) ? $uid : '',
    );

//    $form['factura'] = array(
//      '#title' => 'Factura',
//      '#description' => t('Busca la factura tecleando su titulo'),
//      '#type' => 'entity_autocomplete',
//      '#target_type' => 'node',
//      '#selection_handler' => 'default',
//      '#selection_settings' => array(
//        'target_bundles' => array('factura'),
//      ),
//    );

    $internal_users = get_carbray_workers(TRUE);
    $form['responsable'] = array(
      '#title' => 'Captador',
      '#type' => 'checkboxes',
      '#empty_option' => ' - Selecciona captador - ',
      '#options' => $internal_users,
      '#multiple' => TRUE,
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
        'wrapper' => 'servicios-wrapper',
        // Effect when replacing content. Options: 'none' (default), 'slide', 'fade'.
        'effect' => 'fade',
        // Javascript event to trigger Ajax. Currently for: 'onchange'.
        'event' => 'change',
        'progress' => array(
          // Graphic shown to indicate ajax. Options: 'throbber' (default), 'bar'.
          'type' => 'throbber',
          // Message to show along progress graphic. Default: 'Please wait...'.
          'message' => 'Cargando servicios asociados...',
        ),
      ),
    );

    $form['servicios_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'servicios-wrapper', 'class' => array('form-item')],
    ];


    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear expediente',
      '#attributes' => array('class' => array('btn-primary')),
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
  public function serviciosCallback(array &$form, FormStateInterface $form_state) {
    $tematica_tid = $form_state->getValue('tematica');

    // Get child terms of currently selected parent $tematica_tid.
    $tematica_child_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('tematicas', $tematica_tid, 1);

    foreach ($tematica_child_terms as $term) {
      $term_data[$term->tid] = $term->name;
    }

    $form['servicios_wrapper']['servicios'] = [
      '#type' => 'select',
      '#title' => $this->t('Servicios'),
      '#options' => $term_data,
    ];

    return $form['servicios_wrapper'];
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

    $num_expediente = $form_state->getValue('num_expediente');
    $cliente = $form_state->getValue('cliente');
    $factura = $form_state->getValue('factura');
    $tematica = $form_state->getValue('tematica');
    $responsable = $form_state->getValue('responsable');



    $expediente = Node::create(['type' => 'expediente']);
    $expediente->set('title', $num_expediente);
    $expediente->set('field_expediente_cliente', $cliente);
    $expediente->set('field_expediente_factura', $factura);
    $expediente->set('field_expediente_responsable', $responsable);
    $expediente->set('field_expediente_tematica', $tematica);
    $expediente->enforceIsNew();
    $expediente->save();

    $nid = $expediente->id();
    drupal_set_message('Expediente ' . $num_expediente . ' (nid: ' . $nid . ') ha sido creado');
//    $form_state['redirect'] = '<front>';
  }
}
