<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */

namespace Drupal\carbray\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DrupalDateTime;

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
    // Obtain query string values to set default_value in filters.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = [];
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }

    $form['date_from'] = array(
//      '#type' => 'date',
      '#type' => 'datelist',
//      '#description' => t('DOB'),
//      '#date_timezone' => drupal_get_user_timezone(),
      '#date_format' => 'd-m-Y',
      '#date_flexible' => 0,
      '#date_increment' => 1,
      '#date_year_range' => '-3:+0',
//      '#date_date_element' => 'datetime',
      '#date_part_order' => array('day', 'month', 'year'),
      '#date_time_element' => 'none',
      '#title' => t('Fecha inicio'),
      '#default_value' => isset($query_array['date_from']) ? DrupalDateTime::createFromTimestamp($query_array['date_from']) : '',
    );
    $form['date_to'] = array(
      '#type' => 'datelist',
      '#date_format' => 'd-m-Y',
      '#date_flexible' => 0,
      '#date_increment' => 1,
      '#date_year_range' => '-3:+0',
      '#date_part_order' => array('day', 'month', 'year'),
      '#date_time_element' => 'none',
      '#title' => t('Fecha final'),
      '#default_value' => isset($query_array['date_to']) ? DrupalDateTime::createFromTimestamp($query_array['date_to']) : '',
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Filtrar por fechas'),
      '#attributes' => array('class' => ['margin-top-20']),
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
}