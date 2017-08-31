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
    $clientes_uids = get_my_clients($logged_in_uid, 'captacion');

    $clientes = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($clientes_uids);

    foreach ($clientes as $cliente) {

      $estado_nombre = '';
      $term_entity = $cliente->field_user_estado_de_captacion->entity;
      if ($term_entity) {
        $term = Term::load($term_entity->id());
        $estado_nombre = $term->name->value;
      }

      $new_date_format = '';
      if ($cliente->get('field_fecha_alta')->value) {
        $timestamp = strtotime($cliente->get('field_fecha_alta')->value);
        $new_date_format = date('d-M-Y', $timestamp);
      }

      $rows[] = array(
        print_cliente_link($cliente),
        print_cliente_captadores_responsables($cliente->get('field_captador')->getValue()),
        $estado_nombre,
        $new_date_format,
        print_cliente_contacto($cliente),
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
