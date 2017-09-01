<?php

namespace Drupal\carbray_cliente\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PropuestaPlantillaRef.
 */
class PropuestaPlantillaRef extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'propuesta_plantilla_ref';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $db = \Drupal::database();
    $sql = "SELECT nid, title FROM node_field_data nfd WHERE type = 'plantilla_propuesta'";
    $propuesta_plantillas = $db->query($sql)->fetchAllKeyed();

    $form['propuesta_ref'] = [
      '#title' => t('Elegir propuesta plantilla'),
      '#type' => 'select',
      '#options' => $propuesta_plantillas,
    ];

    // Capture current user this propuesta is going to refer to on a hidden field.
    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $cliente_uid = 0;
    foreach ($path_args as $path_arg) {
      if (is_numeric($path_arg)) {
        $cliente_uid = $path_arg;
      }
    }
    $form['cliente_uid'] = [
      '#type' => 'hidden',
      '#value' => $cliente_uid,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Crear propuesta'),
      '#attributes' => array('class' => array('btn-sm')),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $propuesta_plantilla_nid = $form_state->getValue('propuesta_ref');
    $cliente_uid = $form_state->getValue('cliente_uid');
    $route = 'carbray_cliente.new_propuesta';
    $options = array(
      'prop_plantilla' => $propuesta_plantilla_nid,
      'uid' => $cliente_uid,
    );

    $form_state->setRedirect($route, $options);

  }
}
