<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * ResumenAbogadosFilters form.
 */
class ResumenAbogadosFilters extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'toggle_user_status';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uid = NULL) {

    $form['date_from'] = array(
      '#type' => 'date',
      '#title'  => t('Fecha inicio'),
    );
    $form['date_to'] = array(
      '#type' => 'date',
      '#title'  => t('Fecha final'),
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value'  => t('Filtrar por fechas'),
    ];

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

    $status = $form_state->getValue('status');

  }
}