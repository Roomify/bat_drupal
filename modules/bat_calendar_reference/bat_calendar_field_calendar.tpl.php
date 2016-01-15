<div class="<?php print $variables['classes']; ?>">

  <?php foreach ($bat_calendar_field_calendar['calendar'] as $unit_calendar): ?>
    <?php print render($unit_calendar); ?>
    <div class="cal">
    </div>
  <?php endforeach; ?>

</div>
