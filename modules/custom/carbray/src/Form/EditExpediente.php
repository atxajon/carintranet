<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\DatabaseException;


/**
 * EditExpediente form.
 */
class EditExpediente extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_expediente';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $form['#attributes']['class'][] = 'form-in-modal';
    $form['expediente_nid'] = array(
      '#type' => 'hidden',
      '#value' => $nid,
    );

    $internal_users = get_carbray_workers(TRUE);
    $internal_users_options = [];
    foreach ($internal_users as $uid => $email) {
      $user = User::load($uid);
      $internal_users_options[$uid] = $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value;
    }
    $current_responsable = get_expediente_responsable($nid);
    $form['responsable'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Responsable(s)',
      '#options' => $internal_users_options,
      '#default_value' => $current_responsable,
      '#multiple' => TRUE,
      '#required' => TRUE,
    );

    // Is this an expediente for cliente_cuota? Make the modelos assigned to it editable.
    $chosen_modelos = \Drupal::database()->query("SELECT modelos_tid from carbray_expediente_modelos WHERE expediente_nid = $nid")->fetchCol();
    if ($chosen_modelos) {
      $modelos_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('modelos');
      foreach ($modelos_terms as $term) {
        $modelo_options[$term->tid] = $term->name;
      }
      $form['expediente_modelos'] = array(
        '#type' => 'checkboxes',
        '#title' => 'Modelos asignados',
        '#options' => $modelo_options,
        '#default_value' => $chosen_modelos,
        '#multiple' => TRUE,
      );
    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Guardar expediente',
      '#attributes' => array('class' => array('btn-primary', 'btn')),
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

    $responsable = $form_state->getValue('responsable');
    // $captador strangely adds uid 0 for every non selected captador checkbox;
    // let's clean those up.
    $selected_responsable = array();
    foreach ($responsable as $responsable_id => $value) {
      if ($value == 0) {
        continue;
      }
      $selected_responsable[$responsable_id] = $value;
    }

    $nid = $form_state->getValue('expediente_nid');
    $modelos = $form_state->getValue('expediente_modelos');


    $expediente = Node::load($nid);
    $expediente->set('field_expediente_responsable', $selected_responsable);
    $expediente->save();

    if ($modelos) {
      // $modelos strangely adds uid 0 for every non selected modelos checkbox;
      // let's clean those up.
      $selected_modelos = [];
      $selected_modelo_tids = [];
      foreach ($modelos as $modelos_id => $value) {
        if ($value == 0) {
          continue;
        }
        $selected_modelos[$modelos_id] = $value;
        $selected_modelo_tids[] = $modelos_id;
      }

      $expediente_modelos_tids = get_expediente_modelos_tids($nid);

      try {
        foreach ($selected_modelos as $modelo_tid => $value) {
          // Is it not in the table? insert it.
          if (!in_array($modelo_tid, $expediente_modelos_tids)) {
            \Drupal::database()->insert('carbray_expediente_modelos')
              ->fields([
                'expediente_nid',
                'modelos_tid',
                'completed',
              ])
              ->values(array(
                $expediente->id(),
                $modelo_tid,
                0,
              ))
              ->execute();
          }
        }
        foreach ($expediente_modelos_tids as $expediente_modelos_tid) {
          // Originally created modelo is not selected any more? delete it.
          if (!in_array($expediente_modelos_tid, $selected_modelo_tids)) {
            $query = \Drupal::database()->delete('carbray_expediente_modelos');
            $query->condition('expediente_nid', $nid);
            $query->condition('modelos_tid', $expediente_modelos_tid);
            $query->execute();
          }
        }
      } catch
      (DatabaseException $e) {
        watchdog_exception('carbray_expediente_modelos', $e);
      }
    }

    drupal_set_message('Expediente guardado.');
  }
}