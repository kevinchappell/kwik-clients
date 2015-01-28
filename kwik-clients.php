<?php
/*
Plugin Name: Kwik Clients
Plugin URI: http://kevin-chappell.com/kwik-clients
Description: Display and manage your clients and their logos. Works well for attributing resources or portfolio work.
Author: Kevin Chappell
Version: .1.1
Author URI: http://kevin-chappell.com
 */


define('K_CLIENTS_BASENAME', basename(dirname(__FILE__)));
define('K_CLIENTS_SETTINGS', preg_replace('/-/', '_', K_CLIENTS_BASENAME).'_settings');
define('K_CLIENTS_URL', plugins_url('', __FILE__));
define('K_CLIENTS_PATH', dirname(__FILE__));
define('K_CLIENTS_CPT', 'clients');

// Load the core.
  require_once __DIR__ . '/inc/class.kwik-clients.php';
  kwikclients();
