(function ($, Drupal) {

  "use strict";

  /**
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.views_bulk_operations = {
    attach: function (context, settings) {
      $('#edit-this-page').closest('.view-content').once('select-all').each(Drupal.selectAll);
    }
  };

  /**
   * Callback used in {@link Drupal.behaviors.views_bulk_operations}.
   */
  Drupal.selectAll = function () {
    // Keep track of the table, which checkbox is checked and alias the
    // settings.
    var checkboxes = $(this).find('.views-row input[type="checkbox"]');
    var $selectAll = $('#edit-this-page');
    var $selectAllLabel = $('label[for=edit-this-page]');
    var strings = {
      selectAll: Drupal.t('Select all items on this page'),
      selectNone: Drupal.t('Deselect all items on this page')
    };
    var updateSelectAll = function (state) {
      $selectAll.prop('checked', state);
      $selectAllLabel.html(state ? strings.selectNone : strings.selectAll);
    };

    // Do not add a "Select all" checkbox if there are no rows with checkboxes.
    if (checkboxes.length === 0) {
      return;
    }

    $selectAll.on('click', function (event) {

      if ($(event.target).is('input[type="checkbox"]')) {
        updateSelectAll(event.target.checked);

        // Loop through all checkboxes and set their state to the select all
        // checkbox' state.
        checkboxes.filter(':enabled').each(function () {
          $(this).prop('checked', event.target.checked);
        });
      }
    });

    // For each of the checkboxes that are not disabled.
    checkboxes.filter(':enabled').on('click', function (e) {

      /**
       * @this {HTMLElement}
       */
      $(this).closest('.views-row').toggleClass('selected', this.checked);

      // If all checkboxes are checked, make sure the select-all one is checked
      // too, otherwise keep unchecked.
      updateSelectAll((checkboxes.length === checkboxes.filter(':checked').length));
    });

    // If all checkboxes are checked on page load, make sure the select-all one
    // is checked too, otherwise keep unchecked.
    updateSelectAll((checkboxes.length === checkboxes.filter(':checked').length));
  };
})(jQuery, Drupal);
