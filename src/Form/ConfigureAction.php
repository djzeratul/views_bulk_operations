<?php

/**
 * @file
 * Contains \Drupal\views_bulk_operations\Form\ConfigureAction.
 */

namespace Drupal\views_bulk_operations\Form;

use Drupal\Core\Action\ActionManager;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use \Drupal\views_bulk_operations\Plugin\views\field\BulkForm;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 *
 */
class ConfigureAction extends FormBase {

  protected $container;
  protected $entityManager;
  protected $temp_store_factory;
  protected $action_manager;

  public function __construct(ContainerInterface $container, EntityManagerInterface $entity_manager, PrivateTempStoreFactory $temp_store_factory, ActionManager $action_manager) {
    $this->container = $container;
    $this->entityManager = $entity_manager;
    $this->tempStoreFactory = $temp_store_factory;
    $this->actionManager = $action_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container,
      $container->get('entity.manager'),
      $container->get('user.private_tempstore'),
      $container->get('plugin.manager.action')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return __CLASS__;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $action_id = NULL) {
    $action_definitions = $this->actionManager->getDefinitions();
    $definition = $action_definitions[$action_id];
    $action = $this->getUnconfiguredAction($definition);

    $form['#title'] = $this->t('Configure %action applied to the selection', ['%action' => $definition['label']]);

    $form += $action->buildConfigurationForm($form, $form_state);

    $form['action'] = [
      '#type' => 'hidden',
      '#value' => $action_id,
    ];

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Apply'),
      '#submit' => array(
        array($this, 'submitForm'),
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();

    $info = $this->tempStoreFactory->get($form_state->getValue('action'))->get($user->id());

    $action_id = $form_state->getValue('action');

    $action_definitions = $this->actionManager->getDefinitions();
    $definition = $action_definitions[$action_id];
    $action_plugin = $this->getUnconfiguredAction($definition);
    $action_plugin->submitConfigurationForm($form, $form_state);

    $storage = $this->entityManager->getStorage($info['entity_type']);

    $entities = [];
    foreach ($info['selected'] as $bulk_form_key) {
      $entity = BulkForm::loadEntityFromBulkFormKey($bulk_form_key, $storage);
      $entities[$bulk_form_key] = $entity;
    }

    $entities = BulkForm::filterEntitiesByActionAccess($entities, $action_plugin, $user);

    $action_plugin->executeMultiple($entities);
  }


  /**
   * Return an unconfigured instance of an action with the given id.
   */
  function getUnconfiguredAction($definition) {
    $implemented_interfaces = class_implements($definition['class']);

    if (isset($implemented_interfaces['Drupal\Core\Plugin\ContainerFactoryPluginInterface'])) {
      $action = forward_static_call_array([$definition['class'], 'create'], [$this->container, [], $definition['id'], $definition]);
    }
    else {
      $class  = $definition['class'];
      $action = new $class([], $definition['id'], $definition);
    }

    return $action;
  }
}
