<?php

namespace Drupal\carbray_cliente\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;

/**
 * Class UserAutocomplete.
 */
class UserAutocomplete extends ControllerBase {

  /**
   * getClients handler for autocomplete request.
   */
  public function getClients(Request $request) {

    $results = [];

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));

      $db = \Drupal::database();
      $query = $db->select('users_field_data', 'ufd');
      $query->leftJoin('user__field_nombre', 'n', 'n.entity_id = ufd.uid');
      $query->leftJoin('user__field_apellido', 'a', 'a.entity_id = ufd.uid');
      $query->leftJoin('user__roles', 'r', 'r.entity_id = ufd.uid');
      $query->fields('ufd', ['uid', 'mail']);
      $query->fields('n', ['field_nombre_value']);
      $query->fields('a', ['field_apellido_value']);
      $query->isNull('r.entity_id');

      // Find either nombre, apellido, or email partial string match.
      $group = $query->orConditionGroup()
        ->condition('mail', '%' . $query->escapeLike($typed_string) . '%', 'LIKE')
        ->condition('field_nombre_value', '%' . $query->escapeLike($typed_string) . '%', 'LIKE')
        ->condition('field_apellido_value', '%' . $query->escapeLike($typed_string) . '%', 'LIKE');

      $clients = $query->condition($group)->execute();

      foreach ($clients as $client) {
        $full_name = $client->field_nombre_value . ' ' . $client->field_apellido_value;
        $results[] = [
          'value' => $full_name . ' [' . $client->uid . ']',
          'label' => $full_name . ' - ' . $client->mail . '(' . $client->uid . ')',
        ];
      }
    }
    return new JsonResponse($results);
  }

}
