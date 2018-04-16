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


/**
 * CalendarFilters form.
 */
class CalendarFilters extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendar_filters';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $current_user_roles = [], $dept_tid = []) {
//    $form['#attributes']['class'][] = 'margin-left-20';
    $sql = "SELECT tid FROM taxonomy_term_field_data WHERE vid= 'departamento'";
    $departamentos_tids = \Drupal::database()->query($sql)->fetchCol();

    $departamento_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadMultiple($departamentos_tids);

    foreach ($departamento_terms as $departamento_term) {
      $options[$departamento_term->id()] = $departamento_term->name->value;
    }



    if (in_array('jefe_departamento', $current_user_roles)) {
      $dept_workers = get_departamento_workers($dept_tid);
      $dept_workers_options = [];
      foreach ($dept_workers as $dept_worker) {
        $worker = User::load($dept_worker);
        $dept_workers_options[$dept_worker] = $worker->get('field_nombre')->value . ' ' . $worker->get('field_apellido')->value;
      }
      $form['trabajador'] = array(
        '#type' => 'select',
        '#options' => $dept_workers_options,
        '#title' => t('Filtrar por trabajador'),
        '#empty_option' => 'Todos los trabajadores de mi(s) departamento(s)',
      );
    }
    elseif (in_array('administrator', $current_user_roles) || (in_array('carbray_administrator', $current_user_roles))) {
      $form['departamento'] = array(
        '#type' => 'select',
        '#options' => $options,
        '#title' => t('Filtrar por departamento'),
        '#empty_option' => 'Todos los departamentos',
      );
      $workers = get_carbray_workers(TRUE);
      $internal_users_options = [];
      foreach ($workers as $uid => $email) {
        $user = User::load($uid);
        $internal_users_options[$uid] = $user->get('field_nombre')->value . ' ' . $user->get('field_apellido')->value;
      }
      $form['trabajador'] = array(
        '#type' => 'select',
        '#options' => $internal_users_options,
        '#title' => t('Filtrar por trabajador'),
        '#empty_option' => 'Todos los trabajadores',
      );
    }
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
    // No submission: filter value updating is handled through js on the calendar_custom.js file.
  }
}