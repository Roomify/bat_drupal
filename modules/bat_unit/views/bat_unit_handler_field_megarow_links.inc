<?php

/**
 * @file
 */

/**
 *
 */
class bat_unit_handler_field_megarow_links extends views_handler_field_megarow_links {

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    // Get the entity matching this row.
    $results = array($values);
    $entities = $this->query->get_result_entities($results);
    $entity = reset($entities[1]);
    $table_data = views_fetch_data($this->table);
    $entity_type = $table_data['table']['entity type'];
    list($entity_id) = entity_extract_ids($entity_type, $entity);

    // Create an array of links.
    $provided_links = explode("\n", $this->options['megarow']['links']);
    $provided_links = array_map('trim', $provided_links);
    $provided_links = array_filter($provided_links, 'strlen');

    $links = array();
    $tokens = $this->get_render_tokens(array());
    foreach ($provided_links as $link) {
      $link_parts = explode('|', $link);

      $path = $this->render_altered(array('text' => $this->replaceTokens($link_parts[1], $entity)), $tokens);

      if (drupal_valid_path($path)) {
        // Replace tokens if necessary in the url.
        $url = 'display_megarow/' . $entity_id . '/' . $path;

        // Do the same for the label.
        $label = $this->replaceTokens($link_parts[0], $entity);
        $label = $this->render_altered(array('text' => $label), $tokens);
        $label = decode_entities($label);

        // Add the link for rendering.
        $links[] = $this->getLink($label, $url, array('class' => array('views-megarow-open')));
      }
    }

    $nb_links = count($links);
    if ($nb_links == 0) {
      $element = array();

      // Hide this field.
      $this->options['exclude'] = TRUE;
    }
    elseif ($nb_links > 1) {
      $element = array(
        '#prefix' => $this->getElementPrefix($values),
        '#markup' => theme('links__ctools_dropbutton', array(
          'links' => $links,
          'attributes' => array(
            'class' => array('links', 'inline'),
          ),
        )),
        '#suffix' => $this->getElementSuffix($values),
      );
    }
    else {
      $element = array(
        '#type' => 'link',
        '#title' => $links[0]['title'],
        '#href' => $links[0]['href'],
        '#options' => array(
          'attributes' => $links[0]['attributes'],
        ),
      );
    }

    return $element;
  }

}
