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
 * EditNotaForm form.
 */
class EditNotaForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_nota';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = 0) {
    $nota_node = Node::load($id);
    $form['nota'] = array(
      '#type' => 'text_format',
      '#title' => 'Nota',
      '#format' => 'basic_html',
      '#rows' => 5,
      '#default_value' => $nota_node->get('field_nota_nota')->value,
    );
    $form['id'] = array(
      '#type' => 'hidden',
      '#value' => $id,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Guardar nota',
      '#attributes' => array('class' => array('btn-primary', 'margin-top-20')),
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

    $nota_node = Node::load($id);
    $nota_node->set('field_nota_nota', $nota);
    $nota_node->save();

    drupal_set_message('Nueva nota creada');
  }
}