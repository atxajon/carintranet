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
 * NewCaptacionForm form.
 */
class NewCaptacionForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_new_captacion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uid = 0) {
    // Disable caching on this form.
    $form_state->setCached(FALSE);

    $form['#attributes']['class'][] = 'block';

    $form['cliente_uid'] = array(
      '#type' => 'hidden',
      '#default_value' => $uid,
    );

    $internal_users = get_carbray_workers(TRUE);
    $form['captador'] = array(
      '#title' => 'Captadores',
      '#type' => 'checkboxes',
      '#empty_option' => ' - Selecciona captador - ',
      '#options' => $internal_users,
      '#multiple' => TRUE,
      '#required' => TRUE,
    );
//
//    $tematica_parent_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('tematicas', 0, 1);
//    foreach ($tematica_parent_terms as $term) {
//      $term_data[$term->tid] = $term->name;
//    }
//    $form['tematica'] = array(
//      '#title' => 'Tematica',
//      '#type' => 'select',
//      '#empty_option' => ' - Selecciona tematica - ',
//      '#options' => $term_data,
//      '#ajax' => array(
//        'callback' => '::serviciosCallback',
//        'wrapper' => 'servicios-wrapper',
//        'effect' => 'fade',
//        'event' => 'change',
//        'progress' => array(
//          'type' => 'throbber',
//          'message' => 'Cargando servicios asociados...',
//        ),
//      ),
//      '#required' => TRUE,
//    );
//
//    $tematica_tid = $form_state->getValue('tematica');
//    $servicios = ($tematica_tid) ? get_children_of_parent_term($tematica_tid, 'tematicas') : '';
//
//    $servicios_options = [];
//    if ($servicios) {
//      foreach ($servicios as $servicio) {
//        $servicios_options[$servicio->tid] = $servicio->name;
//      }
//    }
//
//    $form['servicios_wrapper'] = [
//      '#type' => 'container',
//      '#attributes' => ['id' => 'servicios-wrapper'],
//      '#states' => array(
//        'visible' => array(
//          ':input[name="tematica"]' => array('filled' => TRUE),
//        ),
//      ),
//    ];
//
//    $form['servicios_wrapper']['servicios'] = [
//      '#type' => 'select',
//      '#title' => $this->t('Servicios'),
//      '#options' => $servicios_options,
//      '#required' => TRUE,
//    ];


    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear captacion',
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

    //$tematica_tid = $form_state->getValue('tematica');
    $uid = $form_state->getValue('cliente_uid');
    $captador = $form_state->getValue('captador');

    // $captador strangely adds uid 0 for every non selected responsable checkbox;
    // let's clean those up.
    $selected_captador = array();
    foreach ($captador as $captador_id => $value) {
      if ($value == 0) {
        continue;
      }
      $selected_captador[$captador_id] = $value;
    }

    $user = User::load($uid);

    $now = date('d-m-Y', time());
    $title = 'Captacion ' . $now . ' para cliente: ' . $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value;
    $captacion = Node::create(['type' => 'captacion']);
    $captacion->set('title', $title);
    $captacion->set('field_captacion_cliente', $uid);
    $captacion->set('field_captacion_captador', $selected_captador);
    $captacion->enforceIsNew();
    $captacion->save();

    // Create an entry on custom table carbray_user_captacion_expediente.
    \Drupal::database()->insert('carbray_user_captacion_expediente')
      ->fields([
        'uid',
        'captacion_nid',
      ])
      ->values(array(
        $uid,
        $captacion->id(),
      ))
      ->execute();

    drupal_set_message($title . ' ha sido creada');
  }
}
