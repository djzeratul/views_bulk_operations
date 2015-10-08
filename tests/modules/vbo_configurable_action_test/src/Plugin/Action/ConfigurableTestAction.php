<?php

/**
 * @file
 * Contains \Drupal\vbo_configurable_action_test\Plugin\Action\ConfigurableTestAction
 */
namespace Drupal\vbo_configurable_action_test\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * @Action(
 *   id = "test_configurable_action",
 *   label = @Translation("Configurable test action"),
 *   type = "node"
 * )
 */
class ConfigurableTestAction extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!$entity) return;

    $entity->title = $this->configuration['foo'] . " - " . $entity->label();
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['foo'] = [
      '#type' => 'textfield',
      '#title' => 'Magic foo value',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['foo'] = $form_state->getValue('foo');
  }
}
