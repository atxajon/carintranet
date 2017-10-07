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
    $form['#attributes']['class'][] = 'margin-top';
    $db = \Drupal::database();
    $sql = "SELECT nid, title FROM node_field_data nfd WHERE type = 'plantilla_propuesta'";
    $propuesta_plantillas = $db->query($sql)->fetchAllKeyed();

    $form['propuesta_ref'] = [
      '#title' => t('Elegir propuesta plantilla'),
      '#type' => 'select',
      '#options' => $propuesta_plantillas,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Descargar propuesta'),
      '#attributes' => array('class' => array('btn-sm', 'btn-primary')),
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
    $route = 'carbray_cliente.propuesta_to_doc';
    $route_parameters = array('nid' => $propuesta_plantilla_nid);
    $form_state->setRedirect($route, $route_parameters);
  }
}
