/**
 * Implementation of hook_elements.
 *
 * Much of the colorpicker code was adapted from the Colorpicker module.
 * That module has no stable release yet nor any D6 branch.
 */
/*
 *  Bind the colorpicker event to the form element
 */
(function ($) {
  Drupal.behaviors.field_example_colorpicker = {
    attach: function(context) {
      $(".bat-edit-colorpicker").on("focus", function(event) {
        var edit_field = this;
        var picker = $(this).closest('div').parent().find(".bat-colorpicker");

        // Hide all color pickers except this one.
        $(".bat-colorpicker").hide();
        $(picker).show();
        $.farbtastic(picker, function(color) {
          edit_field.value = color;
        }).setColor(edit_field.value);
      });
    }
  };
})(jQuery);
