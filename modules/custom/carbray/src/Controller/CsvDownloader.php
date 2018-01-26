<?php

namespace Drupal\carbray\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carbray\CsvResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Render\Markup;

class CsvDownloader extends ControllerBase {

  public function ActuacionesCsv(){
    // Work out data querying filters by looking at url params.
    $path = parse_url(\Drupal::request()->getRequestUri());
    $query_array = array();
    parse_str($path['query'],$query_array);

    $expediente_node = Node::load($query_array['nid']);
    $filename = $expediente_node->label() . '.csv';

    $actuaciones = get_actuaciones_csv($query_array['nid']);
    $total_seconds = 0;
    $rows = [];
    foreach ($actuaciones as $actuacion) {
      $actuacion_node = Node::load($actuacion);

      // Get the minutes values for the actuacion and transform it to seconds for correct display.
      $minutes = $actuacion_node->get('field_actuacion_tiempo_en_seg')->value;
      $seconds = $minutes * 60;
      $hours = floor($seconds / 3600);
      $minutes = floor(($seconds / 60) % 60);
      $total_seconds += $seconds;

      // Load nota for actuacion.
      $nota_text = '';
      $nota_ref = $actuacion_node->get('field_actuacion_nota')->getValue();
      if (isset($nota_ref[0]) && isset($nota_ref[0]['target_id'])) {
        $nota_node = Node::load($nota_ref[0]['target_id']);
        $nota_text = $nota_node->get('field_nota_nota')->value;
      }

      $rows[] = [
        date('d-m-Y H:i:s', $actuacion_node->created->value),
        $actuacion_node->title->value,
        $hours . ':' . $minutes,
        Markup::create($nota_text),
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
}
