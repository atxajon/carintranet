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

    $form['start'] = array(
      '#type' => 'button',
      '#value' => 'Empezar',
      '#prefix' => '<div class="clearfix"><div class="pull-left crono-wrapper"><h2 id="crono" class="no-margin crono-heading pull-left">00:00:00</h2>',
      '#attributes' => array('class' => array('btn-primary', 'btn-sm', 'margin-bottom-20')),
      '#suffix' => '</div></div>',
    );

    $form['timer'] = array(
      '#title' => 'Minutos transcurridos',
      '#type' => 'textfield',
      '#description' => 'Edita el numero de minutos transcurridos.',
      '#required' => TRUE,
//      '#prefix' => '<div class="pull-right timer-textfield">',
//      '#suffix' => '</div>',
    );

    $form['#attached']['library'][] = 'carbray/carbray.carbray_timer';

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

    $form['actuacion_file'] = array(
      '#type' => 'managed_file',
      '#name' => 'my_file',
      '#title' => t('Añadir Documentacion'),
      '#size' => 20,
//      '#description' => t('PDF format only'),
//      '#upload_validators' => $validators,
      '#upload_location' => 'private://actuacion/',
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Añadir actuacion',
      '#attributes' => array('class' => array('btn-primary', 'btn-sm')),
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
    $title = $form_state->getValue('title');
    $timer = $form_state->getValue('timer');
    $nota = $form_state->getValue('nota');
    $actuacion_file = $form_state->getValue('actuacion_file');

    $actuacion = Node::create(['type' => 'actuacion']);
    $actuacion->set('title', $title);
    $actuacion->set('field_actuacion_expediente', $expediente_nid);
    $actuacion->set('field_actuacion_tiempo_en_seg', $timer);
    $actuacion->set('field_actuacion_documentacion', $actuacion_file);
    $actuacion->enforceIsNew();
    $actuacion->save();

    $nota_node = Node::create(['type' => 'nota']);
    $nota_node->set('title', 'Nota para id ' . $actuacion->id() . ' creada el ' . date('d-M-Y H:m:s', time()));
    $nota_node->set('field_nota_nota', $nota);
    $nota_node->enforceIsNew();
    $nota_node->save();

    $actuacion = Node::load($actuacion->id());
    $actuacion->set('field_actuacion_nota', $nota_node->id());
    $actuacion->save();

    drupal_set_message('Actuacion ' . $title . ' ha sido creada');
  }
}
