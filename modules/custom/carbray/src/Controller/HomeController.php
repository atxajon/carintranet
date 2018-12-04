<?php

namespace Drupal\carbray\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

class HomeController extends ControllerBase {

  public function WorkerHome() {
    $build['prefix'] = [
      '#markup' => '<div class="block margin-bottom-20">',
    ];
    $form = \Drupal::formBuilder()->getForm('Drupal\carbray\Form\HomeDataForm', \Drupal::currentUser()->id());
    $build['form'] = [
      '#type' => 'markup',
      '#markup' => render($form),
    ];
    $build['suffix'] = [
      '#markup' => '</div>',
    ];

    // Obtain query string date values.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = [];
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }


    $logged_in_uid = \Drupal::currentUser()->id();
    $leads_captacion = get_my_clients_count($logged_in_uid, $query_array);
    $leads_produccion = get_my_clients_count($logged_in_uid, $query_array, 'produccion');

    $build['figures'] = array(
      '#theme' => 'figures_highlight',
      '#leads_recibidos_count' => $leads_captacion,
      '#leads_captacion_count' => $leads_captacion,
      '#leads_produccion_count' => $leads_produccion,
      '#facturacion_total' => 10002,
    );

    $current_user_roles = \Drupal::currentUser()->getRoles();
    if (in_array('administrator', $current_user_roles) || in_array('carbray_administrator', $current_user_roles)) {
      $colours = get_calendar_colours();
      $colours_legend = get_calendar_colours_legend($colours);
    }

    $actuaciones = get_calendar_actuaciones($logged_in_uid);
    $current_iteration_nid = 0;
    $data = [];
    foreach ($actuaciones as $actuacion) {

      /**
       * The actuaciones sql query returns duplicated data, because a user can be in multiple departments;
       * If they do they return an actuacion row for each department they're in.
       * This could be fixed before mysql 5.7 with 'group by = nid', but now they're enforcing ONLY_FULL_GROUP_BY
       * and each column needs to be thrown into group by clause. Couldn't get it to work,
       * so an (ugly) workaround is to check if the current iteration nid is already part of the result set,
       * and if it is -> skip to next row iteration.
       */
      if ($current_iteration_nid == $actuacion->nid) {
        continue;
      }

      // Work out actuacion start and end date;
      // We only have actuacion form submission time (node created value in timestamp)
      // and minutes passed declared by the user.
      // Start and end date can be misleading as often times a worker submits 2 actuaciones for say 30 mins one after the other.
      // They plot on the calendar as overlapping time...
      $actuacion_minutes = $actuacion->minutes;
      $actuacion_seconds = $actuacion_minutes * 60;
      $actuacion_created = $actuacion->created;
      $actuacion_started = $actuacion_created - $actuacion_seconds;

      if (isset($colours[$actuacion->author])) {
        $colour = (substr($colours[$actuacion->author], 0, 1) === '#') ? $colours[$actuacion->author] : '#' . $colours[$actuacion->author];
      }
      else {
        // Default gray colour for content that has an orphaned author (removed in the system) but still needs to be shown.
        $colour = '#969696';
      }

      $data[] = [
        'title' => $actuacion->title,
        'start' => date("c", $actuacion_started), // converting tiemstamp to ISO 8601 https://stackoverflow.com/questions/5322285/how-do-i-convert-datetime-to-iso-8601-in-php/5322309
        'end' => date("c", $actuacion_created),
        'created' => date('d-m-Y H:m:s', $actuacion->created),
        'url' => Url::fromRoute('entity.node.canonical', ['node' => $actuacion->expediente_nid]
        )->toString(),
        'dept_id' => $actuacion->departamento_tid,
        'dept' => $actuacion->departamento,
        'color' => $colour,
        'author' => $actuacion->nombre . ' ' . $actuacion->apellido,
        'author_uid' => $actuacion->author,
        'type' => 'Actuación',
//        'allDay' => false,
      ];
      $current_iteration_nid = $actuacion->nid;
    }

    // @todo: feed the following calendar with data from citas of category ausencias/vacaciones.
    $build['actuaciones_calendar'] = [
      '#markup' => '<div class="row"><div class="col-sm-6"><div class="block"><div id="actuaciones_calendar"></div></div></div>',
      '#attached' => array(
        'library' => array(
          'carbray_calendar/fullcalendar',
        ),
        'drupalSettings' => array(
          'data' => $data,
        ),
      ),
    ];

    $build['ausencias_calendar'] = [
      '#markup' => '<div class="col-sm-6"><div class="block"><div id="ausencias_calendar"></div></div></div></div>',
      '#attached' => array(
        'library' => array(
          'carbray_calendar/fullcalendar',
        ),
        'drupalSettings' => array(
          'data' => $data,
        ),
      ),
    ];



    return $build;
  }
}
