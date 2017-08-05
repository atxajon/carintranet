<?php
/**
 * @file
 * Contains \Drupal\carbray\Form\NewObjetivoForm.
 */
namespace Drupal\carbray\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Datetime\DrupalDateTime;


/**
 * NewObjetivoForm form.
 */
class NewObjetivoForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'carbray_new_objetivo';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'block';
    $form['fecha_inicio'] = array(
      '#type' => 'datetime',
      '#title' => 'Fecha inicio objetivo',
      '#size' => '20',
      '#default_value' => DrupalDateTime::createFromTimestamp(time()),
    );
    $form['fecha_fin'] = array(
      '#type' => 'datetime',
      '#title' => 'Fecha final objetivo',
      '#size' => '20',
      '#default_value' => DrupalDateTime::createFromTimestamp(time()),
    );
    $form['cifra'] = array(
      '#type' => 'texfield',
      '#title' => 'Cifra',
      '#size' => '20',
    );

    // Get cliente uid from url query string.
//    $path = \Drupal::request()->query->get('cliente');
    $qs = \Drupal::request()->query->all();
    if ($qs) {
      $user = User::load($qs['cliente']);
    }

    $form['trabajador'] = array(
      '#title' => 'Trabajador',
      '#description' => t('Busca el trabajador tecleando su email'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#selection_handler' => 'default',
      '#selection_settings' => [
        'include_anonymous' => FALSE,
      ],
      '#default_value' => (isset($user)) ? $user : '',
    );

    $departamento_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('departamento');
    foreach ($departamento_terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    $form['departamento'] = array(
      '#title' => 'Objetivo departamento',
      '#description' => t('Asigna el objetivo a un departamento; busca el departamento tecleando su nombre'),
      '#type' => 'select',
      '#empty_option' => ' - Selecciona departamento - ',
      '#options' => $term_data,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear objetivo',
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
    // @todo: replace all this for objetivo node.
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