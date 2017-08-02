<?php

namespace Drupal\carbray;

class ProgressBar {

  private $progress_entity;
  public $cifra;

  public function __construct($type_of_entity) {
    $this->progress_entity = $type_of_entity;
    $this->now = date('Y-m-d H:i:s');
    $this->db = \Drupal::database();
  }

  /**
   * {@inheritdoc}
   */
  public function getDepartamentoObjetivo($id) {
    $sql = "SELECT field_objetivo_cifra_value as cifra, fe.field_objetivo_fecha_inicio_value as fecha_inicio, ff.field_objetivo_fecha_final_value as fecha_final
            FROM node__field_objetivo_cifra c
            INNER JOIN node__field_objetivo_departamento d on c.entity_id = d.entity_id
            INNER JOIN node__field_objetivo_fecha_inicio fe on c.entity_id = fe.entity_id
            INNER JOIN node__field_objetivo_fecha_final ff on c.entity_id = ff.entity_id
            WHERE d.field_objetivo_departamento_target_id = :tid
            AND field_objetivo_fecha_inicio_value < :now
            AND field_objetivo_fecha_final_value > :now";
    $objetivo = $this->db->query($sql, array(':tid' => $id, ':now' => $this->now))->fetchAll();
    $this->cifra = $objetivo[0]->cifra;
    $this->fecha_inicio = $objetivo[0]->fecha_inicio;
    $this->fecha_final = $objetivo[0]->fecha_final;
  }

  /**
   * {@inheritdoc}
   */
  public function getTrabajadorObjetivo($uid) {
    $sql = "SELECT field_objetivo_cifra_value as cifra,
fe.field_objetivo_fecha_inicio_value as fecha_inicio,
ff.field_objetivo_fecha_final_value as fecha_final
            FROM node__field_objetivo_cifra c
            INNER JOIN node__field_objetivo_trabajador t on c.entity_id = t.entity_id
            INNER JOIN node__field_objetivo_fecha_inicio fe on c.entity_id = fe.entity_id
            INNER JOIN node__field_objetivo_fecha_final ff on c.entity_id = ff.entity_id
            WHERE t.field_objetivo_trabajador_target_id = :uid
            AND field_objetivo_fecha_inicio_value < :now
            AND field_objetivo_fecha_final_value > :now";
    $objetivo = $this->db->query($sql, array(':uid' => $uid, ':now' => $this->now))->fetchAll();

    $this->cifra = ($objetivo) ? $objetivo[0]->cifra : '';
    $this->fecha_inicio = ($objetivo) ? $this->formatDate($objetivo[0]->fecha_inicio) : '';
    $this->fecha_final = ($objetivo) ? $this->formatDate($objetivo[0]->fecha_final) : '';
  }

  /**
   * Transforms 2017-12-31T22:59:59 into 31-12-2017.
   *
   * @param $date
   *   datetime value.
   * @param string $format
   * @return bool|string
   */
  public function formatDate($date, $format = "d-M-Y") {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
  }

  public function buildArray() {
    // @todo: need method to work out total facturado.
    $total_facturas = 3000.12;
    $total_facturas = (float)$total_facturas;
    $percent = $total_facturas / $this->cifra * 100;
    // Don't let percent exceed 100% when objetivo is achieved.
    $percent = ($percent > 100) ? 100 : $percent;

    $build = array(
      '#theme' => 'carbray_progress_bar',
      '#animate' => FALSE,
      '#large' => TRUE,
      '#percent' => $percent,
      '#objetivo_cifra' => $this->cifra,
      '#facturado' => $total_facturas,
    );
    return $build;
  }

}