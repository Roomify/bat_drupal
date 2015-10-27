
(function ($) {

  Drupal.behaviors.bookingFieldsetSummaries = {
    attach: function (context) {
      $('fieldset#edit-user', context).drupalSetSummary(function (context) {
        var name = $('#edit-owner-name').val() || Drupal.settings.anonymous;
        return Drupal.t('Owned by @name', { '@name': name });
      });

      $('fieldset#edit-booking-history', context).drupalSetSummary(function (context) {
         var summary = $('#edit-created', context).val() ?
           Drupal.t('Created @date', { '@date' : $('#edit-created').val() }) :
           Drupal.t('New order');

         // Add the changed date to the summary if it's different from the created.
         if ($('#edit-created', context).val() != $('#edit-changed', context).val()) {
           summary += '<br />' + Drupal.t('Updated @date', { '@date' : $('#edit-changed').val() });
         }

         return summary;
      });
    }
  };

})(jQuery);
