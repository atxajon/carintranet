<?php

namespace Drupal\carbray_cliente\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class PropuestaToDoc.
 */
class PropuestaToDoc extends ControllerBase {

  /**
   * Getpropuestanode.
   */
  public function getPropuestaNode($nid) {

    $entity_type = 'node';
    $view_mode = 'propuesta_doc';

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $node = $storage->load($nid);
    $build = $view_builder->view($node, $view_mode);
    $output = render($build);
    $filename = 'propuesta-' . $nid . '.docx';
//    $c_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
//    $c_type = 'application/vnd.msword';
    $c_type = 'application/msword';
    $render_array = [
      '#markup' => $output,
      '#attached' => array(
        'http_header' => array(
          array('Content-Type',  $c_type),
          array('content-disposition',  "attachment;filename=$filename"),
        )
      )
    ];

//    $render_array['#attached']['http_header'] = [
//      ['Content-Type', 'application/vnd.msword'],
//      ['content-disposition', 'attachment;filename=' . $filename],
//    ];

    return $render_array;
  }

}
