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
    // Disable caching on this form.
    $form_state->setCached(FALSE);

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
      '#required' => TRUE,
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

    $form['servicios_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'servicios-wrapper'],
      '#states' => array(
        'visible' => array(
          ':input[name="tematica"]' => array('filled' => TRUE),
        ),
      ),
    ];

    $form['servicios_wrapper']['servicios'] = [
      '#type' => 'select',
      '#title' => $this->t('Servicios'),
      '#options' => $servicios_options,
      '#required' => TRUE,
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
  public function serviciosCallback(array &$form, FormStateInterface &$form_state) {
    $form_state->setRebuild(TRUE);
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

    $tematica_tid = $form_state->getValue('tematica');
    $num_expediente = assign_expediente_title($tematica_tid);
    $cliente = $form_state->getValue('cliente');
//    $factura = $form_state->getValue('factura');
    $values = $form_state->getValues();
    $responsable = $form_state->getValue('responsable');

    // $responsable strangely adds uid 0 for every non selected responsable checkbox;
    // let's clean those up.
    $selected_responsable = array();
    foreach ($responsable as $responsable_id => $value) {
      if ($value == 0) {
        continue;
      }
      $selected_responsable[$responsable_id] = $value;
    }

    // Create expediente node.
    $expediente = Node::create(['type' => 'expediente']);
    $expediente->set('title', $num_expediente);
    $expediente->set('field_expediente_cliente', $cliente);
//    $expediente->set('field_expediente_factura', $factura);
    $expediente->set('field_expediente_responsable', $selected_responsable);
    $expediente->set('field_expediente_tematica', $values['servicios']);
    $expediente->enforceIsNew();
    $expediente->save();

    // @todo: assign new estado de captacion of client to transition it to produccion.
    $user = User::load($cliente);
    $user->set('field_fase', 'produccion');
    $user->save();

    drupal_set_message('Expediente ' . $num_expediente . ' para cliente ' . $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value . ' ha sido creado');
    $form_state->setRedirectUrl(_carbray_redirecter());
  }
}
