<div class="bat-two-month-calendar <?php print $classes; ?> clearfix">

<div class="calendar-management-form clearfix">
<?php print render($form); ?>
</div>

<div class="calendar-navigation clearfix">
  <div class="calendar-back"><?php print $backward_link; ?></div>
  <div class="calendar-current"><?php print $current_link; ?></div>
  <div class="calendar-forward"><?php print $forward_link; ?></div>
</div>

<div class="calendar-set clearfix">
  <div id='calendar' class="month1"></div>
  <div id='calendar1' class="month2"></div>
</div>

</div>
