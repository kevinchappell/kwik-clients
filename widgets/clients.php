<?php
/**
 * Widget Name: Clients
 * Description: Show your client logos in widgetized areas
 * Version: 0.1
 * Author: kevinchappell
 *
 */


/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'cpt_clients_widget' );

/**
 * Register our widget.
 * 'Clients_Table' is the widget class used below.
 *
 * @since 0.1
 */
function cpt_clients_widget() {
  register_widget( 'Clients_Table' );
}

/**
 *
 * @since 0.1
 */
class Clients_Table extends WP_Widget {

  /**
   * Widget setup.
   */
  function Clients_Table() {
    /* Widget settings. */
    $widget_ops = array( 'classname' => 'cpt_clients_widget', 'description' => esc_html__('List all your clients', 'kwik') );

    /* Widget control settings. */
    $control_ops = array( 'width' => 150, 'height' => 350, 'id_base' => 'cpt-clients-widget' );

    /* Create the widget. */
    $this->WP_Widget( 'cpt-clients-widget', esc_html__('Kwik Clients', 'kwik'), $widget_ops, $control_ops );
  }

  /**
   * How to display the widget on the screen.
   */
  function widget( $args, $instance ) {

    extract( $args );

    /* Our variables from the widget settings. */
    $title = apply_filters('widget_title', $instance['title'] );
    $levels = $instance['levels'];
    $orderby = $instance['orderby'];
    $show_thumbs = isset( $instance['show_thumbs'] ) ? $instance['show_thumbs'] : false;
    $thumb_size = $instance['thumb_size'];


    /* Before widget (defined by themes). */
    echo $before_widget;

    /* Display the widget title if one was input (before and after defined by themes). */
    if ( $title ) echo $before_title . $title . $views_posts_link . $after_title;

    foreach($levels as $level){
      client_logos($args);
    }

    /* After widget (defined by themes). */
    echo $after_widget;
  }

  /**
   * Update the widget settings.
   */
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;

    /* Strip tags for title and name to remove HTML (important for text inputs). */
    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['levels'] = $new_instance['levels'];
    $instance['orderby'] = strip_tags( $new_instance['post_offset'] );
    $instance['show_thumbs'] = strip_tags( $new_instance['excerpt_length'] );

    return $instance;
  }


  /**
   * Displays the widget settings controls on the widget panel.
   * Make use of the get_field_id() and get_field_name() function
   * when creating your form elements. This handles the confusing stuff.
   */
  function form( $instance ) {
    $inputs = new KwikInputs();

    /* Set up some default widget settings. */
    $defaults = array( 'title' => esc_html__('Member Companies', 'kwik'),
    'levels' => array(),
    'orderby' => 'menu_order',
    'order' => 'ASC',
    'show_thumbs' => 0
    );
    $instance = wp_parse_args( (array) $instance, $defaults );
    ?>

    <script type="text/javascript">
    jQuery(document).ready(function ($) {
      $('#<?php echo $this->get_field_id( "show_date" ); ?>').click( function() {
        var date_style = $("#<?php echo $this->get_field_id( 'show_date_style' ); ?>-label"), show_date_cb = $(this);
        date_style.toggle(250, function(){
          if(show_date_cb.attr('checked') === undefined) $('input[type="checkbox"]', $(this)).removeAttr('checked');
        });
      });
    });
    </script>

    <!-- Widget Title: Text Input -->
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title:', 'kwik'); ?></label>
      <input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
    </p>

    <!-- Client Levels -->
    <?php
    $terms = get_terms("client_levels", 'orderby=id&hide_empty=0'  );
    echo $inputs->markup('h4', __('Levels: ', 'kwik'));

    foreach ($terms as $term) {
      $cbAttrs = array(
        'id'=> $this->get_field_name( 'levels' ).'-'.$term->slug
        );
      $cbAttrs['checked'] = $instance['levels'][$term->slug] ? TRUE : FALSE ;
      echo $inputs->cb($this->get_field_name( 'levels' ).'['.$term->slug.']', $term->slug, $term->name.': ', $cbAttrs);
    }
    ?>

    <!-- Show Thumbnails -->
    <p>
      <label for="<?php echo $this->get_field_id( 'show_thumbs' ); ?>">
          <input class="checkbox" type="checkbox" <?php checked( $instance['show_thumbs'], true ); ?> id="<?php echo $this->get_field_id( 'show_thumbs' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbs' ); ?>" value="1" <?php checked('1', $instance['show_thumbs']); ?> />
          <?php esc_html_e('Show thumbnails', 'kwik'); ?>
      </label>
    </p>

<?php
if($instance['show_thumbs']) : ?>
    <!-- Thumb Dimension -->
    <p>
      <label for="<?php echo $this->get_field_id( 'thumb_size' ); ?>"><?php esc_html_e('Thumbnail Dimensions:', 'kwik'); ?></label><br />
      <input type="text" name="<?php echo $this->get_field_name( 'thumb_size' ); ?>[]" value="<?php echo $instance['thumb_size'][0]; ?>" size="5" maxlength="4" />
      <span> &#10005; </span>
      <input type="text" name="<?php echo $this->get_field_name( 'thumb_size' ); ?>[]" value="<?php echo $instance['thumb_size'][1]; ?>" size="5" maxlength="4" />
      <br />
      <span style="width:58px;display:inline-block;"><?php esc_html_e('Width', 'kwik'); ?></span>&#10005;<span style="margin-left:9px;width:150px;"><?php esc_html_e('Height in pixels', 'kwik'); ?></span>
    </p>

<?php endif; ?>

<?php
  }
}

