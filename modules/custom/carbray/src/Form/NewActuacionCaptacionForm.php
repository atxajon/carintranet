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
 * NewActuacionCaptacionForm form.
 */
class NewActuacionCaptacionForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_new_actuacion_captacion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $captacion_nid = 0) {
    // Disable caching on this form.
    $form_state->setCached(FALSE);

    $form['captacion_nid'] = array(
      '#type' => 'hidden',
      '#default_value' => $captacion_nid,
    );

    $form['title'] = array(
      '#title' => 'Actuacion',
      '#type' => 'textfield',
      '#required' => TRUE,
    );

    $form['#attached']['library'][] = 'carbray/carbray.carbray_timer';

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

    $timer_tooltip = 'Edita el numero de minutos transcurridos.';
    $form['timer'] = array(
      '#type' => 'textfield',
      '#description' => $timer_tooltip,
      '#required' => TRUE,
      '#prefix' => '<div class="pull-right timer-textfield">',
      '#attributes' => array('class' => array('hidden')),
      '#suffix' => '</div></div></div>',
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
    $nota = $form_state->getValue('nota');

    $actuacion_captacion = Node::create(['type' => 'actuacion_captacion']);
    $actuacion_captacion->set('title', $title);
    $actuacion_captacion->set('field_actuacion_captacion', $expediente_nid);
    $actuacion_captacion->set('field_actuacion_captacion_tiempo', $timer);
    $actuacion_captacion->enforceIsNew();
    $actuacion_captacion->save();

    // Now that we have an actuacion_captacion nid we can create a Nota node that references it.
    $nota_node = Node::create(['type' => 'nota']);
    $nota_node->set('title', 'Nota para id ' . $actuacion_captacion->id() . ' creada el ' . date('d-M-Y H:m:s', time()));
    $nota_node->set('field_nota_nota', $nota);
    $nota_node->enforceIsNew();
    $nota_node->save();

    // Now that we have a Nota we can reload our actuacion and save the reference to the Nota.
    $actuacion = Node::load($actuacion_captacion->id());
    $actuacion->set('field_actuacion_captacion_nota', $nota_node->id());
    $actuacion->save();

    drupal_set_message('Actuacion ' . $title . ' ha sido creada');
  }
}
