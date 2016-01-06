<?php

/**
 * @file
 * Example tpl file for theming a single unit-specific theme
 *
 * Available variables:
 * - $status: The variable to theme (while only show if you tick status)
 *
 * Helper variables:
 * - $bat_unit: The unit object the variables are derived from
 */
?>

<div class="bat_unit-status">
  <?php print '<strong>Unit Sample Data:</strong> ' . $bat_unit_sample_data = ($bat_unit_sample_data) ? 'Switch On' : 'Switch Off' ?>
</div>
