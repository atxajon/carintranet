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
 * ArchiveExpediente form.
 */
class ArchiveExpediente extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'archive_expediente';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $form['#attributes']['class'][] = 'margin-left-20';

    $expediente = Node::load($nid);
    $button_classes = ['btn-danger', 'btn', 'pull-right'];

    if ($expediente->isPublished()) {
      $button_text = t('Archivar');
      $estado = 'desarchivada';
    }
    else {
      $button_text = t('Desarchivar');
      $estado = 'archivada';
    }

    $form['expediente_nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );
    $form['expediente_estado'] = array(
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
    $nid = $form_state->getValue('expediente_nid');
    $expediente_status = $form_state->getValue('expediente_estado');

    $expediente = Node::load($nid);
    if ($expediente_status == 'desarchivada') {
      $message = t('Expediente archivado');
      $expediente->setPublished(FALSE);
    }
    else {
      $message = t('Expediente activado');
      $expediente->setPublished(TRUE);
    }
    $expediente->save();

    drupal_set_message($message);
  }
}