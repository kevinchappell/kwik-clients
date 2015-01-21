<?php

class K_CLIENTS_META extends KwikClients{

  public function __construct(){


    // Taxonomy Meta Fields
    add_action('client_levels_add_form_fields', array( $this, 'client_levels_add_new_meta_field'), 10, 2);
    add_action('edited_client_levels', array( $this, 'save_client_levels_custom_meta'), 10, 2);
    add_action('create_client_levels', array( $this, 'save_client_levels_custom_meta'), 10, 2);
    add_action('client_levels_edit_form_fields', array( $this, 'client_levels_edit_meta_field'), 10, 2);
  }

  // Add term page
  public function client_levels_add_new_meta_field() {
    // this will add the custom meta field to the add new term page
    ?>
    <div class="form-field">
      <label for="term_meta[fee]"><?php _e('Annual Fee', 'kwik');?></label>
      <input type="text" name="term_meta[fee][]" id="term_meta[fee]" value="">
      <input type="text" name="term_meta[fee][]" id="term_meta[fee][1]" value="">
      <p class="description"><?php _e('What is the Annual fee for this Membership Level?', 'kwik');?></p>
    </div>
    <div class="form-field">
      <label for="term_meta[fte]"><?php _e('FTEs', 'kwik');?></label>
      <input type="text" name="term_meta[fte]" id="term_meta[fte]" value="">
      <p class="description"><?php _e('How many FTEs?', 'kwik');?></p>
    </div>
    <div class="form-field">
      <label for="term_meta[ipc]"><?php _e('IP Contribution', 'kwik');?></label>
      <input type="text" name="term_meta[ipc]" id="term_meta[ipc]" value="">
      <p class="description"><?php _e('How much?', 'kwik');?></p>
    </div>
    <div class="form-field">
      <label for="term_meta[tsc]"><?php _e('Technical Steering Commitee', 'kwik');?></label>
      <input type="text" name="term_meta[tsc]" id="term_meta[tsc]" value="">
      <p class="description"><?php _e('', 'kwik');?></p>
    </div>
    <div class="form-field">
      <label for="term_meta[position]"><?php _e('Board/Voting Position', 'kwik');?></label>
      <input type="text" name="term_meta[position]" id="term_meta[position]" value="">
      <p class="description"><?php _e('', 'kwik');?></p>
    </div>
  <?php
  }

  // Edit term page
  public function client_levels_edit_meta_field($term) {

    // put the term ID into a variable
    $t_id = $term->term_id;

    // retrieve the existing value(s) for this meta field. This returns an array
    $term_meta = get_option("taxonomy_$t_id");?>
    <tr class="form-field">
    <th scope="row" valign="top"><label for="term_meta[fee]"><?php _e('Annual Fee', 'kwik');?></label></th>
      <td>
        <input type="text" name="term_meta[fee][]" id="term_meta[fee]" value="<?php echo esc_attr($term_meta['fee'][0]) ? esc_attr($term_meta['fee'][0]) : '';?>">
        <input type="text" name="term_meta[fee][]" id="term_meta[fee][1]" value="<?php echo esc_attr($term_meta['fee'][1]) ? esc_attr($term_meta['fee'][1]) : '';?>">
        <p class="description"><?php _e('What is the Annual fee for this Membership Level?', 'kwik');?></p>
      </td>
    </tr>
    <tr class="form-field">
    <th scope="row" valign="top"><label for="term_meta[fte]"><?php _e('FTEs', 'kwik');?></label></th>
      <td>
        <input type="text" name="term_meta[fte]" id="term_meta[fte]" value="<?php echo esc_attr($term_meta['fte']) ? esc_attr($term_meta['fte']) : '';?>">
        <p class="description"><?php _e('Enter the number of FTEs', 'kwik');?></p>
      </td>
    </tr>
    <tr class="form-field">
    <th scope="row" valign="top"><label for="term_meta[ipc]"><?php _e('IP Contribution', 'kwik');?></label></th>
      <td>
        <input type="text" name="term_meta[ipc]" id="term_meta[ipc]" value="<?php echo esc_attr($term_meta['ipc']) ? esc_attr($term_meta['ipc']) : '';?>">
        <p class="description"><?php _e('How much for IPC?', 'kwik');?></p>
      </td>
    </tr>
    <tr class="form-field">
    <th scope="row" valign="top"><label for="term_meta[tsc]"><?php _e('Technical Steering Commitee', 'kwik');?></label></th>
      <td>
        <input type="text" name="term_meta[tsc]" id="term_meta[tsc]" value="<?php echo esc_attr($term_meta['tsc']) ? esc_attr($term_meta['tsc']) : '';?>">
        <p class="description"><?php _e('', 'kwik');?></p>
      </td>
    </tr>
    <tr class="form-field">
    <th scope="row" valign="top"><label for="term_meta[position]"><?php _e('Board/Voting Position', 'kwik');?></label></th>
      <td>
        <input type="text" name="term_meta[position]" id="term_meta[position]" value="<?php echo esc_attr($term_meta['position']) ? esc_attr($term_meta['position']) : '';?>">
        <p class="description"><?php _e('', 'kwik');?></p>
      </td>
    </tr>
  <?php
  }

  // Save extra taxonomy fields callback function.
  public function save_client_levels_custom_meta($t_id) {
    if (isset($_POST['term_meta'], $t_id)) {
      $term_meta = get_option("taxonomy_$t_id");
      $keys = array_keys($_POST['term_meta']);
      foreach ($keys as $key) {
        if (isset($_POST['term_meta'][$key])) {
          $term_meta[$key] = $_POST['term_meta'][$key];
        }
      }
      // Save the option array.
      update_option("taxonomy_$t_id", $term_meta);
    }
  }



}
