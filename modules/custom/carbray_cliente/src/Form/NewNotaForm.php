<?php
/**
 * @file
 * Contains \Drupal\carbray_cliente\Form\NewNotaForm.
 */
namespace Drupal\carbray_cliente\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;


/**
 * NewNotaForm form.
 */
class NewNotaForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'new_nota';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = 0) {
    $form['nota'] = array(
      '#type' => 'text_format',
      '#title' => 'Nota',
      '#format' => 'basic_html',
      '#rows' => 5,
    );
    $form['id'] = array(
      '#type' => 'hidden',
      '#value' => $id,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear nota',
      '#attributes' => array('class' => array('btn-primary')),
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
    $nota = $form_state->getValue('nota');
    $id = $form_state->getValue('id');

    $nota_node = Node::create(['type' => 'nota']);
    $nota_node->set('title', 'Nota para nid ' . $id . ' creada el ' . date('d-M-Y H:m:s', time()));
    $nota_node->set('field_nota_nota', $nota);
    $nota_node->enforceIsNew();
    $nota_node->save();

    $captacion_node = Node::load($id);
    // Adding a new value to a multivalue field.
    $captacion_node->field_captacion_nota->appendItem($nota_node->id());
    $captacion_node->save();

    drupal_set_message('Nueva nota creada');
  }
}