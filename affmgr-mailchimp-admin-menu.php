<?php

add_action('wpam_after_main_admin_menu', 'wpam_mailchimp_do_admin_menu');

function wpam_mailchimp_do_admin_menu($menu_parent_slug) {
    add_submenu_page($menu_parent_slug, __("MailChimp", 'wpam'), __("MailChimp", 'wpam'), 'manage_options', 'wpam-mailchimp', 'wpam_mc_admin_interface');
}

function wpam_mc_admin_interface() {
    echo '<div class="wrap">';    
    echo '<h2>Affiliates Manager and MailChimp</h2>';
    echo '<div id="poststuff"><div id="post-body">';

    if (isset($_POST['wpam_mc_save_settings'])) {
        
        $enable_mc_signup = isset($_POST['enable_mc_signup']) ? '1':'';
        
        $options = array(
            'enable_mc_signup' => $enable_mc_signup,
            'mc_api_key' => $_POST['mc_api_key'],
            'mc_list_name' => $_POST['mc_list_name'],
        );
        update_option('wpam_mailchimp_settings', $options); //store the results in WP options table
        echo '<div id="message" class="updated fade">';
        echo '<p>MailChimp Settings Saved!</p>';
        echo '</div>';
    }
    $wpam_mc_settings = get_option('wpam_mailchimp_settings');
    ?>

    <p style="background: #fff6d5; border: 1px solid #d1b655; color: #3f2502; margin: 10px 0;  padding: 5px 5px 5px 10px;">
        Read the <a href="http://wpaffiliatemanager.com/signup-affiliates-mailchimp-list/" target="_blank">usage documentation</a> to learn how to use the mailchimp integration addon
    </p>
    <p>Enter your MailChimp API details below.</p>

    <form action="" method="POST">

        <div class="postbox">
            <h3 class="hndle"><label for="title">MailChimp Integration Settings</label></h3>
            <div class="inside">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Enable MailChimp Signup:</th>
                        <td>
                            <input name="enable_mc_signup" type="checkbox"<?php if($wpam_mc_settings['enable_mc_signup']!='') echo ' checked="checked"'; ?> value="1"/>                            
                            <p class="description">Check this if you want to enable MailChimp signup for your affiliates.</p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">MailChimp API Key:</th>
                        <td>
                            <input type="text" name="mc_api_key" value="<?php echo $wpam_mc_settings['mc_api_key']; ?>" size="60" />
                            <p class="description">The API Key of your MailChimp account (you can find it under the "Account" tab). Make sure to activate it first.</p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">MailChimp List Name:</th>
                        <td>
                            <input type="text" name="mc_list_name" value="<?php echo $wpam_mc_settings['mc_list_name']; ?>" size="60" />
                            <p class="description">Enter the MailChimp list name where you want your affiliates to be signed upto.</p>
                        </td>
                    </tr>
                    
                </table>
            </div></div>
        <input type="submit" name="wpam_mc_save_settings" value="Save" class="button-primary" />

    </form>


    <?php
    echo '</div></div>'; //end of poststuff and post-body
    echo '</div>'; //end of wrap    
}