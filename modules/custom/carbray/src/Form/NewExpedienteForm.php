<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewExpedienteForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;


/**
 * NewExpedienteForm form.
 */
class NewExpedienteForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_new_expediente';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'block';
    $form['num_expediente'] = array(
      '#type' => 'textfield',
      '#title' => 'Numero expediente',
      '#size' => '20',
    );

    // Get cliente uid from url query string.
//    $path = \Drupal::request()->query->get('cliente');
    $qs = \Drupal::request()->query->all();
    if ($qs) {
      $user = User::load($qs['cliente']);
    }

    $form['cliente'] = array(
      '#title' => 'Cliente',
      '#description' => t('Busca el cliente tecleando su email'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#selection_handler' => 'default',
      '#selection_settings' => [
        'include_anonymous' => TRUE,
      ],
      '#default_value' => (isset($user)) ? $user : '',
    );

    $form['factura'] = array(
      '#title' => 'Factura',
      '#description' => t('Busca la factura tecleando su titulo'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#selection_handler' => 'default',
      '#selection_settings' => array(
        'target_bundles' => array('factura'),
      ),
    );

    $internal_users = get_carbray_workers(TRUE);
    $form['responsable'] = array(
      '#title' => 'Captador',
      '#type' => 'select',
      '#empty_option' => ' - Selecciona captador - ',
      '#options' => $internal_users,
      '#multiple' => TRUE,
    );

    $tematica_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('tematicas');
    foreach ($tematica_terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    $form['tematica'] = array(
      '#title' => 'Tematica',
      '#type' => 'select',
      '#empty_option' => ' - Selecciona tematica - ',
      '#options' => $term_data,
    );


//    $form['captador'] = array(
//      '#type' => 'select',
//      '#title' => 'Captador',
//      '#empty_option' => ' - Selecciona captador - ',
//      '#options' => $internal_users,
//      '#multiple' => TRUE,
//    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear expediente',
      '#attributes' => array('class' => array('btn-success')),
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

    $num_expediente = $form_state->getValue('num_expediente');
    $cliente = $form_state->getValue('cliente');
    $factura = $form_state->getValue('factura');
    $tematica = $form_state->getValue('tematica');
    $responsable = $form_state->getValue('responsable');



    $expediente = Node::create(['type' => 'expediente']);
    $expediente->set('title', $num_expediente);
    $expediente->set('field_expediente_cliente', $cliente);
    $expediente->set('field_expediente_factura', $factura);
    $expediente->set('field_expediente_responsable', $responsable);
    $expediente->set('field_expediente_tematica', $tematica);
    $expediente->enforceIsNew();
    $expediente->save();

    $nid = $expediente->id();
    drupal_set_message('Expediente ' . $num_expediente . ' (nid: ' . $nid . ') ha sido creado');
//    $form_state['redirect'] = '<front>';
  }
}