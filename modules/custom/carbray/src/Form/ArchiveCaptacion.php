<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;


/**
 * ArchiveCaptacion form.
 */
class ArchiveCaptacion extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'archive_captacion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $form['#attributes']['class'][] = 'pull-left';
    $form['#attributes']['class'][] = 'margin-left-20';

    $captacion = Node::load($nid);
    $estado_term = $captacion->get('field_captacion_estado_captacion')->entity;
    $button_classes = ['btn-danger', 'btn'];

    if ($estado_term->id() == CAPTACION_ARCHIVADA) {
      $button_text = t('Desarchivar captacion');
      $estado = 'archivada';
    }
    else {
      $button_text = t('Archivar captacion');
      $estado = 'desarchivada';
    }
    $form['captacion_nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );
    $form['captacion_estado'] = array(
      '#type' => 'hidden',
      '#value' => $estado,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $button_text,
      '#attributes' => array('class' => $button_classes),
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
    $nid = $form_state->getValue('captacion_nid');
    $estado = $form_state->getValue('captacion_estado');

    $captacion = Node::load($nid);
    if ($estado == 'desarchivada') {
      $estado_tid = CAPTACION_ARCHIVADA;
      $message = t('Captacion archivada');
    }
    else {
      $estado_tid = 43;
      $message = t('Captacion desarchivada');
    }
    $captacion->set('field_captacion_estado_captacion', $estado_tid);
    $captacion->save();

    drupal_set_message($message);
  }
}