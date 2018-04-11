<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray_calendar\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Core\Database\DatabaseException;


/**
 * AssignWorkerColour form.
 */
class AssignWorkerColour extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'assign_worker_colour';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'margin-left-20';

    $header = array(
      "",
      "Trabajador/a",
      "Color (en formato HEX)",
    );

    $form['workers_colours_table'] = array(
      '#type' => 'table',
//      '#caption' => $this->t('<h2>Asignar colores</h2>'),
      '#header' => $header,
    );

    $saved_colours = \Drupal::database()->query("SELECT * FROM carbray_calendar_colours")->fetchAllAssoc('uid');

    $internal_users = get_carbray_workers(TRUE);
    foreach ($internal_users as $uid => $email) {
      $user = User::load($uid);

      $form['workers_colours_table'][$uid]['uid'] = array(
        '#type' => 'hidden',
        '#value' => $user->id(),
      );

      $form['workers_colours_table'][$uid]['name'] = array(
        '#markup' => $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value,
      );

//      $account_url = Url::fromRoute('bm_account.account',array('account_id' => $withdrawal->account_id));
//      $username_cell = Link::fromTextAndUrl($withdrawal->uid." - ".$withdrawal->field_first_name . ' ' . $withdrawal->field_last_name, $account_url);
//      $form['withdrawals_table'][$withdrawal->id]['user'] = array(
//        '#markup' => $username_cell->toString(),
//      );


      $form['workers_colours_table'][$uid]['colour'] = array(
        '#type' => 'textfield',
        '#title' => 'Codigo HEX',
        '#default_value' => isset($saved_colours[$uid]->colour) ? $saved_colours[$uid]->colour : '',
      );

    }

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Guardar',
      '#attributes' => array(
        'class' => array(
          'button--primary',
        )
      )
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
    $values = $form_state->getValues();

    foreach ($values['workers_colours_table'] as $key => $value) {
      try {
        $updated_record = \Drupal::database()->merge('carbray_calendar_colours')
          ->key(array('uid' => $key))
          ->insertFields(array(
            'uid' => $key,
            'colour' => $value['colour'],
          ))
          ->updateFields(array(
            'uid' => $key,
            'colour' => $value['colour'],
          ))
          ->execute();

        // If query runs but no record gets updated return an error.
        if (!$updated_record) {
          $form_state->setRebuild();
          drupal_set_message('No se ha podido guardar el color: ' . $key, 'error');
          return;
        }
      } catch (DatabaseException $e) {
        $mssg = 'No fue posible actualizar tabla carbray_calendar_colours: ' . $e->getMessage();
        \Drupal::logger('carbray_calendar')->error($mssg);
        $form_state->setRebuild();
        drupal_set_message($mssg, 'error');
        return;
      }
    }
    drupal_set_message('Colores guardados.');
  }
}