<?php

use \WPAM\MailChimp\MailChimp;

add_action('wpam_front_end_registration_form_submitted', 'wpam_do_mailchimp_signup', 10, 2);

function wpam_do_mailchimp_signup($model, $request) {
    
    $first_name = strip_tags($request['_firstName']);
    $last_name = strip_tags($request['_lastName']);
    $email = strip_tags($request['_email']);

    $wpam_mc_settings = get_option('wpam_mailchimp_settings');
    $target_list_name = $wpam_mc_settings['mc_list_name'];
    $enable_mc_signup = $wpam_mc_settings['enable_mc_signup'];
    if($enable_mc_signup != '1'){
        WPAM_Logger::log_debug("Mailchimp integration addon - Mailchimp signup is disabled in the settings.");
        return;
    }

    WPAM_Logger::log_debug("Mailchimp integration addon. After registration hook. Debug data: " . $target_list_name . "|" . $email . "|" . $first_name . "|" . $last_name);

    if (empty($target_list_name)) {//This level has no mailchimp list name specified for it
        return;
    }

    WPAM_Logger::log_debug("Mailchimp integration - Doing list signup...");
    
    $api_key = $wpam_mc_settings['mc_api_key'];
    if (empty($api_key)) {
        WPAM_Logger::log_debug("MailChimp API Key value is not saved in the settings. Go to MailChimp settings and enter the API Key.", 4);
        return;
    }

    WPAM_Logger::log_debug("Mailchimp integration - target list name: " . $target_list_name);

    // let's check if we have interest groups delimiter (|) present. my-list-1 | groupname1, groupname2
    $res_array = explode('|', $target_list_name);
    if (count($res_array) > 1) {
        // we have interest group(s) specified
        // first, let's set list name
        $target_list_name = trim($res_array[0]);
        // now let's get interest group(s). We'll deal with those later.
        $interest_group_names = explode(',', $res_array[1]);
    }

    include_once(AFFMGR_MAILCHIMP_SIGNUP_ADDON_PATH . 'lib/MailChimp.php');

    try {
        $api = new MailChimp($api_key);
    } catch (Exception $e) {
        WPAM_Logger::log_debug("MailChimp API error occudred: " . $e->getMessage(), 4);
        return;
    }

    //Get the list id for the target list.
    $list_filter = array();
    $list_filter['list_name'] = $target_list_name;
    $args = array('count' => 100, 'offset' => 0); //By default MC APIv3.0 returns 10 lists only. Use it to retrieve more than 10.
    $all_lists = $api->get('lists', $args);
    $lists_data = $all_lists['lists'];
    $found_match = false;
    foreach ($lists_data as $list) {
        WPAM_Logger::log_debug("Checking list name : " . $list['name']);
        if (strtolower($list['name']) == strtolower($target_list_name)) {
            $found_match = true;
            $list_id = $list['id'];
            WPAM_Logger::log_debug("Found a match for the list name on MailChimp. List ID :" . $list_id);
            break;
        }
    }
    if (!$found_match) {
        WPAM_Logger::log_debug("Could not find a list name in your MailChimp account that matches with the target list name: " . $target_list_name, 4);
        return;
    }
    WPAM_Logger::log_debug("List ID to subscribe to:" . $list_id);

    //Disable double opt-in is now controlled by status field. Set it to "pending" for double opt-in.
    $status = 'pending';
    /*
    if (isset($wpam_mc_settings['disable_mc_double_optin']) && $wpam_mc_settings['disable_mc_double_optin'] != '') {
        $status = 'subscribed'; //Don't use double opt-in
    }
    */

    //Create the merge_vars data
    $merge_vars = array('FNAME' => $first_name, 'LNAME' => $last_name, 'INTERESTS' => '');
    //send_welcome is no longer used. When member subscribe, it will send welcome email if set in your list settings.

    $api_arr = array('email_address' => $email, 'status_if_new' => $status, 'status' => $status, 'merge_fields' => $merge_vars);
    if (isset($interests)) {
        //Set the interest groups array.
        $api_arr['interests'] = $interests;
    }

    $member_hash = md5(strtolower($email)); //The MD5 hash of the lowercase version of the list member's email address.
    $retval = $api->put("lists/" . $list_id . "/members/".$member_hash, $api_arr);
    
    if (!$api->success()) {
        WPAM_Logger::log_debug("Unable to subscribe.", 4);
        WPAM_Logger::log_debug("Error=" . $api->getLastError(), 4);
        WPAM_Logger::log_debug("Error Response=" . $api->getLastResponse(), 4);
        return;
    } 
    else {
        WPAM_Logger::log_debug("MailChimp Signup was successful.");
    }
    
}
