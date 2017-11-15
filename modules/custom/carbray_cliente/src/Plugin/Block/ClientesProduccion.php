<?php

namespace Drupal\carbray_cliente\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ClientesProduccion' block.
 *
 * @Block(
 *  id = "clientes_produccion",
 *  admin_label = @Translation("Clientes produccion"),
 * )
 */
class ClientesProduccion extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $logged_in_uid = \Drupal::currentUser()->id();
    $clientes = get_my_clients($logged_in_uid, 'produccion');
    $rows = [];
    foreach ($clientes as $cliente) {
      $cliente_data = \Drupal::entityTypeManager()->getStorage('user')->load($cliente->uid);
      if (!$cliente_data) {
        continue;
      }

      $captacion_data = \Drupal::entityTypeManager()->getStorage('node')->load($cliente->captacion_nid);
      $estado_captacion = $captacion_data->get('field_captacion_estado_captacion')->entity;
      if ($estado_captacion->id() == CAPTACION_ARCHIVADA) {
        continue;
      }

      $expedientes = get_expedientes_for_captacion($cliente->captacion_nid);
      $expedientes_nids = array_values($expedientes);

      // @todo: add logic to allow for multiple captaciones for this cliente...
      $expediente_data = \Drupal::entityTypeManager()->getStorage('node')->load($expedientes_nids[0]);

      $tematicas = $expediente_data->get('field_expediente_tematica')->getValue();
      $tematica = reset($tematicas);

      $rows[] = array(
        print_cliente_link($cliente_data),
        print_cliente_captadores_responsables($captacion_data->get('field_captacion_captador')->getValue()),
        print_cliente_captadores_responsables($expediente_data->get('field_expediente_responsable')->getValue()),
        print_cliente_tematica($tematica),
        ($cliente_data->getEmail()) ? $cliente_data->getEmail() : '',
        ($cliente_data->get('field_telefono')->value) ? $cliente_data->get('field_telefono')->value : '',
        print_cliente_expedientes($expedientes_nids),
      );
    }

    $header = array(
      'Nombre',
      'Captador',
      'Responsable',
      'Tematica',
      'Email',
      'Telefono',
      'Expedientes',
    );
    $build = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('Ningun cliente en produccion.'),
    );
    return $build;
  }
}
