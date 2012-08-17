<?php

/**
 * @file
 * Definition of Views\comment\Plugin\views\field\Username.
 */

namespace Views\comment\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Annotation\Plugin;

/**
 * Field handler to allow linking to a user account or homepage.
 *
 * @ingroup views_field_handlers
 *
 * @Plugin(
 *   id = "comment_username",
 *   module = "comment"
 * )
 */
class Username extends FieldPluginBase {

  /**
   * Override init function to add uid and homepage fields.
   */
  function init(&$view, &$data) {
    parent::init($view, $data);
    $this->additional_fields['uid'] = 'uid';
    $this->additional_fields['homepage'] = 'homepage';
  }

  function option_definition() {
    $options = parent::option_definition();
    $options['link_to_user'] = array('default' => TRUE, 'bool' => TRUE);
    return $options;
  }

  function options_form(&$form, &$form_state) {
    $form['link_to_user'] = array(
      '#title' => t("Link this field to its user or an author's homepage"),
      '#type' => 'checkbox',
      '#default_value' => $this->options['link_to_user'],
    );
    parent::options_form($form, $form_state);
  }

  function render_link($data, $values) {
    if (!empty($this->options['link_to_user'])) {
      $account = entity_create('user', array());
      $account->uid = $this->get_value($values, 'uid');
      $account->name = $this->get_value($values);
      $account->homepage = $this->get_value($values, 'homepage');

      return theme('username', array(
        'account' => $account
      ));
    }
    else {
      return $data;
    }
  }

  function render($values) {
    $value = $this->get_value($values);
    return $this->render_link($this->sanitize_value($value), $values);
  }

}
