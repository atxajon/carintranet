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
 * EditActuacionForm form.
 */
class EditActuacionForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_edit_actuacion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $actuacion_nid = 0) {
    // Disable caching on this form.
    $form_state->setCached(FALSE);
    $actuacion_node = Node::load($actuacion_nid);

    $form['title'] = array(
      '#title' => 'Actuacion',
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $actuacion_node->title->value,
    );

    $form['actuacion_nid'] = array(
      '#type' => 'hidden',
      '#default_value' => $actuacion_nid,
    );
    $form['timer_min'] = array(
      '#title' => 'Minutos actuacion',
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => 'Edita el numero de minutos transcurridos.',
      '#default_value' => $actuacion_node->get('field_actuacion_tiempo_en_seg')->value,
    );

    $nota_ref = $actuacion_node->get('field_actuacion_nota')->getValue();
    if ($nota_ref) {
      $nota_node = Node::load($nota_ref[0]['target_id']);
      $form['nota_nid'] = array(
        '#type' => 'hidden',
        '#default_value' => $nota_ref[0]['target_id'],
      );
    }

    $form['edit_nota'] = array(
      '#type' => 'text_format',
      '#title' => 'Notas de la actuacion',
      '#format' => 'basic_html',
      '#rows' => 5,
      '#default_value' => ($nota_ref) ? $nota_node->get('field_nota_nota')->value : '',
    );

    // Get default value for uploaded file if exists.
    $fid = ($actuacion_node->get('field_actuacion_documentacion')->getValue()) ? $actuacion_node->get('field_actuacion_documentacion')->getValue() : '';
    $default_value_file = [];
    if ($fid) {
      $default_value_file = array($fid[0]['target_id']);
    }
    $allowed_exts = array('jpg jpeg gif png txt doc xls xlsx pdf ppt pptx pps odt ods odp docx zip rar msg');
    $form['actuacion_file'] = array(
      '#type' => 'managed_file',
      '#name' => 'my_file',
      '#title' => t('Adjuntar Documentacion'),
      '#size' => 20,
      '#description' => t('Allowed Files - jpg jpeg gif png txt doc xls xlsx pdf ppt pptx pps odt ods odp docx zip rar msg'),
      '#upload_validators' => array('file_validate_extensions' => $allowed_exts),
      '#upload_location' => 'private://actuacion/',
      '#default_value' => $default_value_file,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Guardar actuacion',
      '#attributes' => array('class' => array('btn-primary', 'create-actuacion')),
    );
    $form['delete'] = array(
      '#type' => 'submit',
      '#value' => 'Eliminar actuacion',
      '#attributes' => array('class' => array('btn-danger', 'delete-actuacion')),
      '#submit' => array('::deleteSubmit'),
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
    $actuacion_nid = $form_state->getValue('actuacion_nid');
    $nota_nid = $form_state->getValue('nota_nid');
    $title = $form_state->getValue('title');
    $timer = $form_state->getValue('timer_min');
    $nota = $form_state->getValue('edit_nota');

    $actuacion_file = $form_state->getValue('actuacion_file');

    // Actuacion had a note already; open it up and edit with new value.
    if ($nota_nid) {
      $nota_node = Node::load($nota_nid);
      $nota_node->set('field_nota_nota', $nota);
    }
    else {
      // Actuacion did not have a nota. Create a new one if user populated the field.
      if ($nota) {
        $nota_node = Node::create(['type' => 'nota']);
        $nota_node->set('title', 'Nota para id ' . $actuacion_nid . ' creada el ' . date('d-M-Y H:m:s', time()));
        $nota_node->set('field_nota_nota', $nota);
        $nota_node->enforceIsNew();
      }
    }
    $nota_node->save();

    $actuacion = Node::load($actuacion_nid);
    $actuacion->set('title', $title);
    $actuacion->set('field_actuacion_tiempo_en_seg', $timer);
    $actuacion->set('field_actuacion_nota', $nota_node->id());
    $actuacion->set('field_actuacion_documentacion', $actuacion_file);
    $actuacion->save();

    drupal_set_message('Actuacion ' . $title . ' ha sido guardada');
  }

  public function deleteSubmit(array &$form, FormStateInterface $form_state) {
    $actuacion_nid = $form_state->getValue('actuacion_nid');
    $title = $form_state->getValue('title');
    $actuacion = Node::load($actuacion_nid);
    $actuacion->delete();
    drupal_set_message('Actuacion ' . $title . ' ha sido eliminada.');
  }

}
