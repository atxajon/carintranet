<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewClientForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * NavigateDepartamentosForm form.
 */
class NavigateDepartamentosForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_departamentos_dropdown';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tid = NULL) {
    $db = \Drupal::database();

    // Query for all other departmaentos but current tid's one.
    $sql = "SELECT tid FROM taxonomy_term_field_data WHERE vid= 'departamento' AND tid != :current_tid";
    $departamentos_tids = $db->query($sql, array(':current_tid' => $tid))->fetchCol();

    $departamento_terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadMultiple($departamentos_tids);

    foreach ($departamento_terms as $departamento_term) {
      $options[$departamento_term->id()] = $departamento_term->name->value;
    }

    $form['departamentos'] = array(
      '#type' => 'select',
      '#options' => $options,
      '#empty_option' => ' - Selecciona departamento - ',
      '#attributes' => array('onChange' => 'document.getElementById("carbray-departamentos-dropdown").submit();')
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Go'),
      '#attributes' => array('class' => array('hide')),
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

    $departamento_tid = $form_state->getValue('departamentos');
    $url = Url::fromRoute('entity.taxonomy_term.canonical', array('taxonomy_term' => $departamento_tid));
    $form_state->setRedirectUrl($url);
  }
}