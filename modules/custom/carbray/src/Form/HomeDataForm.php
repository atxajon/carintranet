<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Url;



/**
 * HomeDataForm form.
 */
class HomeDataForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'home_data_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uid = 0) {
    // Obtain query string values to set default_value in filters.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = [];
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }

//    $form['dates'] = [
//      '#type' => 'container',
//      '#attributes' => [
//        'class' => ['class-here'],
//      ],
//      '#prefix' => '<div class="row margin-top-20">',
//    ];
//
//    $form['dates']['date_from'] = array(
//      '#type' => 'datelist',
//      '#date_format' => 'd-m-Y',
//      '#date_flexible' => 0,
//      '#date_increment' => 1,
//      '#date_year_range' => '-3:+0',
//      '#date_part_order' => array('day', 'month', 'year'),
//      '#date_time_element' => 'none',
//      '#title' => t('Desde'),
//      '#default_value' => isset($query_array['date_from']) ? DrupalDateTime::createFromTimestamp($query_array['date_from']) : '',
////      '#prefix' => '<div class="clearfix">',
////      '#suffix' => '</div>',
//    );
//    $form['dates']['date_to'] = array(
//      '#type' => 'datelist',
//      '#date_format' => 'd-m-Y',
//      '#date_flexible' => 0,
//      '#date_increment' => 1,
//      '#date_year_range' => '-3:+0',
//      '#date_part_order' => array('day', 'month', 'year'),
//      '#date_time_element' => 'none',
//      '#title' => t('Hasta'),
//      '#default_value' => isset($query_array['date_to']) ? DrupalDateTime::createFromTimestamp($query_array['date_to']) : '',
////      '#prefix' => '<div class="pull-left">',
////      '#suffix' => '</div>',
//    );


    $form['calendar_from'] = array(
      '#type' => 'date',
      '#title' => 'Desde: ',
      '#date_format' => 'd/m/Y',
      '#default_value' => isset($query_array['date_from']) ? date("Y-m-d", $query_array['date_from']) : '',
      '#description' => t('Elige la fecha de comienzo'),
    );

    $form['calendar_to'] = array(
      '#type' => 'date',
      '#title' => 'Hasta: ',
      '#default_value' => isset($query_array['date_to']) ? date("Y-m-d", $query_array['date_to']) : '',
      '#format' => 'm/d/Y',
      '#description' => t('Elige la fecha final'),
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Filtrar por fechas'),
      '#attributes' => array('class' => ['btn-primary', 'filter']),
//      '#suffix' => '</div>',

    ];

    if ($query_array) {
      $form['reset'] = [
        '#type' => 'submit',
        '#value' => t('Mostrar todo'),
        '#submit' => array('::resetValues'),
        '#attributes' => array('class' => ['btn-warning', 'reset']),
        '#suffix' => '</div>',
      ];
    }
    else {
      $form['submit']['#suffix'] = '</div>';
    }


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo: date from must be less than date_to.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $calendar_from = $form_state->getValue('calendar_from');
    $calendar_to = $form_state->getValue('calendar_to');
    $options = [];

    if ($calendar_from) {
      $timestamp = strtotime($calendar_from);
      // Store in query string params timestamp.
      $options['date_from'] = $timestamp;
    }
    if ($calendar_to) {
      $timestamp = strtotime($calendar_to);
      // Store in query string params timestamp.
      $options['date_to'] = $timestamp;
    }


    $url = Url::fromRoute('<current>', [], ['query' => $options]);
    $form_state->setRedirectUrl($url);
  }

  public function resetValues(array &$form, FormStateInterface $form_state) {
    $url = Url::fromRoute('<current>');
    $form_state->setRedirectUrl($url);
  }
}