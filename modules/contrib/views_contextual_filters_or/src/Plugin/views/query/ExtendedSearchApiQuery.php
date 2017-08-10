<?php

/**
 * @file
 * Defines the alter query object.
 */

namespace Drupal\views_contextual_filters_or\Plugin\views\query;

use Drupal\search_api\Plugin\views\query\SearchApiQuery;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ViewExecutable;
use Drupal\Core\Url;

/**
 * Object used to create a SELECT query.
 */
class ExtendedSearchApiQuery extends SearchApiQuery {

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    $options = parent::defineOptions();

    $options['contextual_filters_or'] = array(
      'default' => FALSE,
    );

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['contextual_filters_or'] = array(
      '#title' => t('Contextual filters OR'),
      '#description' => t('Contextual filters applied to OR logic.'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['contextual_filters_or']),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(ViewExecutable $view) {
    $this->view = $view;

    if ($this->shouldAbort()) {
      return;
    }

    // Setup the nested filter structure for this query.
    if (!empty($this->where)) {
      // If the different groups are combined with the OR operator, we have to
      // add a new OR filter to the query to which the filters for the groups
      // will be added.
      if ($this->groupOperator === 'OR') {
        $base = $this->query->createConditionGroup('OR');
        $this->query->addConditionGroup($base);
      }
      else {
        $base = $this->query;
      }
      // Add a nested filter for each filter group, with its set conjunction.
      foreach ($this->where as $group_id => $group) {
        if (!empty($group['conditions']) || !empty($group['condition_groups'])) {
          $group += array('type' => 'AND');

          if ($group_id === '') {
            $conditions = $this->options['contextual_filters_or'] ? $this->query->createConditionGroup('OR') : $this->query;
          }
          else {
            $conditions = $this->query->createConditionGroup($group['type']);
          }

          if (!empty($group['conditions'])) {
            foreach ($group['conditions'] as $condition) {
              list($field, $value, $operator) = $condition;
              $conditions->addCondition($field, $value, $operator);
            }
          }
          if (!empty($group['condition_groups'])) {
            foreach ($group['condition_groups'] as $nested_conditions) {
              $conditions->addConditionGroup($nested_conditions);
            }
          }
          // If no group was given, the filters were already set on the query.
          if ($this->options['contextual_filters_or'] || $group_id !== '') {
            $base->addConditionGroup($conditions);
          }
        }
      }
    }

    // Initialize the pager and let it modify the query to add limits.
    $view->initPager();
    $view->pager->query();

    // Set the search ID, if it was not already set.
    if ($this->query->getOption('search id') == get_class($this->query)) {
      $this->query->setOption('search id', 'search_api_views:' . $view->storage->id() . ':' . $view->current_display);
    }

    // Add the "search_api_bypass_access" option to the query, if desired.
    if (!empty($this->options['bypass_access'])) {
      $this->query->setOption('search_api_bypass_access', TRUE);
    }

    // If the View and the Panel conspire to provide an overridden path then
    // pass that through as the base path.
    if (($path = $this->view->getPath()) && strpos(Url::fromRoute('<current>')->toString(), $this->view->override_path) !== 0) {
      $this->query->setOption('search_api_base_path', $path);
    }
  }
}
