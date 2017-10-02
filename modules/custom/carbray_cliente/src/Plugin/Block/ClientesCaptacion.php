<?php

namespace Drupal\carbray_cliente\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;


/**
 * Provides a 'ClientesCaptacion' block.
 *
 * @Block(
 *  id = "clientes_captacion",
 *  admin_label = @Translation("Clientes captacion"),
 * )
 */
class ClientesCaptacion extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $logged_in_uid = \Drupal::currentUser()->id();
    $clientes = get_my_clients($logged_in_uid);

    foreach ($clientes as $cliente) {
      $cliente_data = \Drupal::entityTypeManager()->getStorage('user')->load($cliente->uid);

      $captacion_data = \Drupal::entityTypeManager()->getStorage('node')->load($cliente->captacion_nid);


      $estado_nombre = '';
      $term_entity = $captacion_data->field_captacion_estado_captacion->entity;
      if ($term_entity) {
        $term = Term::load($term_entity->id());
        $estado_nombre = $term->name->value;
      }

      $new_date_format = '';
      if ($cliente_data->get('field_fecha_alta')->value) {
        $timestamp = strtotime($cliente_data->get('field_fecha_alta')->value);
        $new_date_format = date('d-M-Y', $timestamp);
      }

      $rows[] = array(
        // @todo: this needs to link to the ficha captacion node page.
        print_cliente_link($cliente_data),
        print_cliente_captadores_responsables($captacion_data->get('field_captacion_captador')->getValue()),
        $estado_nombre,
        $new_date_format,
        print_cliente_contacto($cliente_data),
      );
    }

    $header = array(
      'Nombre',
      'Captador',
      'Estado captacion',
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
