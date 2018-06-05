<?php
/*
 * Plugin Name: InstaFetcher by Jakub Bajzath
 * Plugin URI: https://jakub.bajzath.sk
 * Description: TBA
 * Version: 1.0.0
 * Author: Jakub Bajzath
 * Author URI: https://jakub.bajzath.sk
 * Text Domain: jb-instafetcher
 */

$plugin_dir = str_replace(basename(__FILE__), "", plugin_basename(__FILE__));
$plugin_dir = substr($plugin_dir, 0, strlen($plugin_dir) - 1);
define('JB_INSTAFETCHER_PATH', plugin_dir_path(__FILE__));
define('JB_INSTAFETCHER_DIR', $plugin_dir);
define('JB_INSTAFETCHER_INDEX', __FILE__);

// Core class
require_once 'lib' . DIRECTORY_SEPARATOR . 'Instafetcher_Core.php';

// Plugin classes
require_once 'lib' . DIRECTORY_SEPARATOR . 'Instafetcher_ACFD.php';
require_once 'lib' . DIRECTORY_SEPARATOR . 'Instafetcher_Cron.php';
require_once 'lib' . DIRECTORY_SEPARATOR . 'Instafetcher_Database.php';
require_once 'lib' . DIRECTORY_SEPARATOR . 'Instafetcher_Images.php';
require_once 'lib' . DIRECTORY_SEPARATOR . 'Instafetcher_Parser.php';
require_once 'lib' . DIRECTORY_SEPARATOR . 'Instafetcher_Rewrites.php';
require_once 'lib' . DIRECTORY_SEPARATOR . 'Instafetcher_Users.php';

// REST
require_once 'lib' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'Instafetcher_REST.php';

// Vendor
require_once 'lib' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'simple_html_dom.php';
require_once 'lib' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'Emoji.php';

// Model
require_once 'lib' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'Instafetcher_Model_Images.php';
require_once 'lib' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'Instafetcher_Model_Mentions.php';
require_once 'lib' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'Instafetcher_Model_Users.php';

// Shortcodes
require_once 'lib' . DIRECTORY_SEPARATOR . 'shortcode' . DIRECTORY_SEPARATOR . 'Instafetcher_Shortcodes.php';

Instafetcher_Core::init();

register_activation_hook(__FILE__, array('Instafetcher_Database', 'install'));
register_activation_hook(__FILE__, array('Instafetcher_Database', 'install_data'));

register_activation_hook(__FILE__, array('Instafetcher_Cron', 'registerCronJobs'));
register_deactivation_hook(__FILE__, array('Instafetcher_Cron', 'removeCronJobs'));