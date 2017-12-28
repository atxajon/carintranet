<?php

namespace Drupal\carbray_cliente\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


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
    $sql = "SELECT nid, title FROM node_field_data nfd WHERE type = 'propuesta_plantilla'";
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

    // Get the file for this propuesta plantilla nid and download it for user.
    $propuesta_plantilla_node = Node::load($propuesta_plantilla_nid);
    $uri = $propuesta_plantilla_node->field_propuesta_doc->entity->getFileUri();
    $content_disposition = 'attachment';

    $response = new BinaryFileResponse($uri, 200, [], true, $content_disposition);
    $form_state->setResponse($response);
  }
}
