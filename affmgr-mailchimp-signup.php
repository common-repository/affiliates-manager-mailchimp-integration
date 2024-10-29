<?php
/*
  Plugin Name: Affiliates Manager MailChimp Integration
  Version: 1.0.1
  Plugin URI: http://wpaffiliatemanager.com/
  Author: wp.insider, wpaffiliatemgr
  Author URI: http://wpaffiliatemanager.com/
  Description: An addon for the affiliates manager plugin to signup the affiliates to your MailChimp list after registration.
 */

if (!defined('ABSPATH')){
    exit; //Exit if accessed directly
}

define('AFFMGR_MAILCHIMP_SIGNUP_ADDON_URL', plugins_url('', __FILE__));
define('AFFMGR_MAILCHIMP_SIGNUP_ADDON_PATH', plugin_dir_path(__FILE__));

include_once('affmgr-mailchimp-admin-menu.php');
include_once('affmgr-mailchimp-action.php');
