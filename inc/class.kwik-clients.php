<?php

require_once 'class.kwik-clients-helpers.php';

class KwikClients
{
    static $helpers;
    const CPT = 'clients';

    public function __construct()
    {

        add_action('init', array($this, 'clients_create_post_type'));

        if (is_admin()) {
            $this->admin();
        } else {
            add_action('wp_enqueue_scripts', array($this, 'scripts_and_styles'));
        }

        // widgets
        self::load_widgets();

        // Cleanup on deactivation
        register_deactivation_hook(__FILE__, array($this, '__destruct'));
    }

    public function __destruct()
    {
        // Do garbage cleanup stuff here
    }

    // function showConstant() {
    //     echo  self::CONSTANT . "\n";
    // }

    public function admin()
    {
        if (!isset($this->admin)) {
            require_once __DIR__ . '/class.kwik-clients-admin.php';
            require_once __DIR__ . '/class.kwik-clients-settings.php';
            $this->admin = new KwikClientsAdmin($this);
        }
        return $this->admin;
    }

    public function scripts_and_styles()
    {
        wp_enqueue_script('jquery-cycle', 'http://malsup.github.io/min/jquery.cycle2.min.js', array('jquery'));
        wp_enqueue_style('kwik-clients-css', K_CLIENTS_URL . '/css/' . K_CLIENTS_BASENAME . '.css', false, '2014-12-31');
    }

    public function clients_create_post_type()
    {

        $settings = get_option(K_CLIENTS_SETTINGS);

        $plugin = array(
            'name' => isset($settings['name']) ? $settings['name'] : 'Client',
            'name_plural' => isset($settings['name_plural']) ? $settings['name_plural'] : 'Clients',
            'dash_icon' => isset($settings['dash_icon']) ? $settings['dash_icon'] : 'dashicons-awards'
        );

        self::create_clients_taxonomies();

        register_post_type('clients',
            array(
                'labels' => array(
                    'name' => __('Clients', 'kwik'),
                    'all_items' => __($plugin['name_plural'], 'kwik'),
                    'singular_name' => __($plugin['name'], 'kwik'),
                    'add_new' => __("Add ${plugin['name']}", 'kwik'),
                    'add_new_item' => __("Add New ${plugin['name']}", 'kwik'),
                    'edit_item' => __("Edit ${plugin['name']}", 'kwik'),
                    'menu_name' => __($plugin['name_plural'], 'kwik'),
                ),
                'menu_icon' => $plugin['dash_icon'],
                'menu_position' => 5,

                'supports' => array('title', 'editor', 'thumbnail', 'author'),
                'public' => true,
                'exclude_from_search' => false,
                'has_archive' => true,
                'taxonomies' => array('client_sector', 'client_levels'),
                // 'register_meta_box_cb' => 'add_clients_metabox',
                'rewrite' => array('slug' => 'clients'),
                'query_var' => true,
            )
        );

        add_image_size('client_logo', 240, 240, false);
        flush_rewrite_rules(false);
    }

    public function create_clients_taxonomies()
    {

        $client_sector_labels = array(
            'name' => _x('Industry', 'taxonomy general name'),
            'singular_name' => _x('Industry', 'taxonomy singular name'),
            'search_items' => __('Search Industries'),
            'all_items' => __('All Industries'),
            'edit_item' => __('Edit Industry'),
            'update_item' => __('Update Industry'),
            'add_new_item' => __('Add New Industry'),
            'new_item_name' => __('New Industry'),
        );

        register_taxonomy('client_sector', array('clients'), array(
            'hierarchical' => true,
            'labels' => $client_sector_labels,
            'show_ui' => true,
            'query_var' => true,
            'show_admin_column' => true,
            'rewrite' => array('slug' => 'member-industry', 'hierarchical' => true),
        ));

        $client_levels_labels = array(
            'name' => _x('Level', 'taxonomy general name'),
            'singular_name' => _x('Level', 'taxonomy singular name'),
            'search_items' => __('Search Levels'),
            'all_items' => __('All Levels'),
            'edit_item' => __('Edit Level'),
            'update_item' => __('Update Level'),
            'add_new_item' => __('Add New Level'),
            'new_item_name' => __('New Level'),
        );

        register_taxonomy('client_levels', array('clients'), array(
            'hierarchical' => true,
            'labels' => $client_levels_labels,
            'show_ui' => true,
            'query_var' => true,
            'show_admin_column' => true,
            'rewrite' => array('slug' => 'member-level', 'hierarchical' => true),
        ));

    }

    public function member_table()
    {
        ?>
<div class="member_table">
<?php $terms = get_terms("client_levels", 'orderby=id&hide_empty=0');
        foreach ($terms as $term) {
            $clients = new WP_Query(array(
                'post_type' => 'clients',
                'posts_per_page' => -1,
                $term->taxonomy => $term->slug,
                'order' => 'ASC',
                'orderby' => 'menu_order',
            ));
            echo '<h3>' . $term->name . ' Level</h3>';

            if ($clients->have_posts()): ?>
          <ul class="mem_level-<?php echo $term->slug;?>clear">
<?php while ($clients->have_posts()):$clients->the_post();?>
	              <li><?php if ($term->term_id != 27) {?><a href="<?php the_permalink();?>" title="<?php the_title();?>"><?php }

                if (has_post_thumbnail()) {
                    the_post_thumbnail('client_logo');
                } else {
                    echo "<span>";
                    the_title();
                    echo "</span>";
                }
                ?><?php if ($term->term_id != 27) {?></a><?php }?></li>
	<?php endwhile;?>
</ul>
<?php else:
                echo '<p>' . $term->name . ' membership level available</p>';
            endif;?>
        <?php wp_reset_postdata(); // Don't forget to reset again!
        }?>
    </div><?php
}// member_table()

/**
 * Adds `membership_table` shortcode.
 * @param  [Array] $atts array of attribute to pass
 * @return [String]      Markup to display array of client data
 *
 * Usage: [membership_table foo="foo-value"]
 * TODO: use Kwik Framework markup generator
 */
    public function membership_table($atts)
    {
        extract(shortcode_atts(array(
            'foo' => 'something',
            'bar' => 'something else',
        ), $atts));

        $memb_table = '<!-- BEGIN [membership_table] -->';
        $terms = get_terms("client_levels", 'orderby=id&hide_empty=0&exclude=27');

        $memb_table .= '<table class="mem_table" cellpadding="5">
    <thead>
      <tr>';
        $memb_table .= '<th class="column-mem_level_img"></th>';
        $memb_table .= '<th class="column-mem_level">' . __('Membership Level', 'kwik') . '</th>';
        $memb_table .= '<th class="column-fee">' . __('Annual Fee*', 'kwik') . '</th>';
        $memb_table .= '<th class="column-fte">' . __('FTEs', 'kwik') . '</th>';
        // $memb_table .= '<th class="column-ipc">'.__('IP Contribution', 'kwik' ).'</th>';
        $memb_table .= '<th class="column-tsc">' . __('Technical Steering Commitee', 'kwik') . '</th>';
        $memb_table .= '<th class="column-position">' . __('Board/Voting <br/>Position', 'kwik') . '</th>';
        $memb_table .= '</tr>
    </thead>
    <tbody data-post-type="client_levels">';

        foreach ($terms as $term) {
            $t_id = $term->term_id;
            $term_meta = get_option("taxonomy_$t_id");
            $img = '';

            if (function_exists('taxonomy_image_plugin_get_image_src')) {
                $associations = taxonomy_image_plugin_get_associations();
                if (isset($associations[$term->term_id])) {
                    $attachment_id = (int) $associations[$term->term_id];
                    $img = wp_get_attachment_image($attachment_id, 'medium');
                }
            }

            $memb_table .= '<tr>';
            $memb_table .= '<td class="mem_level_img">' . $img . '</td>';
            $memb_table .= '<td class="mem_level_name">' . $term->name . '</td>';
            $memb_table .= '<td>' . (esc_attr($term_meta['fee'][0]) ? esc_attr($term_meta['fee'][0]) : '0');
            $memb_table .= (esc_attr($term_meta['fee'][1]) ? '<br><em>' . esc_attr($term_meta['fee'][1]) . '</em>' : '');

            $memb_table .= '</td>';
            $memb_table .= '<td>' . (esc_attr($term_meta['fte']) ? esc_attr($term_meta['fte']) : '0') . '</td>';
            // $memb_table .= '<td>'.(esc_attr( $term_meta['ipc'] ) ? esc_attr( $term_meta['ipc'] ) : '').'</td>';
            $memb_table .= '<td>' . (esc_attr($term_meta['tsc']) ? esc_attr($term_meta['tsc']) : '') . '</td>';
            $memb_table .= '<td>' . (esc_attr($term_meta['position']) ? esc_attr($term_meta['position']) : '') . '</td>';
            $memb_table .= '</tr>';
        }
        $memb_table .= '</tbody></table><em style="font-size: 12px;">*' . __('Fee in US Dollars.', 'kwik') . '</em>';
        $memb_table .= '<!-- END [membership_table] -->';

        return $memb_table;
    }

    public function client_logos($args)
    {
        $inputs = new KwikInputs();
        $query_args = array(
            'post_status' => 'publish',
            'post_type' => 'clients',
            'orderby' => $args['orderby'],
            'order' => $args['order'],
        );

        if (isset($args['level'])) {
            $query_args['client_levels'] = $args['level'];
            $term = get_term_by('slug', $args['level'], 'client_levels');
            $cl = $inputs->markup('h3', $term->name . ' Members');
        }

        $client_query = new WP_Query($query_args);

        $i = 1;
        $total = $client_query->post_count;
        if ($client_query->have_posts()):
            $cl = '';
            while ($client_query->have_posts()):$client_query->the_post();
                global $more;
                $more = 0;

                $client_id = get_the_ID();
                $client_name = get_the_title($client_id);
                $logo_or_name = (has_post_thumbnail() && $args['show_thumbs']) ? get_the_post_thumbnail($client_id, 'client_logo') : $client_name;
                $client = $inputs->markup('a', $logo_or_name, array('href' => get_the_permalink($client_id), 'title' => $client_name));
                $cl .= $inputs->markup('div', $client, array("class" => "client client-" . $client_id . " nth-client-" . $i));
                $i++;
            endwhile;
        endif;
        wp_reset_postdata();

        $term_class = isset($term) ? $term->slug . '-members' : null;

        $cl = $inputs->markup('div', $cl, array('class' => array('member-level', $term_class, 'clear')));

        echo $cl;
    }

    public function load_widgets()
    {
        foreach (glob(K_CLIENTS_PATH . "/widgets/*.php") as $inc_filename) {
            require_once $inc_filename;
        }
    }

}// / Class KwikClients

// Singleton
function kwikclients()
{
    global $kwikclients;
    if (!$kwikclients) {
        $kwikclients = new KwikClients();
    }
    return $kwikclients;
}
