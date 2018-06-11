<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray_facturacion\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DrupalDateTime;



/**
 * FacturacionFechasFilters form.
 */
class FacturacionFechasFilters extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facturacion_fechas_filters.php';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'carbray_informes/date_autosubmit';
    $form['#attributes']['class'][] = 'informe-filters';

    // Obtain query string values to set default_value in filters.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = [];
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }

    // Get last 12 months relative to current one in spanish.
    setlocale(LC_ALL,"es_ES");
    $last_12_months = [];
    $meses = array("", "Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
    for ($i = 1; $i <= 12; $i++) {
      $month_year = date('n Y', strtotime(date('Y-m-01') . " -$i months"));
      $date_parts = explode(' ', $month_year);
      $mes = $meses[date($date_parts[0])];
      $last_12_months[$month_year] = $mes . ' ' . $date_parts[1];
    }

    $form['last_months'] = array(
      '#type' => 'select',
      '#title' => 'Elige mes',
      '#options' => $last_12_months,
      '#empty_option' => ' Mes actual ',
    );

    if (isset($query_array['date_from'])) {
      $default_option = date('n Y', $query_array['date_from']);
      $form['last_months']['#default_value'] = $default_option;
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Filtrar por fechas'),
      '#attributes' => array('class' => ['margin-bottom-20', 'btn-primary', 'filter', 'hidden']),
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
    $date_from = $form_state->getValue('date_from');
    $date_to = $form_state->getValue('date_to');
    $last_months = $form_state->getValue('last_months');

    $options = array();

    if ($date_from) {
      $string = $date_from->format('r');
      $timestamp = strtotime($string);
      // Store in query string params timestamp.
      $options['date_from'] = $timestamp;
    }
    if ($date_to) {
      $string = $date_to->format('r');
      $timestamp = strtotime($string);
      // Store in query string params timestamp.
      $options['date_to'] = $timestamp;
    }

    if ($last_months) {
      $date_parts = explode(' ', $last_months);
      $first_month_day = '01-' . $date_parts[0] . '-' . $date_parts[1];
      $date_from = strtotime($first_month_day);
      $last_month_day = date('t-' . $date_parts[0] . '-' . $date_parts[1], strtotime($first_month_day));
      $date_to = strtotime($last_month_day);
      $options['date_from'] = $date_from;
      $options['date_to'] = $date_to;
    }

    $url = Url::fromRoute('<current>', [], ['query' => $options]);
    $form_state->setRedirectUrl($url);
  }

  public function resetValues(array &$form, FormStateInterface $form_state) {
    $url = Url::fromRoute('<current>');
    $form_state->setRedirectUrl($url);
  }
}