<?php

namespace Drupal\import_carbray\Plugin\migrate\process;

use Drupal\migrate\Row;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;

/**
  * NOTE: this plugin is not found and errors if used,
  * it is not needed as I found format_date plugin,
  * but I am leaving it here to showcase directions for
  * next needed custom Plugin.
  *
  * @MigrateProcessPlugin(
  *   id = "transform_date_order"
  * )
  */
class DateOrderTransform extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_null($value)) {
      return $value;
    }

    $date_bits = explode("/", $value);
    $new_value = $date_bits[2] . '-' . $date_bits[1] . '-' . $date_bits[0];

    return $new_value;
  }
}