<?php

namespace Drupal\carbray\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;


class CsvDownloader extends ControllerBase {

  public function ActuacionesCsv() {
    // Work out data querying filters by looking at url params.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = array();
    parse_str($path['query'],$query_array);

    $node = Node::load($query_array['nid']);
    $filename = $node->label() . '.csv';

    $node_type = $node->bundle();

    $actuaciones = get_actuaciones_for_node_csv($query_array['nid'], $node_type);
    $total_seconds = 0;
    $rows = [];
    foreach ($actuaciones as $actuacion) {
      $actuacion_node = Node::load($actuacion);
      $tiempo_field = ($node_type == 'expediente') ? 'field_actuacion_tiempo_en_seg' : 'field_actuacion_captacion_tiempo';

      // Get the minutes values for the actuacion and transform it to seconds for correct display.
      $minutes = $actuacion_node->get($tiempo_field)->value;
      $seconds = $minutes * 60;
      $hours = floor($seconds / 3600);
      $minutes = floor(($seconds / 60) % 60);
      $total_seconds += $seconds;

      // Load nota for actuacion.
      $nota_text = '';
      $nota_field = ($node_type == 'expediente') ? 'field_actuacion_nota' : 'field_actuacion_captacion_nota';
      $nota_ref = $actuacion_node->get($nota_field)->getValue();
      if (isset($nota_ref[0]) && isset($nota_ref[0]['target_id'])) {
        $nota_node = Node::load($nota_ref[0]['target_id']);
        $nota_text = $nota_node->get('field_nota_nota')->value;
      }

      $rows[] = [
        date('d-m-Y H:i:s', $actuacion_node->created->value),
        $actuacion_node->title->value,
        $hours . ':' . $minutes,
        strip_tags($nota_text),
        get_cliente_nombre($actuacion_node->getOwner()->id()),
      ];
    }
    // Add a total row at the bottom.
    $total_hours = floor($total_seconds / 3600);
    $total_minutes = floor(($total_seconds / 60) % 60);
    $rows[] = [
      'Total:',
      '',
      $total_hours . ':' . $total_minutes,
      '',
      '',
    ];

    $header[] = array(
      'Fecha creacion',
      'Actuacion',
      'Horas:Minutos',
      'Notas',
      'Autor',
    );
    $all_data = array_merge($header, $rows);

    // Instantiate obj CsvResponse to leverage data to csv conversion.
    $csvresponse = new CsvResponse($all_data);
    $csvresponse->setFilename($filename);
    return $csvresponse;
  }

  public function informePaisesCSV() {
    // Obtain query string parameters to pass them in to the queries.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = array();
    if (isset($path['query'])) {
      parse_str($path['query'], $query_array);
    }

    $countries = \Drupal::service('country_manager')->getList();
    // We need to sort translated countries ignoring their accents.
    uasort($countries,"sort_alphabetically");

    foreach ($countries as $country_code => $translatableMarkup) {
      $captaciones_activas = get_captaciones_activas_by_country_and_dept($country_code, $query_array);
      $expedientes_activos = get_expedientes_activos_by_country_and_dept($country_code, $query_array);
      $facturas_emitidas = get_facturas_emitidas_by_country_and_dept($country_code, $query_array);
      $facturas_pagadas = get_facturas_pagadas_by_country_and_dept($country_code, $query_array);

      $c_activas_dept_count = get_total_count_for_departamento($captaciones_activas, 'captacion');
      $e_activos_dept_count = get_total_count_for_departamento($expedientes_activos, 'expediente');
      $f_emitidas_dept_count = get_total_count_for_departamento($facturas_emitidas, 'factura');
      $f_pagadas_dept_count = get_total_count_for_departamento($facturas_pagadas, 'factura');

      if (!$c_activas_dept_count && !$e_activos_dept_count && !$f_emitidas_dept_count && !$f_pagadas_dept_count) {
        // Country has no content (all 0's). Do not show it on the table, skip to next iteration.
        continue;
      }

      $rows[] = array(
        $translatableMarkup,
        (isset($c_activas_dept_count[$query_array['departamento']])) ? $c_activas_dept_count[$query_array['departamento']] : 0,
        (isset($e_activos_dept_count[$query_array['departamento']])) ? $e_activos_dept_count[$query_array['departamento']] : 0,
        (isset($f_emitidas_dept_count[$query_array['departamento']])) ? $f_emitidas_dept_count[$query_array['departamento']] : 0,
        (isset($f_pagadas_dept_count[$query_array['departamento']])) ? $f_pagadas_dept_count[$query_array['departamento']] : 0,
      );
    }
    $header[] = array(
      'Pais',
      'Captaciones en curso',
      'Expedientes en curso',
      'Facturas emitidas',
      'Facturas pagadas',
    );

    $filename = 'informe-paises-';
    if (isset($query_array['departamento'])) {
      $term = Term::load($query_array['departamento']);
      $filename .= strtolower($term->name->value);
    }
    if (isset($query_array['date_from'])) {
      $filename .= '-desde:' . date('d-m-Y', $query_array['date_from']);
    }
    if (isset($query_array['date_to'])) {
      $filename .= '-hasta:' . date('d-m-Y', $query_array['date_to']);
    }

    $filename .= '.csv';

    $all_data = array_merge($header, $rows);

    // Instantiate obj CsvResponse to leverage data to csv conversion.
    $csvresponse = new CsvResponse($all_data);
    $csvresponse->setFilename($filename);
    return $csvresponse;
  }

}
