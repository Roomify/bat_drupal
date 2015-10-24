(function ($) {

Drupal.behaviors.nodeFieldsetSummaries = {
  attach: function (context) {
    $('fieldset.unit-form-availability', context).drupalSetSummary(function (context) {
      var vals = [];

      if ($('.form-item-bookable input', context).is(':checked')) {
        vals.push(Drupal.t('Bookable'));
      }
      else {
        vals.push(Drupal.t('Not bookable'));
      }

      vals.push($('#edit-default-state option:selected').text());

      return vals.join(', ');
    });

    $('fieldset.unit-form-multiple', context).drupalSetSummary(function (context) {

      if ($('.form-item-multiple input', context).val() > 1) {
        return (Drupal.t('Multiple: @units units', { '@units': $('.form-item-multiple input', context).val()}));
      }
      else {
        return (Drupal.t('Not multiple'));
      }
    });

    $('fieldset.unit-form-author', context).drupalSetSummary(function (context) {
      var name = $('.form-item-author-name input', context).val() || Drupal.settings.anonymous,
        date = $('.form-item-date input', context).val();
      return date ?
        Drupal.t('By @name on @date', { '@name': name, '@date': date }) :
        Drupal.t('By @name', { '@name': name });
    });

    $('fieldset.unit-form-published', context).drupalSetSummary(function (context) {

      if ($('.form-item-status input', context).is(':checked')) {
        return (Drupal.t('Published'));
      }
      else {
        return (Drupal.t('Not published'));
      }
    });
  }
};

})(jQuery);
