<?php

/**
 * @file
 * Contains \Drupal\views_bulk_operations\Plugin\views\field\NodeBulkForm.
 */

namespace Drupal\views_bulk_operations\Plugin\views\field;

use Drupal\views_bulk_operations\Plugin\views\field\BulkForm;

/**
 * Defines a node operations bulk form element.
 *
 * @ViewsField("vbo_node_bulk_form")
 */
class NodeBulkForm extends BulkForm {

  /**
   * {@inheritdoc}
   */
  protected function emptySelectedMessage() {
    return $this->t('No content selected.');
  }
}
