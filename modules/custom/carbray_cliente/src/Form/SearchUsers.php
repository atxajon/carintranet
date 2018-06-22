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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->getValue('uid');
    if (!$uid) {
      // User has not submited the form without entering any value.
      $form_state->setErrorByName('uid', $this->t('Selecciona un cliente de la lista.'));
    }

    // User has entered a value.
    $client_uid = $this->extractUid($uid);

    if (!$client_uid) {
      // The entered value does not contain a uid. Find out whether is an email or not.
      $is_email = $this->lookupEmail($uid, $form_state);
      if (!$is_email) {
        // If not match for email throw error and rebuild.
        $form_state->setErrorByName('uid', $this->t('Selecciona un cliente de la lista.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->getValue('uid');
    $client_uid = $this->extractUid($uid);
    if (!$client_uid) {
      // The entered value is an email. Find its uid.
      $client_uid = $this->lookupEmail($uid, $form_state);
    }

    $route_name = 'entity.user.canonical';
    $form_state->setRedirect($route_name, ['user' => $client_uid]);
  }

  /**
   * Parses and extracts the {uid} from a string like {name surname [uid]}
   */
  private function extractUid($string) {
    $uid_value = [];
    preg_match_all("/\[([^\]]*)\]/", $string, $uid_value);
    $client_uid = reset($uid_value[1]);
    return $client_uid;
  }

  /**
   * Queries for a uid in the system given an email string.
   */
  private function lookupEmail($string, FormStateInterface $form_state) {
    // User has entered a value on the form textfield but has not selected an entry from autocomplete list;
    // Most lileky reason: copy pasted an email into textfield and hit enter;
    // Let's clean the string value, do a lookup on the system and return the uid for that email.
    $probably_email = trim($string);
    if (!filter_var($probably_email, FILTER_VALIDATE_EMAIL)) {
      // No match for the email in the system. Throw error.
      $form_state->setErrorByName('uid', $this->t('Selecciona un cliente de la lista.'));
    }
    $client_uid = get_cliente_uid($probably_email);
    return $client_uid;
  }
}