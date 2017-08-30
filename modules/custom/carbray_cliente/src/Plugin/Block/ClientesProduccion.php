<?php

namespace Drupal\carbray_cliente\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;

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

    $sql = "SELECT ufd.uid AS uid, ufd.mail AS mail, n.field_nombre_value AS field_nombre_value, a.field_apellido_value AS field_apellido_value, c.field_captador_target_id AS field_captador_target_id, r.field_responsable_target_id AS field_responsable_target_id, t.field_telefono_value AS field_telefono_value, p.field_pais_value AS field_pais_value, fa.field_fecha_alta_value
FROM
users_field_data ufd
LEFT JOIN user__field_nombre n ON n.entity_id = ufd.uid
LEFT JOIN user__field_apellido a ON a.entity_id = ufd.uid
LEFT JOIN user__field_captador c ON c.entity_id = ufd.uid
LEFT JOIN user__field_responsable r ON r.entity_id = ufd.uid
LEFT JOIN user__field_fecha_alta fa ON fa.entity_id = ufd.uid
LEFT JOIN user__field_telefono t ON t.entity_id = ufd.uid
LEFT JOIN user__field_pais p ON p.entity_id = ufd.uid
LEFT JOIN user__field_fase f ON f.entity_id = ufd.uid
WHERE (f.field_fase_value = 'produccion') AND ((c.field_captador_target_id = :logged_in_uid) OR (r.field_responsable_target_id = :logged_in_uid))";

    $clientes_produccion = db_query($sql, array(':logged_in_uid' => $logged_in_uid))->fetchAll();

    $already_in_list = array();
    foreach ($clientes_produccion as $cliente_produccion) {
      if (!in_array($cliente_produccion->uid, $already_in_list)) {
        $already_in_list[] = $cliente_produccion->uid;
        // Build column 'Nombre': link to user page with nombre and apellido as text.
        $nombre = ($cliente_produccion->field_nombre_value) ? $cliente_produccion->field_nombre_value : ' ';
        $apellido = ($cliente_produccion->field_apellido_value) ? $cliente_produccion->field_apellido_value : ' ';
        $nombre_apellido = $nombre . ' ' . $apellido;
        $url = Url::fromRoute('entity.user.canonical', array('user' => $cliente_produccion->uid));
        $user_link = Link::fromTextAndUrl($nombre_apellido, $url);

        // Build contacto column.
        $mail = ($cliente_produccion->mail) ? $cliente_produccion->mail . '<br>' : '';
        $telefono = ($cliente_produccion->field_telefono_value) ? $cliente_produccion->field_telefono_value . '<br>' : '';
        $pais = ($cliente_produccion->field_pais_value) ? $cliente_produccion->field_pais_value : '';
        $contacto = $mail . $telefono . $pais;
        $contacto_markup = Markup::create($contacto);

        /**
         * A client can have multiple captadores assigned;
         * Each captador is a link to the Carbray worker.
         * To print the links in a table $row we need to:
         *  1- Create the Link obj.
         *  2- Get its html.
         *  3- Loop through extra captadores and concatenate their link html in a string.
         *  4- Create the Markup obj that later will get rendered as HTML by twig.
         **/
        if ($cliente_produccion->field_captador_target_id) {
          $captador = get_cliente_nombre($cliente_produccion->field_captador_target_id);
          // Step 1:
          $captador_link = Link::fromTextAndUrl($captador, Url::fromRoute('entity.user.canonical', array('user' => $cliente_produccion->field_captador_target_id), array('attributes' => array('target' => '_blank'))));
          // Step 2: Convert captador link from Link obj to html.
          $captador_link_string = $captador_link->toString()->getGeneratedLink();
          // Does this client have more captadores? add them.
          $sql = "SELECT field_captador_target_id
FROM user__field_captador
WHERE entity_id = :cliente_uid AND field_captador_target_id != :captador";
          $captadores_extra = db_query($sql, array(':cliente_uid' => $cliente_produccion->uid, ':captador' => $cliente_produccion->field_captador_target_id))->fetchAll();
          if ($captadores_extra) {
            foreach ($captadores_extra as $captador_extra) {
              $captador_extra_name = get_cliente_nombre($captador_extra->field_captador_target_id);
              $captador_extra_link = Link::fromTextAndUrl($captador_extra_name, Url::fromRoute('entity.user.canonical', array('user' => $captador_extra->field_captador_target_id), array('attributes' => array('target' => '_blank'))));
              // Step 3:
              $captador_link_string .= '<br>' . $captador_extra_link->toString()->getGeneratedLink();
            }
          }
        }
        // Step 4:
        $captador_markup = Markup::create($captador_link_string);

        // Print responsable name with link with target _blank.
        if ($cliente_produccion->field_responsable_target_id) {
          $responsable = get_cliente_nombre($cliente_produccion->field_responsable_target_id);
          $responsable_link = Link::fromTextAndUrl($responsable, Url::fromRoute('entity.user.canonical', array('user' => $cliente_produccion->field_captador_target_id), array('attributes' => array('target' => '_blank'))));
          $responsable_link_string = $responsable_link->toString()->getGeneratedLink();
          // Does this client have more responsables? add them.
          $sql = "SELECT field_responsable_target_id
FROM user__field_responsable
WHERE entity_id = :cliente_uid AND field_responsable_target_id != :responsable";
          $responsables_extra = db_query($sql, array(
              ':cliente_uid' => $cliente_produccion->uid,
              ':responsable' => $cliente_produccion->field_responsable_target_id
            ))->fetchAll();
          if ($responsables_extra) {
            foreach ($responsables_extra as $responsable_extra) {
              $responsable_extra_name = get_cliente_nombre($responsable_extra->field_responsable_target_id);
              $responsable_extra_link = Link::fromTextAndUrl($responsable_extra_name, Url::fromRoute('entity.user.canonical', array('user' => $responsable_extra->field_responsable_target_id), array('attributes' => array('target' => '_blank'))));
              // Step 3:
              $responsable_link_string .= '<br>' . $responsable_extra_link->toString()
                  ->getGeneratedLink();
            }
          }
        }
        $responsable_markup = Markup::create($responsable_link_string);

        // Convert datetime.
        $new_date_format = '';
        if ($cliente_produccion->field_fecha_alta_value) {
          $timestamp = strtotime($cliente_produccion->field_fecha_alta_value);
          $new_date_format = date('d-M-Y', $timestamp);
        }

        $rows[] = array(
          $user_link,
          $captador_markup,
          $responsable_markup,
          $new_date_format,
          $contacto_markup,
        );
      }
    }

    $header = array(
      'Nombre',
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
