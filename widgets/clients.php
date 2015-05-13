<?php
/**
 * Widget Name: Client Logos
 * Description: Show your client logos in widgetized areas
 * Version: 0.2
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
		$this->WP_Widget( 'cpt-clients-widget', esc_html__( 'Kwik Clients Logos', 'kwik' ), $widget_ops, $control_ops );
	}

	function add_style($cpr) {
		$width = $cpr !== 0 ? 100 / $cpr : 100;
		$width = $width - 2 + ( 2 / $cpr ); // factor in the margin-right
		$add_style = '<style type="text/css">';
		$add_style .= '@media screen and (min-width: 740px) {';
			$add_style .= '.cpt_clients_widget .client{width:'.round($width, 2).'%}';
			$add_style .= '.cpt_clients_widget .client:nth-child('.$cpr.'){margin-right:0}';
		$add_style .= '}';
		$add_style .= '</style>';
		echo $add_style;
	}

	/**
	 * Render the widget for users
	 */
	function widget( $args, $instance ) {
		$KwikClients = new KwikClients;
		extract( $args );

		// variables from widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		$orderby = $instance['orderby'];
		$order = $instance['order'];
		$clients_per_row = intval($instance['clients_per_row']);
		$show_thumbs = isset( $instance['show_thumbs'] ) ? 1 : 0;
		$group_by_level = isset( $instance['group_by_level'] ) ? 1 : 0;

		$args = array(
			'levels' => $instance['levels'],
			'orderby' => $instance['orderby'],
			'order' => $instance['order'],
			'show_thumbs' => $instance['show_thumbs'],
			'group_by_level' => $instance['group_by_level']
		);

		// custom styling based on widget settings
		self::add_style($clients_per_row);

		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		if( ! $instance['levels'] ){
			$KwikClients->client_logos($args);
		} else {
			foreach($instance['levels'] as $level){
				$args['level'] = $level;
				$KwikClients->client_logos($args);
			}
		}

		echo $after_widget;
	}

	/**
	 * Update widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['levels'] = $new_instance['levels'];
		$instance['orderby'] = strip_tags( $new_instance['orderby'] );
		$instance['order'] = strip_tags( $new_instance['order'] );
		$instance['show_thumbs'] = $new_instance['show_thumbs'];
		$instance['group_by_level'] = $new_instance['group_by_level'];
		$instance['clients_per_row'] = strip_tags( $new_instance['clients_per_row'] );
		return $instance;
	}


	/**
	 * Widget settings form
	 */
	function form( $instance ) {
		$inputs = new KwikInputs();

		// Set up some default widget settings.
		$defaults = array( 'title' => esc_html__('Member Companies', 'kwik'),
			'levels' => array(),
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'show_thumbs' => 0,
			'group_by_level' => 0,
			'clients_per_row' => 6
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		// Widget Title: Text Input
		$output = $inputs->text($this->get_field_name( 'title' ), $instance['title'], __('Title: ', 'kwik'));

		// Client Levels
		$terms = get_terms("client_levels", 'orderby=id&hide_empty=0');
		$output .= $inputs->markup('h3', __('Levels: ', 'kwik'));

		foreach ($terms as $term) {
			$cbAttrs = array(
				'id'=> $this->get_field_name( 'levels' ).'-'.$term->slug,
				'checked' => isset( $instance['levels'][$term->slug] ) ? TRUE : FALSE
				);
			$output .= $inputs->cb($this->get_field_name( 'levels' ).'['.$term->slug.']', $term->slug, $term->name.': ', $cbAttrs);
		}

		$output .= $inputs->select($this->get_field_name( 'orderby' ), $instance['orderby'], __('Order By: ', 'kwik'), NULL, KwikHelpers::order_by());
		$output .= $inputs->select($this->get_field_name( 'order' ), $instance['order'], __('Order: ', 'kwik'), NULL, KwikHelpers::order());
		$output .= $inputs->spinner($this->get_field_name( 'clients_per_row' ), $instance['clients_per_row'], __('Clients per Row: ', 'kwik'), array('min' => '1', 'max'=>'6'));
		$output .= $inputs->cb($this->get_field_name( 'show_thumbs' ), TRUE, __('Show thumbnails: ', 'kwik'), array('checked'=> $instance['show_thumbs'] ? TRUE : FALSE));
		$output .= $inputs->cb($this->get_field_name( 'group_by_level' ), TRUE, __('Group by Level: ', 'kwik'), array('checked'=> $instance['group_by_level'] ? TRUE : FALSE));

		echo $output;

	}
}

