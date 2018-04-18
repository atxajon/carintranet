<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */

namespace Drupal\carbray_informes\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * InformeAbogadosFilters form.
 */
class InformeAbogadosFilters extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'informe_abogados_filters';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uid = NULL) {
    // Obtain query string values to set default_value in filters.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = [];
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }

    $form['date_from'] = array(
      '#type' => 'datelist',
      '#date_format' => 'd-m-Y',
      '#date_flexible' => 0,
      '#date_increment' => 1,
      '#date_year_range' => '-3:+0',
      '#date_part_order' => array('day', 'month', 'year'),
      '#date_time_element' => 'none',
      '#title' => t('Desde'),
      '#default_value' => isset($query_array['date_from']) ? DrupalDateTime::createFromTimestamp($query_array['date_from']) : '',
//      '#prefix' => '<div class="clearfix"><div class="pull-left">',
//      '#suffix' => '</div>',
    );
    $form['date_to'] = array(
      '#type' => 'datelist',
      '#date_format' => 'd-m-Y',
      '#date_flexible' => 0,
      '#date_increment' => 1,
      '#date_year_range' => '-3:+0',
      '#date_part_order' => array('day', 'month', 'year'),
      '#date_time_element' => 'none',
      '#title' => t('Hasta'),
      '#default_value' => isset($query_array['date_to']) ? DrupalDateTime::createFromTimestamp($query_array['date_to']) : '',
//      '#prefix' => '<div class="pull-left">',
//      '#suffix' => '</div></div>',
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Filtrar por fechas'),
      '#attributes' => array('class' => ['margin-top-20', 'margin-bottom-20', 'btn-primary']),
    ];

    $form['reset'] = [
      '#type' => 'submit',
      '#value' => t('Mostrar todo'),
      '#submit' => array('::resetValues'),
      '#attributes' => array('class' => ['margin-top-20', 'margin-bottom-20', 'margin-left-10', 'btn-warning']),
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
    $values = $form_state->getValues();
    $options = array();
    foreach ($values as $key => $value) {
      if ($key == 'submit') {
        break;
      }
      if ($value != '' && $value != '-any-') {
        // Convert DrupalDatetime obj to string.
        $string = $value->format('r');
        $timestamp = strtotime($string);
        // Store in query string params timestamp.
        $options[$key] = $timestamp;
      }
    }

    $url = Url::fromRoute('<current>', [], ['query' => $options]);
    $form_state->setRedirectUrl($url);
  }

  public function resetValues(array &$form, FormStateInterface $form_state) {
    $url = Url::fromRoute('<current>');
    $form_state->setRedirectUrl($url);
  }
}