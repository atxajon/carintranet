<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray_informes\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DrupalDateTime;



/**
 * InformeFechasDepartamentosFilters form.
 */
class InformeFechasDepartamentosFilters extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'informe_fechas_departamentos_filters';
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

    $form['dates'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['floated-col'],
      ],
      '#prefix' => '<div class="row margin-top-20">',
    ];

    $form['dates']['date_from'] = array(
      '#type' => 'datelist',
      '#date_format' => 'd-m-Y',
      '#date_flexible' => 0,
      '#date_increment' => 1,
      '#date_year_range' => '-3:+0',
      '#date_part_order' => array('day', 'month', 'year'),
      '#date_time_element' => 'none',
      '#title' => t('Desde'),
      '#default_value' => isset($query_array['date_from']) ? DrupalDateTime::createFromTimestamp($query_array['date_from']) : '',
    );
    $form['dates']['date_to'] = array(
      '#type' => 'datelist',
      '#date_format' => 'd-m-Y',
      '#date_flexible' => 0,
      '#date_increment' => 1,
      '#date_year_range' => '-3:+0',
      '#date_part_order' => array('day', 'month', 'year'),
      '#date_time_element' => 'none',
      '#title' => t('Hasta'),
      '#default_value' => isset($query_array['date_to']) ? DrupalDateTime::createFromTimestamp($query_array['date_to']) : '',
    );

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

    $form['departamento'] = array(
      '#type' => 'select',
      '#options' => get_departamento_options('departamento'),
      '#title' => t('Filtrar por departamento'),
      '#prefix' => '<div class="floated-col">',
      '#suffix' => '</div>',
//      '#attributes' => [
//        'class' => ['col-sm-3'],
//      ],
//      '#empty_option' => 'Todos los departamentos',
    );

    $form['last_months'] = array(
      '#type' => 'select',
      '#title' => 'Ultimos 12 meses',
      '#options' => $last_12_months,
//      '#attributes' => [
//        'class' => ['col-sm-3'],
//      ],
      '#empty_option' => ' - Elegir més - ',
      '#prefix' => '<div class="floated-col">',
      '#suffix' => '</div></div>',
//      '#suffix' => '</div>',
    );


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Buscar por departamento y fecha'),
      '#attributes' => array('class' => ['margin-top-20', 'margin-bottom-20', 'btn-primary', 'filter']),
//      '#type' => 'inline_template',
//      '#template' => '<button type="button" class="btn btn-primary margin-top-20 margin-bottom-20 filter"><span class="glyphicon glyphicon-plus-sign"></span>Buscar por departamento y fecha</button>',
    ];

//    $form['reset'] = [
//      '#type' => 'submit',
//      '#value' => t('Mostrar total'),
//      '#submit' => array('::resetValues'),
//      '#attributes' => array('class' => ['margin-top-20', 'margin-bottom-20', 'margin-left-10', 'btn-warning', 'reset']),
//    ];

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
    $departamento = $form_state->getValue('departamento');

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

    if ($departamento) {
      $options['departamento'] = $departamento;
    }

    $url = Url::fromRoute('<current>', [], ['query' => $options]);
    $form_state->setRedirectUrl($url);
  }

  public function resetValues(array &$form, FormStateInterface $form_state) {
    $url = Url::fromRoute('<current>');
    $form_state->setRedirectUrl($url);
  }
}