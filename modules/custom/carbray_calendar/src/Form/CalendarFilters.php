<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray_calendar\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;


/**
 * CalendarFilters form.
 */
class CalendarFilters extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendar_filters';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
//    $form['#attributes']['class'][] = 'margin-left-20';
    $filter_options = [
      0 => 'Todos los departamentos',
      1 => 'Departamento 1',
      2 => 'Departamento 2',
    ];

    $form['departamento'] = array(
      '#type' => 'select',
      '#options' => $filter_options,
      '#title' => t('Filtrar por departamento'),
    );
//    $form['submit'] = array(
//      '#type' => 'submit',
//      '#value' => t('Mostrar'),
//    );
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
//    $nid = $form_state->getValue('captacion_nid');
//    $estado = $form_state->getValue('captacion_estado');
//
//    $captacion = Node::load($nid);
//    if ($estado == 'desarchivada') {
//      $estado_tid = CAPTACION_ARCHIVADA;
//      $message = t('Captacion archivada');
//    }
//    else {
//      $estado_tid = 43;
//      $message = t('Captacion desarchivada');
//    }
//    $captacion->set('field_captacion_estado_captacion', $estado_tid);
//    $captacion->save();
//
//    drupal_set_message($message);
  }
}