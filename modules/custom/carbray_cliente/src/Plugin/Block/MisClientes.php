<?php

namespace Drupal\carbray_cliente\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'MisClientes' block.
 *
 * @Block(
 *  id = "mis_clientes",
 *  admin_label = @Translation("Mis Clientes"),
 * )
 */
class MisClientes extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $logged_in_uid = \Drupal::currentUser()->id();
    $clientes_uids = get_my_clients($logged_in_uid);

    $clientes = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($clientes_uids);

    foreach ($clientes as $cliente) {
      $captadores = $cliente->get('field_captador')->getValue();
      $responsables = $cliente->get('field_responsable')->getValue();

      $new_date_format = '';
      if ($cliente->get('field_fecha_alta')->value) {
        $timestamp = strtotime($cliente->get('field_fecha_alta')->value);
        $new_date_format = date('d-M-Y', $timestamp);
      }

      $rows[] = array(
        print_cliente_link($cliente),
        $cliente->get('field_fase')->value,
        print_cliente_captadores_responsables($captadores),
        print_cliente_captadores_responsables($responsables),
        $new_date_format,
        print_cliente_contacto($cliente),
      );
    }

    $header = array(
      'Nombre',
      'Fase',
      'Captador',
      'Responsable',
      'Fecha alta',
      'Contacto',
    );
    $build = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    );
    return $build;
  }
}
