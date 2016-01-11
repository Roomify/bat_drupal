(function ($) {

  Drupal.behaviors.BatTypeEdit = {
    attach: function (context, settings) {
      updateElements();

      $(':input[name="operation"]', context).change(function () {
        updateElements();
      });
    }
  };

  function updateElements() {
    $('form[id^=bat-type-edit] fieldset').each(function() {
      $(this).hide();
    });

    // Get selected operation.
    var operation = ($(':input[name="operation"]').val());
    if (operation) {
      var selector = 'fieldset[id^=edit-' + operation.replace('_', '-') + ']';
      $(selector).show();
    }
  }

}(jQuery));
