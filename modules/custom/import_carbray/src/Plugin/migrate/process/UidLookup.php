<?php
/**
 * @file
 * Contains \Drupal\import_cabray\Plugin\migrate\process\UidLookup.
 */

namespace Drupal\import_carbray\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Convert a field_nombre into a uid.
 *
 * Example usage with configuration:
 * @code
 *   field_expediente_cliente:
 *       plugin: uid_lookup
 *       source: cliente
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "uid_lookup",
 * )
 */
class UidLookup extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // DB lookup of uid for a given field_nombre.
    $query = \Drupal::entityQuery('user')
      ->condition('field_nombre', $value);
    $uid = $query->execute();
    return $uid;
  }

}