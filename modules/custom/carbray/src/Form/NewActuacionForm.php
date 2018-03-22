<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewExpedienteForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\DatabaseException;


/**
 * NewActuacionForm form.
 */
class NewActuacionForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_new_actuacion';
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

    $form['title'] = array(
      '#title' => 'Actuacion',
      '#type' => 'textfield',
      '#required' => TRUE,
    );

    $form['#attached']['library'][] = 'carbray/carbray.carbray_timer';


    // When user does not populate 'pack de horas' form field on new expediente form, default inserted on db is -1.
    $pack = -1;

    // Does this expediente have a pack de horas set? if so pass it to js timer file.
    $is_pack = \Drupal::database()->query("SELECT expediente_nid FROM carbray_expediente_horas WHERE expediente_nid = :expediente_nid LIMIT 1", array(':expediente_nid' => $expediente_nid))->fetchField();
    if ($is_pack) {
      $expediente = Node::load($expediente_nid);
      $pack = $expediente->get('field_expediente_pack_minutos')->value;
    }


    if ($pack > 0) {
      // If is an actuacion for an expediente with pack de horas, pass the
      // value to js to set the timer to countdown.
      $form['#attached']['drupalSettings']['pack_minutos'] = $pack * 60;
      $crono_time = gmdate("H:i:s", $pack * 60);
      $form['start'] = array(
        '#type' => 'button',
        '#value' => 'Comenzar',
        '#prefix' => '<div class="pull-left clearfix timer-container"><div class="pull-left crono-wrapper"><h2 id="crono" class="no-margin crono-heading pull-left">' . $crono_time . '</h2>',
        '#attributes' => array('class' => array('btn-primary', 'margin-bottom-20', 'start-timer-btn')),
      );
      $form['resume'] = array(
        '#type' => 'button',
        '#value' => 'Continuar',
        '#attributes' => array(
          'class' => array(
            'btn-primary',
            'margin-bottom-20',
            'resume-timer-btn',
            'hidden'
          )
        ),
      );
      $form['pause'] = array(
        '#type' => 'button',
        '#value' => 'Pausar',
        '#attributes' => array('class' => array('hidden', 'pause-timer-btn', 'btn-warning')),
      );

      $timer_tooltip = 'Edita el numero de minutos transcurridos.';
      $form['timer'] = array(
        '#type' => 'textfield',
        '#description' => $timer_tooltip,
        '#required' => TRUE,
        '#prefix' => '<div class="pull-right timer-textfield">',
        '#attributes' => array('class' => array('hidden')),
        '#suffix' => '</div></div></div>',
      );
    }
    elseif ($pack == '0') {
      // If is an actuacion for an expediente that has a pack the horas that has now run out (e.g === 0 minutes left).
      $form['start'] = array(
        '#markup' => '<div class="pull-left timer-container"></div>',
      );
    }
    else {
      // Expedientes that were created before pack de horas functionality will fall to this (NULL!).
      $crono_time = '00:00:00';
      $form['start'] = array(
        '#type' => 'button',
        '#value' => 'Comenzar',
        '#prefix' => '<div class="pull-left clearfix timer-container"><div class="pull-left crono-wrapper"><h2 id="crono" class="no-margin crono-heading pull-left">' . $crono_time . '</h2>',
        '#attributes' => array(
          'class' => array(
            'btn-primary',
            'margin-bottom-20',
            'start-timer-btn'
          )
        ),
      );
      $form['resume'] = array(
        '#type' => 'button',
        '#value' => 'Continuar',
        '#attributes' => array(
          'class' => array(
            'btn-primary',
            'margin-bottom-20',
            'resume-timer-btn',
            'hidden'
          )
        ),
      );
      $form['pause'] = array(
        '#type' => 'button',
        '#value' => 'Pausar',
        '#attributes' => array('class' => array('hidden', 'pause-timer-btn', 'btn-warning')),
      );

      $timer_tooltip = ($pack) ? 'Edita el numero de minutos restantes para concluir el pack de horas' : 'Edita el numero de minutos transcurridos.';
      $form['timer'] = array(
        '#type' => 'textfield',
        '#description' => $timer_tooltip,
        '#required' => TRUE,
        '#prefix' => '<div class="pull-right timer-textfield">',
        '#attributes' => array('class' => array('hidden')),
        '#suffix' => '</div></div></div>',
      );
    }

    $form['is_pack'] = array(
      '#type' => 'hidden',
      '#default_value' => $pack,
    );

    $form['nota_container'] = array(
      '#type' => 'container',
      '#attributes' => array('class' => array('margin-bottom-20 margin-top-20')),
    );
    $form['nota_container']['nota'] = array(
      '#type' => 'text_format',
      '#title' => 'Notas de la actuacion',
      '#format' => 'basic_html',
      '#rows' => 5,
    );

    $allowed_exts = array('jpg jpeg gif png txt doc xls xlsx pdf ppt pptx pps odt ods odp docx zip rar msg');
    $form['actuacion_file'] = array(
      '#type' => 'managed_file',
      '#name' => 'my_file',
      '#title' => t('Añadir Documentacion'),
      '#size' => 20,
      '#description' => t('Allowed Files - jpg jpeg gif png txt doc xls xlsx pdf ppt pptx pps odt ods odp docx zip rar msg'),
      '#upload_validators' => array('file_validate_extensions' => $allowed_exts),
      '#upload_location' => 'private://actuacion/',
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Añadir actuacion',
      '#attributes' => array('class' => array('btn-primary', 'create-actuacion')),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    if (!$title) {
      $form_state->setErrorByName('title', t('Por favor añade un texto para dar nombre a la actuacion.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $expediente_nid = $form_state->getValue('expediente_nid');
    $title = $form_state->getValue('title');
    $timer = $form_state->getValue('timer');
    $is_pack = $form_state->getValue('is_pack');
    $nota = $form_state->getValue('nota');
    $actuacion_file = $form_state->getValue('actuacion_file');

    // If it's an expediente with pack de horas update remaining time.
    if ($is_pack != -1) {
      $expediente = Node::load($expediente_nid);
      // Currently stored pack minutes (we need to check against this, as another worker could have done an actuacion in paralel while this worker submits his!
      $current_pack_minutes = $expediente->get('field_expediente_pack_minutos')->value;

      $updated_pack_remaining_minutes = $current_pack_minutes - $timer;

      // Store the subtracted minutes in hours for the remaining time in the pack.
      $expediente->set('field_expediente_pack_minutos', $updated_pack_remaining_minutes);
      $expediente->save();
    }


    $actuacion = Node::create(['type' => 'actuacion']);
    $actuacion->set('title', $title);
    $actuacion->set('field_actuacion_expediente', $expediente_nid);
    // Dont get confused with 'field_actuacion_tiempo_en_seg' machinename, it was originally created
    // to deal with saving seconds, but in reality it's dealing with minutes...
    $actuacion->set('field_actuacion_tiempo_en_seg', $timer);
    $actuacion->set('field_actuacion_documentacion', $actuacion_file);
    $actuacion->enforceIsNew();
    $actuacion->save();

    // Now that we have an actuacion nid we can create a Nota node that references it.
    $nota_node = Node::create(['type' => 'nota']);
    $nota_node->set('title', 'Nota para id ' . $actuacion->id() . ' creada el ' . date('d-M-Y H:m:s', time()));
    $nota_node->set('field_nota_nota', $nota);
    $nota_node->enforceIsNew();
    $nota_node->save();

    // Now that we have a Nota we can reload our actuacion and save the reference to the Nota.
    $actuacion = Node::load($actuacion->id());
    $actuacion->set('field_actuacion_nota', $nota_node->id());
    $actuacion->save();

    if ($is_pack != -1) {
      /**
       * Store on custom table the reference between this actuacion and the iteration of the expediente pack de horas we're currently on;
       * This way we can tell current actuacion is for an expediente with pack de horas of type facturables or cortesia.
       */
      // Get the current iteration of the expediente's pack de horas, which is the last record on the {carbray_expediente_horas} table.
      $expediente_horas_id = \Drupal::database()->query("SELECT id
      FROM carbray_expediente_horas 
    WHERE 
     expediente_nid = :expediente_nid ORDER BY id DESC LIMIT 1", [':expediente_nid' => $expediente_nid])->fetchField();
      try {
        $record = \Drupal::database()->insert('carbray_expediente_horas_actuaciones')
          ->fields([
            'actuacion_nid',
            'expediente_horas_id',
          ])
          ->values(array(
            $actuacion->id(),
            $expediente_horas_id,
          ))
          ->execute();
        \Drupal::logger('NewActuacionForm')->notice('New actuacion for expediente with pack de horas added, entry ' . $record . ' on table carbray_expediente_horas_actuaciones added referencing expediente_horas_id: ' . $expediente_horas_id .  ' on table carbray_expediente_horas');
      } catch (DatabaseException $e) {
        watchdog_exception('NewActuacionForm', $e);
        \Drupal::logger('NewActuacionForm')->notice('New actuacion for expediente with pack de horas added but unable to add entry on table carbray_expediente_horas_actuaciones!');
      }
    }

    drupal_set_message('Actuacion ' . $title . ' ha sido creada');
  }
}
