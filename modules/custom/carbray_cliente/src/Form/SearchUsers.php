<?php
/**
 * @file
 * Contains \Drupal\carbray_cliente\Form\NewNotaForm.
 */
namespace Drupal\carbray_cliente\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;



/**
 * SearchUsers form.
 */
class SearchUsers extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_users';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'margin-bottom-20';
    $form['#attached']['library'][] = 'carbray/carbray.client_search';


    $form['uid'] = [
//      '#type' => 'entity_autocomplete',
//      '#target_type' => 'user',
      '#type' => 'textfield',
      '#autocomplete_route_name' => 'carbray_cliente.clientes_autocomplete',
//      '#autocomplete_route_parameters' => array('name' => 'title'),
      '#attributes' => array(
        'placeholder' => 'Cliente',
//        'autofocus' => TRUE,
      ),
      '#required' => TRUE,
    ];
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#button_type' => 'primary',
    );
    $form['log_out'] = array(
      '#markup' => '<a class="log-out-link pull-right" href="/user/logout">Log Out</a>',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->getValue('uid');
    if (!$uid) {
      $form_state->setErrorByName('uid', $this->t('Selecciona un cliente de la lista.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->getValue('uid');
    $uid_value = array();
    preg_match_all("/\[([^\]]*)\]/", $uid, $uid_value);
    $client_uid = reset($uid_value[1]);

    $route_name = 'entity.user.canonical';
    $form_state->setRedirect($route_name, ['user' => $client_uid]);
  }
}