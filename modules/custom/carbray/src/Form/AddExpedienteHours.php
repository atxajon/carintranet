<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewExpedienteForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;


/**
 * AddExpedienteHours form.
 */
class AddExpedienteHours extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_add_expediente_hours';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $expediente_nid = 0) {
    // Disable caching on this form.
    $form_state->setCached(FALSE);

    $form['expediente_nid'] = array(
      '#type' => 'hidden',
      '#default_value' => $expediente_nid,
    );

    $form['horas'] = array(
      '#title' => 'Numero de horas que se añaden',
      '#type' => 'textfield',
      '#required' => TRUE,
    );

    $form['tipo_horas'] = [
      '#type' => 'radios',
      '#title' => t('Tipo de horas'),
      '#options' => array(
        0 => $this->t('De cortesía'),
        1 => $this->t('Facturables')
      ),
//      '#default_value' => 1,
      '#required' => TRUE,
    ];

    // Does this expediente have a pack de horas set? if so pass it to js timer file.
//    $expediente = Node::load($expediente_nid);
//    $pack = $expediente->get('field_expediente_pack_minutos')->value;


    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Añadir horas',
      '#attributes' => array('class' => array('btn-primary', 'add-hours')),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $expediente_nid = $form_state->getValue('expediente_nid');
    $horas_anadidas = $form_state->getValue('horas');
    $minutos_anadidos = $horas_anadidas * 60;
    $tipo_horas = $form_state->getValue('tipo_horas');


    /**
     * Insert on custom table carbray_expediente_horas, if this is an expediente with pack de horas set.
     */
    if ($minutos_anadidos > 0) {
      try {
        $record = \Drupal::database()->insert('carbray_expediente_horas')
          ->fields([
            'expediente_nid',
            'refill_minutes',
            'refill_type',
            'author',
          ])
          ->values(array(
            $expediente_nid,
            $minutos_anadidos,
            $tipo_horas,
            \Drupal::currentUser()->id(),
          ))
          ->execute();
        \Drupal::logger('update_pack_horas')
          ->notice('Expediente with pack de horas ' . $expediente_nid . ' updated, entry ' . $record . ' on table carbray_expediente_horas added.');
      } catch (DatabaseException $e) {
        watchdog_exception('update_pack_horas', $e);
        \Drupal::logger('update_pack_horas')
          ->notice('Unable to update expediente with pack de horas ' . $expediente_nid . ' with refill hours ' . $horas_anadidas . ' on carbray_expediente_horas table!');
      }

      // Update expediente's pack horas time.
      $expediente = Node::load($expediente_nid);
      $expediente->set('field_expediente_pack_minutos', $minutos_anadidos);
      $expediente->save();
    }

    drupal_set_message('Expediente actualizado');
  }
}
