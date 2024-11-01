<?php

// =============================================================================
// StreamIn
//
// Released under the GNU General Public Licence v2
// http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
//
// Please refer all questions/requests to: office@streamin.io
//
// This is an add-on for WordPress
// http://wordpress.org/
// =============================================================================

// =============================================================================
// This piece of software is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY, without even the implied warranty of MERCHANTABILITY or
// FITNESS FOR A PARTICULAR PURPOSE.
// =============================================================================

/*
  Plugin Name: StreamIn
  Plugin URI: http://streamin.io
  Description:  Push notifications from your website or blog via StreamIn
  Version: 1.0
  Author: Eontek
  Author URI: http://eontek.rs
 */

// define some settings
$STREAMIN_API = 'http://app.streamin.io/api/hook';

// include webhooks definition
require dirname(__FILE__) . '/webhooks.php';

/**
 * Forms and returns website wiki-formatted link
 *
 * @return string
 */
function streamin_site_link_wiki() {
    return '[' . get_site_url() . ' ' . get_bloginfo('name') . ']';
}

/**
 * Saves a log entry
 *
 * @param string $hook
 * @param string $user_key
 * @param string $error (optional)
 */
function streamin_save_log_entry($hook,$user_key,$error = null) {
    global $wpdb;

    // create a log entry
    $log = array(
        'hook' => $hook,
        'user_key' => $user_key,
        'message' => 'SUCCESS'
    );

    // set error
    if($error != null) $log['message'] = $error;

    // save log to database
    $result = $wpdb->insert('wp_streamin_log', $log, array('%s','%s','%s','%s'));
}

/*
 * Performs an API call to StreamIn
 *
 * @param string $hook
 * @param string $user_key
 * @param string $message
 *
 * @return boolean true if API call succeeded
 */
function streamin_api_call($hook,$user_key,$message) {
    global $STREAMIN_API;

    // init cURL
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_URL,"{$STREAMIN_API}/word_press_hook/{$user_key}");
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_POST,1);
    curl_setopt($curl,CURLOPT_POSTFIELDS,"message=" . urlencode($message));

    // execute cURL call
    $response = curl_exec($curl);

    // get HTTP status
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    // close cURL
    curl_close($curl);

    // skip if HTTP status wrong
    if($http_status != 200) {
        streamin_save_log_entry($hook,$user_key,'API call failed');
        return false;
    }

    // decode response
    $response = json_decode($response);

    // skip if invalid JSON
    if($response === null || $response === false) {
        streamin_save_log_entry($hook,$user_key,'Invalid API response');
        return false;
    }

    // check response status
    if($response->status != 1) {
        streamin_save_log_entry($hook,$user_key,$response->message);
        return false;
    }

    // respond success
    streamin_save_log_entry($hook,$user_key);
    return true;
}

/**
 * Returns enabled webhooks
 *
 * @return array $webhooks
 */
function streamin_get_webhooks() {
    // get webhooks settings
    $webhooks = get_option('streamin_enabled_webhooks', array());

    // make sure an array is returned
    if (!is_array($webhooks)) return array();

    // return webhooks array
    return $webhooks;
}

/**
 * Registers enabled webhooks
 */
function streamin_register_webhooks() {
    global $STREAMIN_WEBHOOKS;

    // get webhooks settings
    $webhooks = streamin_get_webhooks();

    // go trough all webhook handles
    foreach($webhooks as $webhook => $user_key) {
        // skip if webhook not supported
        if(!in_array($webhook,array_keys($STREAMIN_WEBHOOKS))) continue;

        // add webhook action
        add_action($webhook, "streamin_webhook_$webhook");
    }
}

/**
 * Activates StreamIn plugin
 */
function streamin_activate() {
    global $wpdb;

    // create log table
    @$wpdb->query('
      CREATE TABLE wp_streamin_log (
        log_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        hook VARCHAR(64) NOT NULL,
        user_key varchar(64) NOT NULL,
        message VARCHAR(500) NOT NULL ,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
      ) ENGINE = MYISAM;
    ');
}

/**
 * Deactivates StreamIn plugin
 */
function streamin_deactivate() {
    global $wpdb;

    // delete log table
    $wpdb->query('DROP TABLE wp_streamin_log');
}

/**
 * Renders settings HTML
 */
function streamin_render_settings() {
    global $STREAMIN_WEBHOOKS;

    // get enabled webhooks
    $webhooks = streamin_get_webhooks();

    // render HTML
    require dirname(__FILE__) . '/settings.php';
}

/**
 * Renders logs HTML
 */
function streamin_render_logs() {
    global $wpdb,$STREAMIN_WEBHOOKS;

    // get logs
    $logs = $wpdb->get_results("SELECT * FROM wp_streamin_log ORDER BY created_at ASC");

    // make sure an array is used for rendering
    if (!is_array($logs)) $logs = array();

    // render HTML
    require dirname(__FILE__) . '/logs.php';
}

/**
 * Adds a menu items for setting up StreamIn webhooks and managing StreamIn logs
 */
function streamin_menu() {
    add_submenu_page(
        'options-general.php', // parent
        'StreamIn Webhooks Settings', // page title
        'StreamIn Webhooks', // menu item title
        'administrator', // permission
        'streamin-settings', // unique page name
        'streamin_render_settings' // rendering function
    );
    add_submenu_page(
        'options-general.php', // parent
        'StreamIn Logs', // page title
        'StreamIn Logs', // menu item title
        'administrator', // permission
        'streamin-logs', // unique page name
        'streamin_render_logs' // rendering function
    );
}

/**
 * Updates settings
 */
function streamin_action_update_settings() {
    global $STREAMIN_WEBHOOKS;

    // init enabled webhooks array
    $webhooks = array();

    // go trough all webhooks
    foreach($STREAMIN_WEBHOOKS as $webhook => $info) {
        // skip if not enabled
        if(!isset($_REQUEST["{$webhook}_check"])) continue;

        // save user key in webhooks array
        $webhooks[$webhook] = $_REQUEST["{$webhook}_key"];
    }

    // save webhooks array
    update_option('streamin_enabled_webhooks',$webhooks);

    // redirect to settings page
    header('Location: ' . WP_ADMIN_URL . '/options-general.php?page=streamin-settings&updated=true');
    die;
}

/**
 * Clears StreamIn log
 */
function streamin_action_clear_log() {
    global $wpdb;

    // clear log
    $wpdb->query('TRUNCATE wp_streamin_log');

    // redirect to log page
    header('Location: ' . WP_ADMIN_URL . '/options-general.php?page=streamin-logs&cleared=true');
    die;
}

/**
 * Handles StreamIn requests
 */
function streamin_request_handler() {
    // check if an action is to be performed
    if(empty($_REQUEST['streamin_action'])) return;

    // check if user is admin
    if(!is_admin()) return;

    // perform action
    switch($_REQUEST['streamin_action']) {
        case 'update_settings': streamin_action_update_settings(); break;
        case 'clear_log': streamin_action_clear_log(); break;
    }
}

// register menu item
add_action('admin_menu', 'streamin_menu');

// register all enabled webhooks
streamin_register_webhooks();

// register streamin activation hook
register_activation_hook( __FILE__,'streamin_activate');

// register streamin deactivation hook
register_deactivation_hook( __FILE__,'streamin_deactivate');

// register request handler
add_action('init', 'streamin_request_handler', 9999);