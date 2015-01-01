<?php

class KwikClients_Admin{

  public function __construct() {

    add_action('admin_enqueue_scripts', array( $this, 'add_clients_script' ));
    add_action('manage_clients_posts_custom_column', array( $this, 'clients_columns_content' ), 10, 2);
    add_action('wp_ajax_clients_update_post_order', array( $this, 'clients_update_post_order' ));
    add_action('save_post', array( $this, 'save_clients_meta' ), 1, 2);
    add_action('admin_menu', array( $this, 'register_clients_menu' ));
    add_shortcode( 'membership_table', array( $this, 'membership_table' ) );

    // Utils/Helpers
    add_filter('gettext', array('K_CLIENTS_HELPERS', 'k_client_logo_text_filter'), 20, 3);
    add_filter('manage_clients_posts_columns' , array( 'K_CLIENTS_HELPERS', 'add_clients_columns' ));
    add_action('dashboard_glance_items' , array('K_CLIENTS_HELPERS','clients_at_a_glance'), 'clients' );

    // Cleanup on deactivation
    add_action( 'switch_theme', array( $this, '__destruct' ) );
  }

  public function add_clients_script($hook) {
    $screen = get_current_screen();

    $post_types_array = array(
      "clients",
      "clients_page_slide-order"
    );

    // Check screen hook and current post type
    if ( in_array($screen->post_type, $post_types_array) ){
      wp_enqueue_script( 'jquery-ui-autocomplete');
      wp_enqueue_script( 'jquery-ui-sortable');
      wp_enqueue_script( 'kwik-clients-admin',  K_CLIENTS_URL . '/js/kwik-clients-admin.js', array('jquery-ui-autocomplete', 'jquery-ui-sortable', 'jquery'), NULL, true );
      wp_enqueue_script( 'kwik-clients',  K_CLIENTS_URL . '/js/kwik-clients.js', array('jquery-ui-autocomplete', 'jquery-ui-sortable', 'jquery'), NULL, true );
    }
  }


    // SHOW THE FEATURED IMAGE
  public function clients_columns_content($column_name, $post_ID) {
    switch ( $column_name ) {
      case "featured_image":
        $thumb = wp_get_attachment_image_src(get_post_thumbnail_id(), 'medium' );
        $thumb = $thumb['0'];
        if ($thumb) {
          echo '<img width="75" src="' . $thumb . '" />';
        }
      break;
    }
  }


  public function clients_in_right_now() {

    $post_type = 'clients';

    if (!post_type_exists($post_type)) {
               return;
      }
    $num_posts = wp_count_posts( $post_type );
    echo '';
    $num = number_format_i18n( $num_posts->publish );
    $text = _n( 'User Submission', 'User Submissions', $num_posts->publish );
    if ( current_user_can( 'edit_posts' ) ) {
              $num = '<a href="edit.php?post_type='.$post_type.'">'.$num.'</a>';
              $text = '<a href="edit.php?post_type='.$post_type.'">'.$text.'</a>';
      }
    echo '<td class="first b b-clients">' . $num . '</td>';
    echo '<td class="t clients">' . $text . '</td>';
    if ($num_posts->pending > 0) {
      $num = number_format_i18n( $num_posts->pending );
      $text = _n( 'User Submission Pending', 'User Submissions Pending', intval($num_posts->pending) );
      if ( current_user_can( 'edit_posts' ) ) {
        $num = '<a href="edit.php?post_status=pending&post_type='.$post_type.'">'.$num.'</a>';
        $text = '<a href="edit.php?post_status=pending&post_type='.$post_type.'">'.$text.'</a>';
      }
      echo '<td class="first b b-clients">' . $num . '</td>';
      echo '<td class="t clients">' . $text . '</td>';
    }

    echo '</tr>';
  }


  // Add the meta box
  public function add_clients_metabox(){
    add_meta_box('clients_meta', 'Client Meta Data', 'clients_meta', 'clients', 'normal', 'default');
  }


  public function clients_meta(){
      global $post;

    $post_link = get_post_meta($post->ID, '_post_link', true);
    $user_info = get_post_meta($post->ID, '_user_info', false);
    $user_info = (is_array($user_info) && !empty($user_info) ? $user_info[0] : '');


    $clients_meta = '';
      // Noncename for security check on data origin
      $clients_meta .= '<input type="hidden" name="clients_meta_noncename" id="clients_meta_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    $clients_meta .= '<div class="meta_wrap">';
    $clients_meta .= '<ul>';
      //$clients_meta .= '<li><strong>'.__('Belt Link','kwik').':</strong></li>';
      $clients_meta .= '<li><label>'.__('Post Link','kwik').'</label><input type="text" name="_post_link_title" id="post_link_title"" value="'.($post_link != "" ? get_the_title($post_link) : "").'" /><input type="hidden" id="post_link_id" name="_post_link" value="'.$post_link.'" /><label>&nbsp;</label><small>Type the name of the linked content and select from list</small></li>';
    $clients_meta .= '</ul>';
    $clients_meta .= '</div>';

    $clients_meta .= '<div class="meta_wrap user_info">';
    $clients_meta .= '<h4>'.__('Customer Info','kwik').':</h4>';
    $clients_meta .= (isset($user_info[1]) ? get_avatar( $user_info[1], 200 ) : '');
    $clients_meta .= '<ul>';

      $clients_meta .= '<li><label>'.__('Name','kwik').'</label><input type="text" name="_user_info[]" value="'.(isset($user_info[0]) ? $user_info[0] : '').'" /></li>';
      $clients_meta .= '<li><label>'.(isset($user_info[1]) ? '<a href="mailto:'.$user_info[1].'?subject=Your%20submission%20has%20been%20approved!&body=Check%20out%20your%20Action%20Shot%20on%20TopRopeBelts.com%20here: '.get_permalink($post->ID).'" title="'.(isset($user_info[0]) ? 'Send email to '.$user_info[0] : '').'">'.__('Email','kwik').'</a>' : __('Email','kwik')).'</label><input type="text" name="_user_info[]" value="'.(isset($user_info[1]) ? $user_info[1] : '').'" /></li>';
      $clients_meta .= '<li><label>'.__('Phone','kwik').'</label><input type="text" name="_user_info[]" value="'.(isset($user_info[2]) ? $user_info[2] : '').'" /></li>';
      $clients_meta .= '<li><label>'.__('URL','kwik').'</label><input type="text" name="_user_info[]" value="'.(isset($user_info[3]) ? $user_info[3] : '').'" /></li>';
      $clients_meta .= '<li><label>'.__('Twitter','kwik').'</label><input type="text" name="_user_info[]" value="'.(isset($user_info[4]) ? $user_info[4] : '').'" /></li>';
      $clients_meta .= '<li><label>'.__('Publish User Info?','kwik').'</label><input type="checkbox" name="_user_info[]" '. checked( 1, $user_info[5], false ) . ' value="1" /></li>';
    $clients_meta .= '</ul>';
    $clients_meta .= '</div>';


    $clients_meta .= '<br class="clear"/>';

    echo  $clients_meta;

  }


  // Save the Metabox Data
  public function save_clients_meta($post_id, $post){


    if($post->post_status =='auto-draft') return;


    if($post->post_type!='clients') return $post->ID;
      // make sure there is no conflict with other post save function and verify the noncename
      if (!wp_verify_nonce($_POST['clients_meta_noncename'], plugin_basename(__FILE__))) {
          return $post->ID;
      }

    $_POST['_user_info'][3] = (preg_match("#https?://#", $_POST['_user_info'][3]) === 0 && !empty($_POST['_user_info'][3]) ? 'http://' . $_POST['_user_info'][3] : $_POST['_user_info'][3]);
    $_POST['_user_info'][4] = (preg_match("/\@[a-z0-9_]+/i", $_POST['_user_info'][4]) != 0 ? str_replace('@', '', $_POST['_user_info'][4]) : $_POST['_user_info'][4]);


      // Is the user allowed to edit the post or page?
      if (!current_user_can('edit_post', $post->ID)) return $post->ID;

      $clients_meta = array(
      '_post_link' => $_POST['_post_link'],
      '_user_info' => $_POST['_user_info']
      );

      // Add values of $clients_meta as custom fields
      foreach ($clients_meta as $key => $value) {
          if( $post->post_type == 'revision' ) return;
          __update_post_meta( $post->ID, $key, $value );
      }

  }




  public function register_clients_menu(){
    add_submenu_page('edit.php?post_type=clients', 'Order Clients', 'Order', 'edit_pages', 'clients-order', array($this,'clients_order_page'));
  }

  public function clients_order_page(){
  ?>

    <div class="wrap">

      <h2>Sort Clients</h2>

      <p>Simply drag the client up or down and they will be saved in the order the appear here.</p>

    <?php

    $terms = get_terms("client_levels", 'orderby=id&hide_empty=1' );

          foreach ($terms as $term) {
          $clients = new WP_Query(array(
              'post_type' => 'clients',
              'posts_per_page' => -1,
              'tax_query' => array(
                array(
                  'taxonomy' => $term->taxonomy,
                  'field' => 'id',
                  'terms' => $term->term_id, // Where term_id of Term 1 is "1".
                  'include_children' => false
                )
              ),
              'order' => 'ASC',
              'orderby' => 'menu_order'
          ));
          echo '<h1>'.$term->name.' Level</h1>';
          if ($clients->have_posts()): ?>
          <table class="wp-list-table widefat fixed posts" id="sortable-table">
            <thead>
              <tr>
                <th class="column-order">Order</th>
                <th class="column-thumbnail">Thumbnail</th>
                <th class="column-title">Title</th>
              </tr>
            </thead>
            <tbody data-post-type="clients">
            <?php
                  while ($clients->have_posts()): $clients->the_post();
                  ?>

              <tr id="post-<?php the_ID(); ?>">
                <td class="column-order"><img src="<?php echo get_stylesheet_directory_uri() . '/images/icons/move.png'; ?>" title="" alt="Move Slide" width="30" height="30" class="" /></td>
                <td class="column-thumbnail"><?php the_post_thumbnail('client_logo'); ?></td>
                <td class="column-title">
                              <strong><?php the_title();?></strong>
                              <div class="excerpt"><?php the_excerpt(); ?></div>
                          </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
            <tfoot>
              <tr>
                <th class="column-order">Order</th>
                <th class="column-thumbnail">Thumbnail</th>
                <th class="column-title">Title</th>
              </tr>
            </tfoot>
          </table>

    <?php else: ?>
      <p>No clients found, why not <a href="post-new.php?post_type=clients">add one?</a></p>
    <?php endif; ?>

    <?php wp_reset_postdata(); // Don't forget to reset again!
    }?>


    </div><!-- .wrap -->

  <?php
  }





  public function clients_update_post_order(){
    global $wpdb;
    $post_type = $_POST['postType'];
    $order     = $_POST['order'];
    /**
     *    Expect: $sorted = array(
     *                menu_order => post-XX
     *            );
     */
    foreach ($order as $menu_order => $post_id) {
        $post_id    = intval(str_ireplace('post-', '', $post_id));
        $menu_order = intval($menu_order);
        wp_update_post(array(
            'ID' => $post_id,
            'menu_order' => $menu_order
        ));
    }
    die('1');
  }



}