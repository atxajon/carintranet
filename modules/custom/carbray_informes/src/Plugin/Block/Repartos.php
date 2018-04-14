<?php

namespace Drupal\carbray_informes\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Informes' admin block.
 *
 * @Block(
 *  id = "repartos",
 *  admin_label = @Translation("Repartos"),
 * )
 */
class Repartos extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $procedencia_clientes = \Drupal::database()->query("SELECT field_procedencia_value as name, Count(field_procedencia_value) as amount_count, (COUNT(*) / (SELECT COUNT(*) FROM user__field_procedencia)) * 100 AS percent FROM user__field_procedencia GROUP BY field_procedencia_value")->fetchAll();

    $rows = [];
    foreach ($procedencia_clientes as $procedencia_cliente) {
      $rows[] = [
        'name' => ucfirst($procedencia_cliente->name),
        'y' => (float)$procedencia_cliente->amount_count,
        'percent' => (float)round($procedencia_cliente->percent, 2),
      ];
    }

    $markup = '<h3>Reparto</h3><div id="procedencia-chart"></div>';

    $build = array(
      '#markup' => $markup,
      '#attached' => array(
        'library' => array(
          'carbray_informes/highcharts',
          'carbray_informes/exporting',
          'carbray_informes/procedencia_piechart',
        ),
        // Pass php var content to js.
        'drupalSettings' => array(
          'procedencia_data' => $rows,
        ),
      ),
    );

    return $build;
  }

}
