<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewExpedienteForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;
use Drupal\Core\Database\DatabaseException;


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
  public function buildForm(array $form, FormStateInterface $form_state, $captacion_nid = 0, $uid = 0) {
    // Disable caching on this form.
    $form_state->setCached(FALSE);

    $form['#attributes']['class'][] = 'block';
    $form['#attributes']['id'] = 'new-expediente-captacion-nid' . $captacion_nid;

    $form['captacion'] = array(
      '#type' => 'hidden',
      '#default_value' => $captacion_nid,
    );

    $form['cliente'] = array(
      '#type' => 'hidden',
      '#default_value' => (isset($uid)) ? $uid : '',
    );

    $internal_users = get_carbray_workers(TRUE);
    $internal_users_options = [];
    foreach ($internal_users as $uid => $email) {
      $user = User::load($uid);
      $internal_users_options[$uid] = $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value;
    }
    $form['responsable'] = array(
      '#title' => 'Responsable',
      '#type' => 'checkboxes',
      '#empty_option' => ' - Selecciona responsable - ',
      '#options' => $internal_users_options,
      '#multiple' => TRUE,
      '#required' => TRUE,
    );

    $captacion_node = Node::load($captacion_nid);
    $is_cliente_cuota = $captacion_node->get('field_captacion_cliente_cuenta')->value;
    if ($is_cliente_cuota) {
      $modelos_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('modelos');
      foreach ($modelos_terms as $term) {
        $modelo_options[$term->tid] = $term->name;
      }
      $form['modelos'] = array(
        '#title' => 'Modelos',
        '#type' => 'checkboxes',
        '#empty_option' => ' - Selecciona modelo - ',
        '#options' => $modelo_options,
        '#multiple' => TRUE,
        '#required' => TRUE,
      );
    }
    else {
      $form['pack'] = array(
        '#type' => 'number',
        '#title' => 'Pack de horas',
        '#description' => t('Introduce el número de horas si es un cliente con pack de horas'),
        '#size' => '20',
        '#min' => -1,
        '#step' => 0.1,
      );
    }

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

    $tematica_parent_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('tematicas', 0, 1);
    foreach ($tematica_parent_terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    $form['exp_tematica'] = array(
      '#title' => 'Tematica',
      '#type' => 'select',
      '#empty_option' => ' - Selecciona tematica - ',
      '#options' => $term_data,
      '#ajax' => array(
        'callback' => '::expServiciosCallback',
        'wrapper' => 'exp-servicios-wrapper-' . $captacion_nid,
        'effect' => 'fade',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => 'Cargando servicios asociados...',
        ),
      ),
      '#required' => TRUE,
    );

    $tematica_tid = $form_state->getValue('exp_tematica');
    $servicios = ($tematica_tid) ? get_children_of_parent_term($tematica_tid, 'tematicas') : '';

    $servicios_options = [];
    if ($servicios) {
      foreach ($servicios as $servicio) {
        $servicios_options[$servicio->tid] = $servicio->name;
      }
    }

    $form['exp_servicios_wrapper_' . $captacion_nid] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'exp-servicios-wrapper-' . $captacion_nid],
      '#states' => array(
        'visible' => array(
          ':input[name="exp_tematica"]' => array('filled' => TRUE),
        ),
      ),
    ];

    $form['exp_servicios_wrapper_' . $captacion_nid]['servicios'] = [
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
  public function expServiciosCallback(array &$form, FormStateInterface &$form_state) {
    $form_state->setRebuild(TRUE);
    $captacion_nid = $form['captacion']['#default_value'];
    return $form['exp_servicios_wrapper_' . $captacion_nid];
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

    $tematica_tid = $form_state->getValue('exp_tematica');
    $num_expediente = assign_expediente_title($tematica_tid);
    $captacion_nid = $form_state->getValue('captacion');
    $captacion_node = Node::load($captacion_nid);
    $uid = $form_state->getValue('cliente');
    $modelos = $form_state->getValue('modelos');
    $pack_horas = $form_state->getValue('pack');
    $pack_minutos = $pack_horas * 60;
    if ($pack_minutos == 0) {
      $pack_minutos = -1;
    }
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

    // $modelos strangely adds uid 0 for every non selected modelos checkbox;
    // let's clean those up.
    $selected_modelos = array();
    foreach ($modelos as $modelos_id => $value) {
      if ($value == 0) {
        continue;
      }
      $selected_modelos[$modelos_id] = $value;
    }

    // Create expediente node.
    $expediente = Node::create(['type' => 'expediente']);
    $expediente->set('title', $num_expediente);
    $expediente->set('field_expediente_captacion', $captacion_nid);
    $expediente->set('field_expediente_responsable', $selected_responsable);
    $expediente->set('field_expediente_tematica', $values['servicios']);
    $expediente->set('field_expediente_pack_minutos', $pack_minutos);
    if ($modelos) {
      $expediente->set('field_expediente_modelos', $modelos);
    }
    $expediente->enforceIsNew();
    $expediente->save();

    /**
     * Update/Insert on custom table carbray_user_captacion_expediente.
     * If its another new expediente for an existing captacion (it takes multiple values...) CREATE new entry!
     * Logic is: if this row's user and captacion have an expediente NULL, update NULL to new expediente id;
     * else, if not NULL, insert new record.
     */

    // Query for expediente_nid from carbray_cliente_captacion_expediente table rows for this user and captacion_nid; then if NULL, proceed one way or another.
    $db = \Drupal::database();
    $has_expediente = $db->query("SELECT expediente_nid FROM carbray_user_captacion_expediente WHERE uid = :uid AND captacion_nid = :captacion_nid", array(':uid' => $uid, ':captacion_nid' => $captacion_nid))->fetchField();
    if (!$has_expediente) {
      // Update NULL to new expediente.
      $query = \Drupal::database()->update('carbray_user_captacion_expediente');
      $query->fields([
        'expediente_nid' =>  $expediente->id()
      ]);
      $query->condition('uid', $uid);
      $query->condition('captacion_nid', $captacion_nid);
      $query->execute();
    }
    else {
      // Create an entry on custom table carbray_user_captacion_expediente.
      \Drupal::database()->insert('carbray_user_captacion_expediente')
        ->fields([
          'uid',
          'captacion_nid',
          'expediente_nid',
        ])
        ->values(array(
          $uid,
          $captacion_nid,
          $expediente->id(),
        ))
        ->execute();
    }

    /**
     * Insert on custom table carbray_expediente_horas, if this is an expediente with pack de horas set.
     */
    if ($pack_minutos > 0) {
      try {
        $record = \Drupal::database()->insert('carbray_expediente_horas')
          ->fields([
            'expediente_nid',
            'original_minutes',
            'author',
          ])
          ->values(array(
            $expediente->id(),
            $pack_minutos,
            \Drupal::currentUser()->id(),
          ))
          ->execute();
        \Drupal::logger('new_expediente')->notice('New expediente with pack de horas added, entry ' . $record . ' on table carbray_expediente_horas added.');
      } catch (DatabaseException $e) {
        watchdog_exception('new_expediente_exception', $e);
        \Drupal::logger('new_expediente')->notice('New expediente with pack de horas added but unable to add entry on table carbray_expediente_horas!');
      }
    }

      drupal_set_message('Expediente ' . $num_expediente . ' para ' . $captacion_node->label() . ' ha sido creado');

    // Redirect to the newly created expediente.
    $options = ['absolute' => TRUE];
    $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $expediente->id()], $options);
    $url = $url->toString();
    $response = new RedirectResponse($url);
    $response->send();
  }
}
