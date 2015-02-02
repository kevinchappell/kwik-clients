<?php
/**
 * KwikClients Settings Page
 * @package KwikClients
 * @subpackage KwikClientsSettings
 * @since KwikClients 1.0
 */

class KwikClientsSettings
{
    private $options;

    public function __construct()
    {

        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_menu', array($this, 'add_settings_page'));
    }

    public function add_settings_page()
    {
        $settings = $this->get_options();

        $this->plugin =  get_plugin_data( K_CLIENTS_PATH.'/'.K_CLIENTS_BASENAME.'.php', true, true );
        $settings_page = add_submenu_page(
            'edit.php?post_type='.K_CLIENTS_CPT,
            __("{$this->plugin['Name']} Settings", 'kwik'),
            __('Settings', 'kwik'),
            'manage_options',
            K_CLIENTS_BASENAME,
            array($this, 'settings_page')
        );

        if (!$settings_page) {
            return;
        }

        add_action("load-$settings_page", array($this, 'help_screen'));
    }

    public function settings_init()
    {
        $utils = new KwikUtils();
        $kwik_settings = new KwikSettings();
        $default_settings = $this->default_options();
        $kwik_settings->settings_init(KT_BASENAME, K_CLIENTS_SETTINGS, $default_settings);
    }

    public function help_screen()
    {

        $general_help = '<p>' . __('Some themes provide customization options that are grouped together on a Theme Options screen. If you change themes, options may change or disappear, as they are theme-specific. Your current theme, KwikClients, provides the following Theme Options:', 'kwik') . '</p>' .
        '<ol>' .
        '<li>' . __('<strong>Color Scheme</strong>: You can choose a color palette of "Light" (light background with dark text) or "Dark" (dark background with light text) for your site.', 'kwik') . '</li>' .
        '<li>' . __('<strong>Link Color</strong>: You can choose the color used for text links on your site. You can enter the HTML color or hex code, or you can choose visually by clicking the "Select a Color" button to pick from a color wheel.', 'kwik') . '</li>' .
        '<li>' . __('<strong>Default Layout</strong>: You can choose if you want your site&#8217;s default layout to have a sidebar on the left, the right, or not at all.', 'kwik') . '</li>' .
        '</ol>' .
        '<p>' . __('Remember to click "Save Changes" to save any changes you have made to the theme options.', 'kwik') . '</p>';

        $headers_help = '<p>' . __('Specific Header Images can be applied to the various sections of the website. For example, on the Portfolio page you may want to a specific work or a map on the Contact Page.', 'kwik') . '</p>' .

        '<p>' . __('Remember to click "Save Changes" to save any changes you have made to the theme options.', 'kwik') . '</p>';

        $sidebar = '<p><strong>' . __('For more information:', 'kwik') . '</strong></p>' .
        '<p>' . __('<a href="http://codex.wordpress.org/Appearance_Theme_Options_Screen" target="_blank">Documentation on Theme Options</a>', 'kwik') . '</p>' .
        '<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>', 'kwik') . '</p>';

        $screen = get_current_screen();

        if (method_exists($screen, 'add_help_tab')) {
            // WordPress 3.3+
            $screen->add_help_tab(
                array(
                    'title' => __('General', 'kwik'),
                    'id' => 'general-options-help',
                    'content' => $general_help
                )
            );
            $screen->add_help_tab(
                array(
                    'title' => __('Header', 'kwik'),
                    'id' => 'header-options-help',
                    'content' => $headers_help
                )
            );

            $screen->set_help_sidebar($sidebar);
        } else {
            // WordPress 3.2
            add_contextual_help($screen, $help . $sidebar);
        }
    }

    public function settings_page()
    {
        $settings = $this->get_options();
        echo '<div class="wrap">';
        echo KwikInputs::markup('h2', __("{$this->plugin['Name']} Settings", 'kwik'));
        echo '<form action="options.php" method="post">';
        settings_fields(K_CLIENTS_SETTINGS);
        echo KwikSettings::settings_sections(K_CLIENTS_SETTINGS, $settings);
        echo '</form>';
        echo '</div>';
    }

    public static function get_options()
    {
        return get_option(K_CLIENTS_SETTINGS, array(&$this, 'default_options'));
    }

    public function default_options()
    {
        $kwik_clients_default_options = array(
            'general' => array(
                'section_title' => __('General', 'kwik'),
                'section_desc' => __('General plugin settings', 'kwik'),
                'settings' => array(
                    'name' => array(
                        'type' => 'text',
                        'title' => __('Singular Name', 'kwik'),
                        'value' => 'Client',
                        'desc' => __('Client, customer, Member, Colleague, Team etc', 'kwik'),
                    ),
                    'name_plural' => array(
                        'type' => 'text',
                        'title' => __('Plural Name', 'kwik'),
                        'value' => 'Clients',
                        'desc' => __('Clients, customers, Members, Colleagues, Teams etc', 'kwik'),
                    ),
                    'dash_icon' => array(
                        'type' => 'select',
                        'title' => __('Icon', 'kwik'),
                        'value' => 'dashicons-awards',
                        'options' => K_CLIENTS_HELPERS::icons(),
                        'desc' => __('Select the Primary icon for this user type', 'kwik')
                    )
                )
            )
        );

        return apply_filters('kwik_clients_default_options', $kwik_clients_default_options);
    }

}// END KwikClientsSettings

if (is_admin()) {
    $kwik_theme_options = new KwikClientsSettings();
}
