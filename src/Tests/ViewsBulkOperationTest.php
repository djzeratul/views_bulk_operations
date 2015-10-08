<?php

/**
 * @file
 * Contains \Drupal\views_bulk_operations\Tests\ViewsBulkOperationTest.
 */

namespace Drupal\views_bulk_operations\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Component\Utility\Crypt;
use Drupal\views\Views;

/**
 * @group views_bulk_operations
 */
class ViewsBulkOperationTest extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('node', 'views', 'views_ui', 'action', 'views_bulk_operations', 'vbo_configurable_action_test');

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $node_storage;

  protected function setUp() {
    parent::setUp();

    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(array(
        'type' => 'page',
        'name' => 'Basic page',
        'display_submitted' => FALSE,
      ));
      $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
    }

    $permissions = [
      'administer actions',
      'administer views',
      'create page content',
      'access content',
      'edit any page content',
      'administer nodes',
      'administer content types',
    ];
    $this->adminUser = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->adminUser);

    $title_key = 'title[0][value]';
    $body_key = 'body[0][value]';
    // Create node to edit.
    $edit = array();
    $edit[$title_key] = $this->randomMachineName(8);
    $edit[$body_key] = $this->randomMachineName(16);
    $this->drupalPostForm('node/add/page', $edit, t('Save and publish'));

    $this->node_storage = $this->container->get('entity.manager')->getStorage('node');
  }

  public function testBulkOperationOnNonConfigurableAction() {
    $this->drupalGet('vbo-test');

    $this->node_storage->resetCache(array(1));
    $node = $this->node_storage->load(1);

    $this->assertTrue(NODE_NOT_STICKY == $node->sticky->value);

    $this->assertOption('edit-action', 'node_make_sticky_action');

    $edit["vbo_node_bulk_form[0]"] = 'en-1';
    $edit["action"] = 'node_make_sticky_action';

    $this->drupalPostForm(NULL, $edit, t('Apply'));

    $this->node_storage->resetCache(array(1));
    $node = $this->node_storage->load(1);

    $this->assertTrue(NODE_STICKY == $node->sticky->value);

    $this->assertUrl('vbo-test', [], "We are redirected to the views page.");
  }

  public function testBulkOperationOnConfigurableAction() {

    $this->drupalGet('vbo-test');

    $this->assertOption('edit-action', '#test_configurable_action');

    $edit["vbo_node_bulk_form[0]"] = 'en-1';
    $edit["action"] = '#test_configurable_action';

    $this->drupalPostForm(NULL, $edit, t('Apply'));
    $this->assertFieldByName('foo');

    $title_prefix = $this->randomMachineName(8);
    $this->drupalPostForm(NULL, ['foo' => $title_prefix], t('Apply'));

    $this->node_storage->resetCache(array(1));
    $node = $this->node_storage->load(1);
    $title = $node->label();

    $this->assertTrue(preg_match("/^$title_prefix - /", $title), 'The node was prefixed with the prefix');

    $this->assertUrl('vbo-test', [], "We are redirected to the views page.");
  }

  public function testSelectAll() {
    $this->drupalGet('vbo-test');

    $this->assertFieldById('edit-this-page');
  }
}
