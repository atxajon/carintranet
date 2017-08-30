<?php

namespace Drupal\carbray_cliente\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;

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
      // Build column 'Nombre': link to user page with nombre and apellido as text.
      $nombre = $cliente->get('field_nombre')->value;
      $apellido = $cliente->get('field_apellido')->value;
      $nombre_apellido = $nombre . ' ' . $apellido;
      $url = Url::fromRoute('entity.user.canonical', array('user' => $cliente->id()));
      $user_link = Link::fromTextAndUrl($nombre_apellido, $url);

      // Build contacto column.
      $mail = ($cliente->getEmail()) ? $cliente->getEmail() . '<br>' : '';
      $telefono = ($cliente->get('field_telefono')->value) ? $cliente->get('field_telefono')->value . '<br>' : '';
      $pais = ($cliente->get('field_pais')->value) ? $cliente->get('field_pais')->value : '';
      $contacto = $mail . $telefono . $pais;
      $contacto_markup = Markup::create($contacto);

      $captadores = $cliente->get('field_captador')->getValue();
      /**
       * A client can have multiple captadores assigned;
       * Each captador is a link to the Carbray worker.
       * To print the links in a table $row we need to:
       *  1- Create the Link obj.
       *  2- Get its html.
       *  3- Loop through extra captadores and concatenate their link html in a string.
       *  4- Create the Markup obj that later will get rendered as HTML by twig.
       **/
      $captador_link_string = '';
      foreach ($captadores as $captador_uid) {
        $captador_uid = $captador_uid['target_id'];
        $captador = get_cliente_nombre($captador_uid);
          // Step 1:
          $captador_link = Link::fromTextAndUrl($captador, Url::fromRoute('entity.user.canonical', array('user' => $captador_uid), array('attributes' => array('target' => '_blank'))));
        // Step 2: Convert captador link from Link obj to html.
        // and 3: concatenate.
        $captador_link_string .= $captador_link->toString()->getGeneratedLink() . '<br>';
      }
      // Step 4.
      $captador_markup = Markup::create($captador_link_string);

      // Same for responsables.
      $responsables = $cliente->get('field_responsable')->getValue();
      $responsable_link_string = '';
      foreach ($responsables as $responsable_uid) {
        $responsable_uid = $responsable_uid['target_id'];
        $responsable = get_cliente_nombre($responsable_uid);
        $responsable_link = Link::fromTextAndUrl($responsable, Url::fromRoute('entity.user.canonical', array('user' => $responsable_uid), array('attributes' => array('target' => '_blank'))));
        $responsable_link_string .= $responsable_link->toString()->getGeneratedLink() . '<br>';
      }
      $responsable_markup = Markup::create($responsable_link_string);

      $new_date_format = '';
      if ($cliente->get('field_fecha_alta')->value) {
        $timestamp = strtotime($cliente->get('field_fecha_alta')->value);
        $new_date_format = date('d-M-Y', $timestamp);
      }

      $rows[] = array(
        $user_link,
        $cliente->get('field_fase')->value,
        $captador_markup,
        $responsable_markup,
        $new_date_format,
        $contacto_markup,
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
