<?php
/**
 * @file
 * Contains \Drupal\carbray_cliente\Form\NewNotaForm.
 */
namespace Drupal\carbray_cliente\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * NewFacturaForm form.
 */
class NewFacturaForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'new_factura';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $captacion_nid = 0) {
    $captacion_node = Node::load($captacion_nid);
    $captacion_uid = $captacion_node->get('field_captacion_cliente')
      ->getValue();
    $cliente_data = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->load($captacion_uid[0]['target_id']);

    $form['cliente'] = array(
      '#type' => 'textfield',
      '#title' => 'Cliente',
      '#default_value' => $cliente_data->get('field_nombre')->value . ' ' . $cliente_data->get('field_apellido')->value,
      '#disabled' => TRUE,
      '#prefix' => '<div class="clearfix">',
    );
    $form['email'] = array(
      '#type' => 'textfield',
      '#title' => 'Email',
      '#default_value' => $cliente_data->getEmail(),
      '#disabled' => TRUE,
    );
    $form['telefono'] = array(
      '#type' => 'textfield',
      '#title' => 'Telefono',
      '#default_value' => $cliente_data->get('field_telefono')->value,
      '#disabled' => TRUE,
    );
    $form['nif'] = array(
      '#type' => 'textfield',
      '#title' => 'NIF',
      '#required' => TRUE,
      '#suffix' => '</div>',
    );

    $form['proforma'] = [
      '#type' => 'radios',
      '#title' => t('Factura / Proforma'),
      '#options' => array(0 => $this->t('Factura'), 1 => $this->t('Proforma')),
      '#default_value' => 1,
      '#required' => TRUE,
    ];

    $i = 0;
    $name_field = $form_state->get('num_names');
    $amount = $name_field;
    $form['#tree'] = TRUE;
    $form['names_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Servicios'),
      '#prefix' => '<div id="names-fieldset-wrapper" class="clearfix margin-top-20 margin-bottom-20">',
      '#suffix' => '</div>',
    ];
    if (empty($name_field)) {
      $name_field = $form_state->set('num_names', 1);
      $amount = 1;
    }

    for ($i = 0; $i < $amount; $i++) {
      $form['names_fieldset']['name'][$i] = [
        '#type' => 'textfield',
        '#title' => t('Servicio / Coste'),
        '#description' => t('Introduce la descripcion del servicio seguido del costo del mismo en este campo.')
      ];
    }
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['names_fieldset']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => t('Añadir servicio'),
      '#submit' => array('::addOne'),
      '#attributes' => [
        'class' => [
          'btn-sm',
        ],
      ],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'names-fieldset-wrapper',
      ],
    ];
    if ($amount > 1) {
      $form['names_fieldset']['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => t('Borrar servicio'),
        '#submit' => array('::removeCallback'),
        '#attributes' => [
          'class' => [
            'btn-sm',
            'btn-danger',
          ],
        ],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'names-fieldset-wrapper',
        ],
      ];
    }
    $form_state->setCached(FALSE);


    $form['direccion'] = array(
      '#title' => 'Direccion',
      '#type' => 'text_format',
      '#format' => 'basic_html',
      '#rows' => 3,
    );
    $form['coste_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Importe factura'),
      '#prefix' => '<div id="importe-factura-wrapper" class="margin-top-20 margin-bottom-20">',
      '#suffix' => '</div>',
    ];
    $form['coste_fieldset']['precio'] = array(
      '#type' => 'number',
      '#title' => 'Coste',
      '#default_value' => 0,
      '#min' => 0,
      '#step' => 0.01,
      '#prefix' => '<div class="clearfix margin-top-20 margin-bottom-20"',
    );
    $form['coste_fieldset']['iva'] = [
      '#type' => 'radios',
      '#title' => t('IVA 21%'),
      '#options' => array(0 => $this->t('Sin IVA'), 1 => $this->t('Con IVA')),
      '#default_value' => 1,
      '#required' => TRUE,
    ];
    $form['coste_fieldset']['importe_total'] = array(
      '#type' => 'number',
      '#title' => 'Importe total',
      '#default_value' => 0,
      '#min' => 0,
      '#step' => 0.01,
      '#suffix' => '</div>',
    );
    $form['provision_fondos'] = array(
      '#type' => 'number',
      '#title' => 'Provision fondos',
      '#default_value' => 0,
      '#min' => 0,
      '#step' => 0.01,
    );
    $form['captacion_nid'] = array(
      '#type' => 'hidden',
      '#value' => $captacion_nid,
    );
    $form['captador_uid'] = array(
      '#type' => 'hidden',
      '#value' => $uid = \Drupal::currentUser()->id(),
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Crear factura / proforma',
      '#attributes' => array('class' => array('btn-primary', 'margin-top-20')),
    );

    $form['#attached']['library'][] = 'carbray/factura_calculator';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    return $form['names_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    $add_button = $name_field + 1;
    $form_state->set('num_names', $add_button);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $name_field = $form_state->get('num_names');
    if ($name_field > 1) {
      $remove_button = $name_field - 1;
      $form_state->set('num_names', $remove_button);
    }
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nif = $form_state->getValue('nif');
    $precio = $form_state->getValue('precio');
    $proforma = $form_state->getValue('proforma');
    $iva = $form_state->getValue(array('coste_fieldset', 'iva'));
    $servicios = $form_state->getValue(array('names_fieldset', 'name'));
    $total = $form_state->getValue(array('coste_fieldset', 'importe_total'));
    $direccion = $form_state->getValue('direccion');
    $provision_fondos = $form_state->getValue('provision_fondos');
    $captacion_nid = $form_state->getValue('captacion_nid');
    $captador_uid = $form_state->getValue('captador_uid');
    $captador_user = User::load($captador_uid);

    $factura_node = Node::create(['type' => 'factura']);
    $factura_node->set('title', 'Factura con NIF ' . $nif . ' para captacion id ' . $captacion_nid);
    $factura_node->set('field_factura_nif', $nif);
    foreach ($servicios as $servicio) {
      $factura_node->field_factura_servicio->appendItem($servicio);
    }
    $factura_node->set('field_factura_proforma', $proforma);
    $factura_node->set('field_factura_iva', $iva);
    $factura_node->set('field_factura_direccion', $direccion);
    $factura_node->set('field_factura_precio', $total);
    $factura_node->set('field_factura_provision_de_fondo', $provision_fondos);
    $factura_node->set('field_factura', $captacion_nid);
    $factura_node->enforceIsNew();
    $factura_node->save();
    $params = [
      'nif' => $nif,
      'captador' => $captador_user->get('field_nombre')->value . ' ' . $captador_user->get('field_apellido')->value,
    ];

    // Send email to notify users with role secretaria.
    $secretarias = get_carbray_workers(TRUE, 'secretaria');
    foreach ($secretarias as $secretaria) {
      $to = $secretaria;
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'carbray';
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $sent = $mailManager->mail($module, 'notify_secretaria_new_factura', $to, $langcode, $params);
      $mssg = ($sent) ? 'Email sent to users of role secretaria as a new factura has been created' : '';
      \Drupal::logger('carbray')->warning($mssg);
    }

    drupal_set_message('Factura creada');
  }
}