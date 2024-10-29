<?php
///////// Only execute this part of the code if the option 'bpc_hasPro' is set to 1 /////////
/////////////////////////////////////////////////////////////////////////////////////////////
function servnpr() { 
    $serverName = $_SERVER['SERVER_NAME'];
    $serverName = trim($serverName);
    return $serverName;
}

/* Front end registration */
add_action('register_form', 'be_popia_compliant_registration_form', 999);
// Define the registration form function
function be_popia_compliant_registration_form() {
    
    // Retrieve and sanitize input values if available
    $identificationNumber = isset($_POST['user_identification_number']) ? sanitize_text_field($_POST['user_identification_number']) : '';
    $otherIdNumber = isset($_POST['other_identification_number']) ? sanitize_text_field($_POST['other_identification_number']) : '';
    $otherIdType = isset($_POST['other_identification_type']) ? sanitize_text_field($_POST['other_identification_type']) : '';
    $otherIdIssue = isset($_POST['other_identification_issue']) ? sanitize_text_field($_POST['other_identification_issue']) : '';   

    // Display the form fields
    ?>
    <p>
        <div id="saiderror" style="color: red; padding: 5px; display: none; font-size: 14px; line-height: 14px;"></div><br>
        <div style="border: solid black 1px; border-radius: 3px; padding: 10px;">
            <label for="user_identification_number"><?php esc_html_e('South African Identity Number', 'be_popiaCompliant') ?><br />
                <input type="text" id="user_identification_number" name="user_identification_number" value="<?php echo esc_attr($identificationNumber); ?>" class="input" />
            </label>
        </div>  
        <p style="text-align: center;"><span><br><b>OR</b><br>(If not South African ID Number)<br><br></span></p>
        <div style="border: solid black 1px; border-radius: 3px; padding: 10px;">
            <p>
                <label for="other_identification_number"><?php esc_html_e('Passport, Social Security or other Identification Number', 'be_popiaCompliant') ?><br />
                    <input type="text" id="other_identification_number" name="other_identification_number" value="<?php echo esc_attr($otherIdNumber); ?>" class="input" placeholder="If not SA ID Number"/>
                </label>
            </p>
            <p>
                <label for="other_identification_type"><?php esc_html_e('What type of Identification Number is this?', 'be_popiaCompliant') ?><br />
                    <input type="text" id="other_identification_type" name="other_identification_type" value="<?php echo esc_attr($otherIdType); ?>" class="input" placeholder="If not SA ID Number"/>
                </label>
            </p>
            <p>
                <label for="other_identification_issue"><?php esc_html_e('What Country issued this Identification Number?', 'be_popiaCompliant') ?><br />
                    <input type="text" id="other_identification_issue" name="other_identification_issue" value="<?php echo esc_attr($otherIdIssue); ?>" class="input" placeholder="If not SA ID Number"/>
                </label>
            </p>
        </div><br>
        <div style='text-align: center;font-size:10px!important'>(Powered by <a href="https://bepopiacompliant.co.za" target="_blank"><span style="color:#B61F20">Be POPIA Compliant</span></a> & <a href="https://manageconsent.co.za" target="_blank"><span style="color:#7a7a7a">Manage Consent</span></a>)</div><br><br><br>
    <?php
}


add_filter('registration_errors', 'be_popia_compliant_registration_errors', 10, 3);
// registration Field validation
function be_popia_compliant_registration_errors($errors, $sanitized_user_login, $user_email) {

        if (empty($_POST['user_identification_number']) && empty($_POST['other_identification_number'])) {
            $errors->add('user_identification_number', __('<strong>We require some form of Identificatin for POPIA (Without an authentication identifier, you will never be able to <a href="https://www.manageconsent.co.za" target="blank">Manage Your Consent</a></strong>:<br>Please enter your South African ID Number (if South African) <br><br>OR<br><br>Passport, Social Security or other Identification Number (if not using South African ID Number). If you opt for this otion we will also need to know what type of identication was used, and what country issued the document.<br><br>', 'be_popiaCompliant'));
        }

        if (!empty($_POST['user_identification_number']) && !empty($_POST['other_identification_number'])) {
            $errors->add('user_identification_number', __('<strong>Provide only one (1) Identification Number</strong>:<br>If you are a South African Citizen, please only enter your South African Identification Number.<br><br>If you are a foreign citizen, please leave "South African Identity Number" blank and provide:<br> - Your Local Identification number or Passport number.<br>- The type of Identification number you are using.<br>- The country that issued the Identification number.<br><br>', 'be_popiaCompliant'));
        }

        if (!empty($_POST['user_identification_number']) && (strlen($_POST['user_identification_number']) != 13)) {
            $errors->add('user_identification_number', __('<strong>South African ID Number</strong>:<br>Your South African Identity Number does not seem to be correct.<br>', 'be_popiaCompliant'));
        }

        if (!empty($_POST['other_identification_number']) && (empty($_POST['other_identification_type'])) && (empty($_POST['other_identification_issue']))) {
            $errors->add('other_identification_type', __('<strong>When using Passport, Social Security or other Identification Number</strong>:<br>Please also provide your Identification Type and Country of Issue.<br>', 'be_popiaCompliant'));
        }

        if (!empty($_POST['other_identification_number']) && (empty($_POST['other_identification_type'])) && (!empty($_POST['other_identification_issue']))) {
            $errors->add('other_identification_type', __('<strong>When using Passport, Social Security or other Identification Number</strong>:<br>Please also provide the Type of Identification Number you are using.<br>', 'be_popiaCompliant'));
        }

        if (!empty($_POST['other_identification_number']) && (!empty($_POST['other_identification_type'])) && (empty($_POST['other_identification_issue']))) {
            $errors->add('other_identification_issue', __('<strong>When using Passport, Social Security or other Identification Number</strong>:<br>Please also provide the Country of Issue for the Identification number you are using.<br>', 'be_popiaCompliant'));
        }

        if (!empty($_POST['other_identification_number']) && (strlen($_POST['other_identification_number']) < 7)) {
            $errors->add('other_identification_number', __('<strong>Other Identificatin number</strong>:<br>Please provide a number that we will be able to confirm your Identity with, when providing a fake number, you will never be able to <a href="https://www.manageconsent.co.za" target="blank">Manage Your Consent</a>.<br>', 'be_popiaCompliant'));
        }

        if (!empty($_POST['other_identification_number']) && (strlen($_POST['other_identification_type']) < 4) && (!empty($_POST['other_identification_type']))) {
            $errors->add('other_identification_issue', __('<strong>Other Identificatin Type</strong>:<br>Please write out the name of the Identification Type, do not use the abbreviation.<br>', 'be_popiaCompliant'));
        }

        if (!empty($_POST['other_identification_number']) && (strlen($_POST['other_identification_issue']) < 4) && (!empty($_POST['other_identification_issue']))) {
            $errors->add('other_identification_issue', __('<strong>Country of Issue</strong>:<br>Ensure your Country of Issue is correct and fully written out.<br>', 'be_popiaCompliant'));
        } 
    return $errors;
}


add_action('edit_user_created_user', 'be_popia_compliant_user_register');
add_action('user_register', 'be_popia_compliant_user_register');
// save Fields
function be_popia_compliant_user_register($user_id) {
    // Sanitize and validate user_identification_number
    $user_identification_number = isset($_POST['user_identification_number']) ? sanitize_text_field($_POST['user_identification_number']) : '';
    if (!empty($user_identification_number) && strlen($user_identification_number) == 13) {
        update_user_meta($user_id, 'user_identification_number', $user_identification_number);
    }

    // Sanitize and store other_identification_number
    $other_identification_number = isset($_POST['other_identification_number']) ? sanitize_text_field($_POST['other_identification_number']) : '';
    if (!empty($other_identification_number)) {
        update_user_meta($user_id, 'other_identification_number', $other_identification_number);
    }

    // Sanitize and store other_identification_type
    $other_identification_type = isset($_POST['other_identification_type']) ? sanitize_text_field($_POST['other_identification_type']) : '';
    if (!empty($other_identification_type)) {
        update_user_meta($user_id, 'other_identification_type', $other_identification_type);
    }

    // Sanitize and store other_identification_issue
    $other_identification_issue = isset($_POST['other_identification_issue']) ? sanitize_text_field($_POST['other_identification_issue']) : '';
    if (!empty($other_identification_issue)) {
        update_user_meta($user_id, 'other_identification_issue', $other_identification_issue);
    }
}

        
add_action('user_register', 'be_popia_compliant_add_user_details_to_py');
/* Trigger when new user account is created*/
function be_popia_compliant_add_user_details_to_py($user_id) {
    $new_user = get_userdata($user_id);

    if(!get_user_meta( $user_id, 'has_provided_consent', true )){
        $user_email = $new_user->user_email;
        $domain = esc_html(servnpr());
        $first_name = '';
        $surname = '';
        $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/getuserid/" . $user_email);
        $args = array('headers' => array('Content-Type' => 'application/json',), 'body' => array(),);
        $response = wp_remote_get(wp_http_validate_url($url), $args);
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if (401 === $response_code) {echo "Unauthorized access"; }

        if (200 === $response_code) {$body = json_decode($body); if (empty($body)) { } else {foreach ($body as $data) {$py_user_id = $data->id;}}}

        if (isset($py_user_id)) {
            $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/getwpname/" . $py_user_id);
            $args = array('headers' => array('Content-Type' => 'application/json',), 'body' => array(),);
            $response = wp_remote_get(wp_http_validate_url($url), $args);
            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            if (401 === $response_code) {echo "Unauthorized access";}

            if (200 === $response_code) {$body = json_decode($body); foreach ($body as $data) {$first_name = $data->data_officer_first_name; $surname = $data->data_officer_surname;}}}

        if (!$new_user) {error_log('Unable to get user data!'); return;}

        $url  = wp_http_validate_url('https://py.bepopiacompliant.co.za/api/newusercreated/');
        $body = array(
            'domain' => $domain,
            'email' => $user_email,
            'user_id' => $user_id,
            'first_name' => $first_name,
            'surname' => $surname,
            'py_user_id' => $py_user_id
        );

        $args = array(
            'method'      => 'POST',
            'timeout'     => 45,
            'sslverify'   => false,
            'headers'     => array(
                'Content-Type'  => 'application/json',
            ),
            'body'        => wp_json_encode($body),
        );

        $request = wp_remote_post(wp_http_validate_url($url), $args);

        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
            error_log(print_r($request, true));
        }

        $response = wp_remote_retrieve_body($request);
        if (!isset($py_user_id)) {
            $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 8; $i++) {
                $randomString .= $characters[wp_rand(0, $charactersLength - 1)];
            }
            $url  = wp_http_validate_url('https://py.bepopiacompliant.co.za/api/users/');
            
            update_option('the_format', $user_id);

            $id_number = get_user_meta($user_id, 'user_identification_number', true);
                if(strlen($id_number) < 13) {
                    $id_number = get_user_meta($user_id, 'billing_user_SAID', true);
                }
                if(strlen($id_number) < 13) {
                    $id_number = get_user_meta($user_id, 'other_identification_number', true);
                }
                if(strlen($id_number) < 6) {
                    $id_number = get_user_meta($user_id, 'billing_user_OtherID', true);
                }
                update_option( 'test_got_idnumber' , $id_number);

            $body = array(
                'email' => $user_email,
                'username' => $id_number,
                'password' => $randomString
            );

            $args = array(
                'method' => 'POST',
                'timeout' => 45,
                'sslverify' => false,
                'headers' => array(
                'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode($body),
            );

            $request = wp_remote_post(wp_http_validate_url($url), $args);
            if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
                error_log(print_r($request, true));   
            } 
            
            if (200 === $response_code) {
                update_option( 'test_got_response' , 'Yes');

                $body = wp_remote_retrieve_body($request);
                update_option( 'body_before', $body);
                    $body = json_decode($body);
                    update_option( 'body_after', $body);
        
                    foreach ($body as $data) {
                        $id = $body->id;
                        $username = $body->username;
                        $email = $body->email;
                        update_option( 'test_got_id' , $id);
                    }

                $url  = wp_http_validate_url('https://py.bepopiacompliant.co.za/api/newuserprofile/');
                $body = array(
                    'user' => $id,
                    'data_officer_direct_email' => $email,
                    'data_officer_first_name' => $first_name,
                    'data_officer_surname' => $surname,
                    'id_number' => $id_number
                );

                $args = array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'sslverify' => false,
                    'headers' => array(
                    'Content-Type' => 'application/json',
                    ),
                    'body' => wp_json_encode($body),
                );

                $request = wp_remote_post(wp_http_validate_url($url), $args);
            }
        }
    }
}


/* Back end registration */
add_action('user_new_form', 'be_popia_compliant_admin_registration_form');
function be_popia_compliant_admin_registration_form($operation) {
    if ('add-new-user' !== $operation) {
        return;
    }
    $nonce = wp_create_nonce('be_popiaCompliant_admin_registration');
    $user_identification_number = !empty($_POST['user_identification_number']) ? sanitize_text_field($_POST['user_identification_number']) : ''; 
    // Check if the nonce is present and valid before processing the form data
    if (isset($_POST['be_popia_compliant_admin_nonce']) && wp_verify_nonce($_POST['be_popia_compliant_admin_nonce'], 'be_popiaCompliant_admin_registration')) {
        ?><h3><?php esc_html_e('Personal Information for POPIA Purposes', 'be_popiaCompliant'); ?></h3>

        <table class="form-table">
            <tr>
                <th>
                    <div id="saiderror" style="color: red; padding: 5px; display: none; font-size: 14px; line-height: 14px;"></div><br><label for="user_identification_number"><?php esc_html_e('South African Identification Number', 'be_popiaCompliant'); ?></label> <span class="description"></span>
                </th>
                <td>
                    <input type="text" id="user_identification_number" name="user_identification_number" value="<?php echo esc_attr($user_identification_number); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <!-- Add the nonce field to the form -->
        <input type="hidden" name="be_popia_compliant_admin_nonce" value="<?php echo esc_attr($nonce); ?>" />
        <br>OR<br>
        <?php
            $other_identification_number = !empty($_POST['other_identification_number']) ? sanitize_text_field($_POST['other_identification_number']) : '';
        ?>

        <table class="form-table">
            <tr>
                <th><label for="other_identification_number"><?php esc_html_e('Other Identification Number', 'be_popiaCompliant'); ?></label> <span class="description"></span></th>
                <td>
                    <input type="text" id="other_identification_number" name="other_identification_number" value="<?php echo esc_attr($other_identification_number); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <?php
        $other_identification_type = !empty($_POST['other_identification_type']) ? ($_POST['other_identification_type']) : '';
        ?>
        <table class="form-table">
            <tr>
                <th><label for="other_identification_type"><?php esc_html_e('Identification Type', 'be_popiaCompliant'); ?></label> <span class="description"></span></th>
                <td>
                    <input type="text" id="other_identification_type" name="other_identification_type" value="<?php echo esc_attr($other_identification_type); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <?php
        $other_identification_issue = !empty($_POST['other_identification_issue']) ? ($_POST['other_identification_issue']) : '';
        ?>
        <table class="form-table">
            <tr>
                <th><label for="other_identification_issue"><?php esc_html_e('Country of Issue', 'be_popiaCompliant'); ?></label> <span class="description"></span></th>
                <td>
                    <input type="text" id="other_identification_issue" name="other_identification_issue" value="<?php echo esc_attr($other_identification_issue); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <?php
        $bpc_consent_url = !empty($_POST['bpc_consent_url']) ? ($_POST['bpc_consent_url']) : '';
        ?>
        <table class="form-table">
            <tr>
                <th><label for="bpc_consent_url"><?php esc_html_e('Consent Form Link', 'be_popiaCompliant'); ?></label> <span class="description"></span></th>
                <td>
                    <input type="text" id="bpc_consent_url" name="bpc_consent_url" value="<?php echo esc_attr($bpc_consent_url); ?>" class="regular-text" />
                </td>
            </tr>
        </table>   <?php
    } else {
        // Nonce verification failed, handle accordingly (e.g., display an error message)
        echo '<p style="color: red;">Nonce verification failed. Please try again.</p>';
    }
}


add_action('user_profile_update_errors', 'be_popiaCompliant_user_profile_update_errors', 10, 3);

function be_popiaCompliant_user_profile_update_errors($errors, $update, $user) {

    $nonce = wp_create_nonce('be_popia_compliant_registration_nonce');
    // wp_nonce_field('be_popia_compliant_registration_nonce', 'be_popia_compliant_registration_nonce');

    if ($update) {
        return;
    }
    if (isset($_POST['be_popia_compliant_registration_nonce']) && wp_verify_nonce($_POST['be_popia_compliant_registration_nonce'], 'be_popia_compliant_registration_nonce')) {
        if (empty($_POST['user_identification_number']) && empty($_POST['other_identification_number'])) {
            $errors->add('user_identification_number', __('<strong>We require some form of Identificatin for POPIA (Without an authentication identifier, this user will never be able to <a href="https://www.manageconsent.co.za" target="blank">Manage Their Consent</a></strong>:<br>Please enter their South African ID Number (if South African) <br><br>OR<br><br>Passport, Social Security or other Identification Number (if not using South African ID Number). If you opt for this otion we will also need to know what type of identication was used, and what country issued the document.<br><br>', 'be_popiaCompliant'));
        }
        if (!empty($_POST['user_identification_number']) && !empty($_POST['other_identification_number'])) {
            $errors->add('user_identification_number', __('<strong>Provide only one (1) Identification Number</strong>:<br>If you they are a South African Citizen, please only enter their South African Identification Number.<br><br>If they are a foreign citizen, please leave "South African Identity Number" blank and provide:<br> - Their Local Identification number or Passport number.<br>- The type of Identification number they are using.<br>- The country that issued the Identification number.<br><br>', 'be_popiaCompliant'));
        }
        if (!empty($_POST['user_identification_number']) && (strlen($_POST['user_identification_number']) != 13)) {
            $errors->add('user_identification_number', __('<strong>South African ID Number</strong>:<br>Their South African Identity Number does not seem to be correct.<br>', 'be_popiaCompliant'));
        }
        if (!empty($_POST['other_identification_number']) && (empty($_POST['other_identification_type'])) && (empty($_POST['other_identification_issue']))) {
            $errors->add('other_identification_type', __('<strong>When using a Passport, Social Security or other Identification Number</strong>:<br>Please also provide their <b>Identification Type</b> and </b>Country of Issue</b>.<br>', 'be_popiaCompliant'));
        }
        if (!empty($_POST['other_identification_number']) && (empty($_POST['other_identification_type'])) && (!empty($_POST['other_identification_issue']))) {
            $errors->add('other_identification_type', __('<strong>When using a Passport, Social Security or other Identification Number</strong>:<br>Please also provide the <b>Type of Identification</b> Number they are using.<br>', 'be_popiaCompliant'));
        }
        if (!empty($_POST['other_identification_number']) && (!empty($_POST['other_identification_type'])) && (empty($_POST['other_identification_issue']))) {
            $errors->add('other_identification_issue', __('<strong>When using a Passport, Social Security or other Identification Number</strong>:<br>Please also provide the <b>Country of Issue</b> for the Identification number they are using.<br>', 'be_popiaCompliant'));
        }
        if (!empty($_POST['other_identification_number']) && (strlen($_POST['other_identification_number']) < 7)) {
            $errors->add('other_identification_number', __('<strong>Other Identificatin number</strong>:<br>Please provide a number that we will be able to confirm their Identity with, when providing a fake number, they will never be able to <a href="https://www.manageconsent.co.za" target="blank">Manage Their Consent</a>.<br>', 'be_popiaCompliant'));
        }
        if (!empty($_POST['other_identification_number']) && (strlen($_POST['other_identification_type']) < 4) && (!empty($_POST['other_identification_type']))) {
            $errors->add('other_identification_issue', __('<strong>Other Identificatin Type</strong>:<br>Please write out the name of the <b>Identification Type</b>, do not use the abbreviation.<br>', 'be_popiaCompliant'));
        }
        if (!empty($_POST['other_identification_number']) && (strlen($_POST['other_identification_issue']) < 4) && (!empty($_POST['other_identification_issue']))) {
            $errors->add('other_identification_issue', __('<strong>Country of Issue</strong>:<br>Ensure their <b>Country of Issue</b> is correct and fully written out.<br>', 'be_popiaCompliant'));
        }
    } else {
        // Nonce verification failed, handle accordingly (e.g., display an error message)
        echo '<p style="color: red;">Nonce verification failed. Please try again.</p>';
    }
}


add_action('init', 'checkKeys');
// Check if Keys is set
function checkKeys() {
    global $wpdb;
    $table_name = 'be_popia_compliant_admin';
    $result_keys_provided = wp_cache_get('be_popia_compliant_admin_result_api_4', 'bpc_plugin_cache');
    // If data doesn't exist in the cache, fetch from the database and cache it
    if (false === $result_keys_provided) {
        // @phpcsSuppress WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        /* FALSE-POSITIVE -> PLACEHOLDER IS USED FOR VARIABLE, IF USED FOR TABLE NAME THE CODE BREAKS */ $result_keys_provided = $wpdb->get_var($wpdb->prepare("SELECT value FROM {$wpdb->prefix}{$table_name} WHERE id = %d && id = %d", 1 , 2));
    // Checking for database errors
    if ($wpdb->last_error) {
        error_log('Error fetching data: ' . $wpdb->last_error);
        // Handle the error here (e.g., display an error message)
    }
        wp_cache_set('be_popia_compliant_admin_result_api_4', $result_keys_provided, 'bpc_plugin_cache');
    }
    if ($result_keys_provided !== null) { // Check if a value is found
        update_option('has_active_keys', 1);
    } else {
        update_option('has_active_keys', null); // Use null instead of NULL
    }
}


if (get_option('active_plugins')) {
    $array = get_option('active_plugins');
    if (in_array('woocommerce/woocommerce.php', $array, true)) {
        // WooCommerce start
        //global array only for extra fields
        $be_popiaCompliant_address_fields = array('first_name', 'last_name', 'phone', 'email', 'address_1', 'address_2', 'city', 'state', 'postcode', 'user_SAID', 'user_OtherID', 'user_OIDT', 'user_OIDI', 'SAIDD',);

        // Display a field in WooCommerce Registration / Edit account
        add_action('woocommerce_register_form', 'display_account_registration_field');
        add_action('woocommerce_edit_account_form', 'display_account_registration_field');

        function display_account_registration_field() {

            if (isset( $_POST['be_popia_compliant_registration_nonce'] ) && wp_verify_nonce( $_POST['be_popia_compliant_registration_nonce'], 'be_popia_compliant_registration_nonce')) {
                $identificationNumber = !empty($_POST['user_identification_number']) ? ($_POST['user_identification_number']) : '';
                if(!isset($identificationNumber)) {
                    $identificationNumber = get_user_meta( $user_id, 'user_identification_number', true );
                }
                $otherIdNumber = !empty($_POST['other_identification_number']) ? ($_POST['other_identification_number']) : '';
                if(!isset($otherIdNumber)) {
                    $otherIdNumber = get_user_meta( $user_id, 'other_identification_number', true );
                }
                $otherIdType = !empty($_POST['other_identification_type']) ? ($_POST['other_identification_type']) : '';
                if(!isset($otherIdType)) {
                    $otherIdType = get_user_meta( $user_id, 'other_identification_type', true );
                }
                $otherIdIssue = !empty($_POST['other_identification_issue']) ? ($_POST['other_identification_issue']) : '';
                if(!isset($otherIdIssue)) {
                    $otherIdIssue = get_user_meta( $user_id, 'other_identification_issue', true );
                }
                ?>

                <p>
                <center><span><b>For POPIA Purposes</b><br>
                        <div style='font-size:10px!important'>(Powered by <a href="https://bepopiacompliant.co.za" target="_blank"><span style="color:#B61F20">Be POPIA Compliant</span></a> & <a href="https://manageconsent.co.za" target="_blank"><span style="color:#7a7a7a">Manage Consent</span></a>)</div><br>
                    </span>
                    <div id="saiderror" style="color: red; padding: 5px; display: none; font-size: 14px; line-height: 14px;"></div><br><label for="user_identification_number"><?php esc_html_e('South African Identity Number', 'woocommerce') ?><br>
                        <input type="text" id="user_identification_number" name="user_identification_number" placeholder="8408275002082" value="<?php echo esc_attr($identificationNumber); ?>" class="woocommerce-Input woocommerce-Input--text input-text" />
                    </label>
                
                </center>
                <center><span><b>OR</b><br>(If not South African ID Number)<br></span>
                <p>
                    <label for="other_identification_number"><?php esc_html_e('Passport, Social Security or other Identification Number', 'woocommerce') ?><br>
                        <input type="text" id="other_identification_number" name="other_identification_number" placeholder="if not using SA ID Number" value="<?php echo esc_attr($otherIdNumber); ?>" class="woocommerce-Input woocommerce-Input--text input-text" />
                    </label>
                </p>
                </center>
                <p>
                <center><label for="other_identification_type"><?php esc_html_e('What type of Identification Number is this?', 'woocommerce') ?><br>
                        <input type="text" id="other_identification_type" name="other_identification_type" placeholder="if not using SA ID Number" value="<?php echo esc_attr($otherIdType); ?>" class="woocommerce-Input woocommerce-Input--text input-text" />
                    </label>
                </p>
                </center>
                <p>
                <center><label for="other_identification_issue"><?php esc_html_e('What Country issued this Identification Number?', 'woocommerce') ?><br>
                        <input type="text" id="other_identification_issue" name="other_identification_issue" placeholder="if not using SA ID Number" value="<?php echo esc_attr($otherIdIssue); ?>" class="woocommerce-Input woocommerce-Input--text input-text" />
                    </label>
                    <br><br>
                </center>
                </p><?php
            } else {
                // Nonce verification failed, handle accordingly (e.g., display an error message)
                echo 'Nonce verification failed!';
                return;
            }
        }

        add_filter('woocommerce_form_field', 'be_popiaCompliant_remove_checkout_optional_text', 10, 4);
        // Remove (optional) from non-compulsory fields
        function be_popiaCompliant_remove_checkout_optional_text($field, $key, $args, $value) {
            if (is_checkout() && !is_wc_endpoint_url()) {
                $optional = '&nbsp;<span class="optional">(' . esc_html__('optional', 'woocommerce') . ')</span>';
                $field = str_replace($optional, '', $field);
            }
            return $field;
        }


        add_action('wp_head', 'be_popia_compliant_checkout_style');
        // Add Styling for Checkout Form
        function be_popia_compliant_checkout_style() {
            if (get_option('active_plugins')) {
                $array = get_option('active_plugins');
                if (in_array('woocommerce/woocommerce.php', $array, true)) {
                    echo '<style>input#bpc_hide {display:none;}<style>';
                    if (is_user_logged_in()) {
                        update_option('bpc_logged_in_user', get_current_user_id());
                        echo 'User ID: ' . esc_html(get_current_user_id());
                    } else {
                        update_option('bpc_logged_in_user', NULL);
                        echo "Not Logged In";
                        if (is_checkout() && !is_wc_endpoint_url()) {
                            echo "is Checkout and not logged in";
                        }
                    }

                    global $wpdb;
                    $policy = '<a href="' . esc_url('https://bepopiacompliant.co.za/#/privacy/' . esc_html(servnpr())) . '" target="_blank">privacy policy</a>';
                    $bpc_wc_privacy_policy_checkout = get_option('woocommerce_checkout_privacy_policy_text');
                    echo esc_html($bpc_wc_privacy_policy_checkout); 

                    if (str_contains($bpc_wc_privacy_policy_checkout, '[privacy_policy]')) {
                        // echo "<br>It has the defaults privacy policy set for Checkout<br>";
                        $bpc_wc_privacy_policy_checkout = str_replace("[privacy_policy]",$policy,$bpc_wc_privacy_policy_checkout);
                        // echo $bpc_wc_privacy_policy_checkout;
                        update_option("woocommerce_checkout_privacy_policy_text", $bpc_wc_privacy_policy_checkout);
                    }

                    $bpc_wc_privacy_policy_registration = get_option('woocommerce_registration_privacy_policy_text');
                    echo esc_html($bpc_wc_privacy_policy_registration);

                    if (str_contains($bpc_wc_privacy_policy_registration, '[privacy_policy]')) {
                        // echo "It has the defaults privacy policy set for Registration";
                        $bpc_wc_privacy_policy_registration = str_replace("[privacy_policy]",$policy,$bpc_wc_privacy_policy_registration);
                        // echo $bpc_wc_privacy_policy_registration;
                        update_option("woocommerce_registration_privacy_policy_text", $bpc_wc_privacy_policy_registration);
                    }
                }
            }
        }



        add_filter('woocommerce_checkout_fields', 'bpc_billing_another_group');
        // Create fields for POPIA on checkout
        function bpc_billing_another_group($checkout_fields) {
            $checkout_fields['order']['billing_user_SAID'] = $checkout_fields['billing']['billing_user_SAID'];
            $checkout_fields['order']['billing_user_OtherID'] = $checkout_fields['billing']['billing_user_OtherID'];
            $checkout_fields['order']['billing_user_OIDT'] = $checkout_fields['billing']['billing_user_OIDT'];
            $checkout_fields['order']['billing_user_OIDI'] = $checkout_fields['billing']['billing_user_OIDI'];
            $checkout_fields['order']['billing_SAIDD'] = $checkout_fields['billing']['billing_SAIDD'];

            unset($checkout_fields['billing']['billing_user_SAID']);
            unset($checkout_fields['billing']['billing_user_OtherID']);
            unset($checkout_fields['billing']['billing_user_OIDT']);
            unset($checkout_fields['billing']['billing_user_OIDI']);
            unset($checkout_fields['billing']['billing_SAIDD']);
            unset($checkout_fields['shipping']['shipping_user_SAID']);
            unset($checkout_fields['shipping']['shipping_user_OtherID']);
            unset($checkout_fields['shipping']['shipping_user_OIDT']);
            unset($checkout_fields['shipping']['shipping_user_OIDI']);
            unset($checkout_fields['shipping']['shipping_SAIDD']);
            return $checkout_fields;
        }


            
        add_action('woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1);
        /* Display field value on the order in the backend edit page on order form */
        function my_custom_checkout_field_display_admin_order_meta($order) {
            if ($billing_user_SAID = get_post_meta($order->get_id(), '_billing_user_SAID', true)) {
                echo '<p><strong>' . esc_html__('South African ID #') . ':</strong><br>' . esc_html($billing_user_SAID) . '</p>';
            }
            if ($billing_user_OtherID = get_post_meta($order->get_id(), '_billing_user_OtherID', true)) {
                echo '<p><strong>' . esc_html__('Other Identification #') . ':</strong><br>' . esc_html($billing_user_OtherID) . '</p>';
            }
            if ($billing_user_OIDT = get_post_meta($order->get_id(), '_billing_user_OIDT', true)) {
                echo '<p><strong>' . esc_html__('Identification Type') . ':</strong><br>' . esc_html($billing_user_OIDT) . '</p>';
            }
            if ($billing_user_OIDI = get_post_meta($order->get_id(), '_billing_user_OIDI', true)) {
                echo '<p><strong>' . esc_html__('Country of Issue') . ':</strong><br>' . esc_html($billing_user_OIDI) . '</p>';
            }
        }

                
        add_filter('woocommerce_default_address_fields', 'be_popiaCompliant_override_default_address_fields');
        // checkout registration WooCommerce Field validation
        function be_popiaCompliant_override_default_address_fields($address_fields) {

            $nonce = wp_create_nonce('be_popia_compliant_checkout_nonce');
            // wp_nonce_field('be_popia_compliant_checkout_nonce', 'be_popia_compliant_checkout_nonce');

            $address_fields['user_SAID'] = array(
                'label' => __('<span><b>For POPIA Purposes, we require some sort of identification.</b><br><div style=\'font-size:10px!important\'>(Powered by <a href="https://bepopiacompliant.co.za" target="_blank"><span style="color:#B61F20">Be POPIA Compliant</span></a> & <a href="https://manageconsent.co.za" target="_blank"><span style="color:#7a7a7a">Manage Consent</span></a>)</div></span><div id="billsaiderror" style="color: red; padding: 5px; display: none; font-size: 14px; line-height: 14px;"></div><div class="bpc-group"><br>South African ID Number<br>', 'woocommerce'),
                'placeholder' => '',
                'class' => array('form-row-wide', 'address-field'),
                'type' => 'text',
                'id' => __('billing_user_SAID', 'woocommerce'),
                'tabindex' => __('0', 'woocommerce')
            );

            $address_fields['user_OtherID'] = array(
                'label' => __('</div><b class="center">OR</b><div class="bpc-group"><br>Passport, Social Security or other Identification Number<br>', 'woocommerce'),
                'placeholder' => 'if not using SA ID Number',
                'required' => false,
                'class' => array('form-row-wide', 'address-field'),
                'type' => 'text'
            );

            $address_fields['user_OIDT'] = array(
                'label' => __('Type of Identification Number Used', 'woocommerce'),
                'placeholder' => 'if not using SA ID Number',
                'required' => false,
                'class' => array('form-row-wide', 'address-field'),
                'type' => 'text'
            );

            $address_fields['user_OIDI'] = array(
                'label' => __('Country Of Issue', 'woocommerce'),
                'placeholder' => 'if not using SA ID Number',
                'required' => false,
                'class' => array('form-row-wide', 'address-field'),
                'type' => 'text'
            );

            $address_fields['SAIDD'] = array(
                'label' => __('</div>', 'woocommerce'),
                'id' => __('bpc_hide', 'woocommerce'),
                'class' => array('fake', 'woocommerce')
            );

            return $address_fields;
        }


        add_action('init', 'WooCommerce_functions');
        // Tie BPC Funcions in with WooCommerce
        function WooCommerce_functions() {

            $bpc_logged_in_user = get_option('bpc_logged_in_user');
            if ($bpc_logged_in_user > 0) {
                $bpc_logged_in_user = intval($bpc_logged_in_user);

                // check if consent was provided
                $user_identification_number = get_user_meta($bpc_logged_in_user, 'user_identification_number');
                if ($user_identification_number) {
                    if (intval($user_identification_number) > 0) {
                        $user_identification_number = implode('', $user_identification_number);
                        if (strlen(strval($user_identification_number)) == 13) {
                            $id_verify = '' . $user_identification_number[0] . $user_identification_number[1] . $user_identification_number[2] . $user_identification_number[3] . $user_identification_number[4] . $user_identification_number[5] . '';
                            if (str_contains(strval($id_verify), '000000')) {
                                $userIDis = 0;
                                update_option('userIDis', $userIDis);
                            } else {
                                $userIDis = 1;
                                update_option('userIDis', $userIDis);
                            }
                        } else {
                            $userIDis = 0;
                            update_option('userIDis', $userIDis);
                        }
                    } else {
                        $userIDis = 0;
                        update_option('userIDis', $userIDis);
                    }
                }

                $other_identification_number = get_user_meta($bpc_logged_in_user, 'other_identification_number');
                if ($other_identification_number) {
                    if (intval($other_identification_number) > 0) {
                        $other_identification_number = implode('', $other_identification_number);
                        if (strlen(strval($other_identification_number)) > 6) {
                            $userOtherIDis = 1;
                            update_option('userOtherIDis', $userOtherIDis);
                        }
                    } else {
                        $userOtherIDis = 0;
                        update_option('userOtherIDis', $userOtherIDis);
                    }
                } else {
                    $userOtherIDis = 0;
                    update_option('userOtherIDis', $userOtherIDis);
                }

                $other_identification_type = get_user_meta($bpc_logged_in_user, 'other_identification_type');
                if ($other_identification_type) {
                    if (intval($other_identification_type) > 0) {
                        $other_identification_type = implode('', $other_identification_type);
                        if (strlen(strval($other_identification_type)) > 8) {
                            $userOtherIDtypeIs = 1;
                            update_option('userOtherIDtypeIs', $userOtherIDtypeIs);
                        }
                    } else {
                        $userOtherIDtypeIs = 0;
                        update_option('userOtherIDtypeIs', $userOtherIDtypeIs);
                    }
                } else {
                    $userOtherIDtypeIs = 0;
                    update_option('userOtherIDtypeIs', $userOtherIDtypeIs);
                }

                $other_identification_issue = get_user_meta($bpc_logged_in_user, 'other_identification_issue');
                if ($other_identification_issue) {
                    if (intval($other_identification_issue) > 0) {
                        $other_identification_issue = implode('', $other_identification_issue);
                        if (strlen(strval($other_identification_issue)) > 3) {
                            $userOtherIDIssueIs = 1;
                            update_option('userOtherIDIssueIs', $userOtherIDIssueIs);
                        }
                    } else {
                        $userOtherIDIssueIs = 0;
                        update_option('userOtherIDIssueIs', $userOtherIDIssueIs);
                    }
                } else {
                    $userOtherIDIssueIs = 0;
                    update_option('userOtherIDIssueIs', $userOtherIDIssueIs);
                }

                $billing_user_SAID = get_user_meta($bpc_logged_in_user, 'billing_user_SAID');
                if ($billing_user_SAID) {
                    if (intval($billing_user_SAID) > 0) {
                        $billing_user_SAID = implode('', $billing_user_SAID);
                        if (strlen(strval($billing_user_SAID)) == 13) {
                            $id_verify = '' . $billing_user_SAID[0] . $billing_user_SAID[1] . $billing_user_SAID[2] . $billing_user_SAID[3] . $billing_user_SAID[4] . $billing_user_SAID[5] . '';
                            if (str_contains(strval($id_verify), '000000')) {
                                $billUserIDis = 0;
                                update_option('billUserIDis', $billUserIDis);
                            } else {
                                $billUserIDis = 1;
                                update_option('billUserIDis', $billUserIDis);
                            }
                        } else {
                            $billUserIDis = 0;
                            update_option('billUserIDis', $billUserIDis);
                        }
                    } else {
                        $billUserIDis = 0;
                        update_option('billUserIDis', $billUserIDis);
                    }
                }

                $billing_user_OtherID = get_user_meta($bpc_logged_in_user, 'billing_user_OtherID');
                if ($billing_user_OtherID) {
                    if (intval($billing_user_OtherID) > 0) {
                        $billing_user_OtherID = implode('', $billing_user_OtherID);
                        if (strlen(strval($billing_user_OtherID)) > 6) {
                            $billUserOtherIDis = 1;
                            update_option('billUserOtherIDis', $billUserOtherIDis);
                        }
                    } else {
                        $billUserOtherIDis = 0;
                        update_option('billUserOtherIDis', $billUserOtherIDis);
                    }
                } else {
                    $billUserOtherIDis = 0;
                    update_option('billUserOtherIDis', $billUserOtherIDis);
                }

                $billing_user_OIDT = get_user_meta($bpc_logged_in_user, 'billing_user_OIDT');
                if ($billing_user_OIDT) {
                    if (intval($billing_user_OIDT) > 0) {
                        $billing_user_OIDT = implode('', $billing_user_OIDT);
                        if (strlen(strval($billing_user_OIDT)) > 8) {
                            $billUserOtherIDtypeIs = 1;
                            update_option('billUserOtherIDtypeIs', $billUserOtherIDtypeIs);
                        }
                    } else {
                        $billUserOtherIDtypeIs = 0;
                        update_option('billUserOtherIDtypeIs', $billUserOtherIDtypeIs);
                    }
                } else {
                    $billUserOtherIDtypeIs = 0;
                    update_option('billUserOtherIDtypeIs', $billUserOtherIDtypeIs);
                }

                $billing_user_OIDI = get_user_meta($bpc_logged_in_user, 'billing_user_OIDI');
                if ($billing_user_OIDI) {
                    if (intval($billing_user_OIDI) > 0) {
                        $billing_user_OIDI = implode('', $billing_user_OIDI);
                        if (strlen(strval($billing_user_OIDI)) > 3) {
                            $billUserOtherIDIssueIs = 1;
                            update_option('billUserOtherIDIssueIs', $billUserOtherIDIssueIs);
                        }
                    } else {
                        $billUserOtherIDIssueIs = 0;
                        update_option('billUserOtherIDIssueIs', $billUserOtherIDIssueIs);
                    }
                } else {
                    $billUserOtherIDIssueIs = 0;
                    update_option('billUserOtherIDIssueIs', $billUserOtherIDIssueIs);
                }

                $this_user_output = get_user_meta($bpc_logged_in_user, 'bpc_comms_market_consent');

                if (!isset($this_user_output) || !is_array($this_user_output) || empty($this_user_output)) {
                    $consentProvidedIs = 0;
                } else {
                    $this_user_consent_provided_link = wp_json_encode($this_user_output);
                    $this_user_consent_provided_link = explode(",", $this_user_consent_provided_link);
                    $this_user_consent_provided_link = $this_user_consent_provided_link[1];
                    $this_user_consent_provided_link = str_replace(' ', '', $this_user_consent_provided_link);
                    $this_user_consent_provided_link = str_replace('"', '', $this_user_consent_provided_link);
                    $this_user_consent_provided_link = str_replace('\\', '', $this_user_consent_provided_link);

                    if (strpos($this_user_consent_provided_link, 'redacted') !== false) {
                        $consentProvidedIs = 1;
                        update_user_meta( $bpc_logged_in_user, 'has_provided_consent', 1 );
                    } else {
                        $consentProvidedIs = 0;
                        if(get_user_meta( $bpc_logged_in_user, 'has_provided_consent', true )){
                            delete_user_meta( $bpc_logged_in_user, 'has_provided_consent', $meta_value = 1 );
                        }

                    }
                }

                if ($consentProvidedIs == 1) {
                    $consent_provided = 1;
                    if ($userIDis == 1) {
                        $secondaryID = NULL;
                        $priorityID = get_user_meta($bpc_logged_in_user, 'user_identification_number');
                        $priorityConsent = $this_user_consent_provided_link;
                    } elseif ($billUserIDis == 1) {
                        $secondaryID = NULL;
                        $priorityID = get_user_meta($bpc_logged_in_user, 'billing_user_SAID');
                        $priorityConsent = $this_user_consent_provided_link;
                    } elseif ($userOtherIDis == 1 && $userOtherIDtypeIs == 1 && $userOtherIDIssueIs == 1) {
                        $priorityID = NULL;
                        $secondaryID = get_user_meta($bpc_logged_in_user, 'other_identification_number');
                        $secondaryType = get_user_meta($bpc_logged_in_user, 'other_identification_type');
                        $secondaryIssue = get_user_meta($bpc_logged_in_user, 'other_identification_issue');
                        $priorityConsent = $this_user_consent_provided_link;
                    } elseif ($billUserOtherIDis == 1 && $billUserOtherIDtypeIs == 1 && $billUserOtherIDIssueIs == 1) {
                        $priorityID = NULL;
                        $secondaryID = get_user_meta($bpc_logged_in_user, 'billing_user_OtherID');
                        $secondaryType = get_user_meta($bpc_logged_in_user, 'billing_user_OIDT');
                        $secondaryIssue = get_user_meta($bpc_logged_in_user, 'billing_user_OIDI');
                        $priorityConsent = $this_user_consent_provided_link;
                    }
                } else {
                    $consent_provided = 2;
                    if (isset($userIDis) && $userIDis == 1) {
                        $secondaryID = NULL;
                        $priorityID = get_user_meta($bpc_logged_in_user, 'user_identification_number');
                    } elseif (isset($billUserIDis) && $billUserIDis == 1) {
                        $secondaryID = NULL;
                        $priorityID = get_user_meta($bpc_logged_in_user, 'billing_user_SAID');
                    } elseif(isset($userOtherIDis) && ($userOtherIDis == 1 && $userOtherIDtypeIs == 1 && $userOtherIDIssueIs == 1)) {
                        $priorityID = NULL;
                        $secondaryID = get_user_meta($bpc_logged_in_user, 'other_identification_number');
                        $secondaryType = get_user_meta($bpc_logged_in_user, 'other_identification_type');
                        $secondaryIssue = get_user_meta($bpc_logged_in_user, 'other_identification_issue');
                    } elseif(isset($billUserOtherIDis) && ($billUserOtherIDis == 1 && $billUserOtherIDtypeIs == 1 && $billUserOtherIDIssueIs == 1)) {
                        $priorityID = NULL;
                        $secondaryID = get_user_meta($bpc_logged_in_user, 'billing_user_OtherID');
                        $secondaryType = get_user_meta($bpc_logged_in_user, 'billing_user_OIDT');
                        $secondaryIssue = get_user_meta($bpc_logged_in_user, 'billing_user_OIDI');
                    }
                }

                if($bpc_logged_in_user> 0) {
                    $bpc_logged_in_user = intval($bpc_logged_in_user);
                    
                    if(isset($priorityID)) {
                        $priorityID = wp_json_encode($priorityID);
                        $priorityID = str_replace(' ', '', $priorityID);
                        $priorityID = str_replace('[', '', $priorityID);
                        $priorityID = str_replace(']', '', $priorityID);
                        $priorityID = str_replace('"', '', $priorityID);
                        $priorityID = strval($priorityID);
                    }
                    
                    if(isset($secondaryID)) {
                        $secondaryID = wp_json_encode($secondaryID);
                        $secondaryID = str_replace(' ', '', $secondaryID);
                        $secondaryID = str_replace('[', '', $secondaryID);
                        $secondaryID = str_replace(']', '', $secondaryID);
                        $secondaryID = str_replace('"', '', $secondaryID);
                        $secondaryID = strval($secondaryID);
                    }
                    
                    if(isset($priorityID)) {
                        // Update all fields
                        update_user_meta( $bpc_logged_in_user, 'user_identification_number', $priorityID );
                        update_user_meta( $bpc_logged_in_user, 'billing_user_SAID', $priorityID);
                        
                        update_user_meta( $bpc_logged_in_user, 'other_identification_number', null );
                        update_user_meta( $bpc_logged_in_user, 'billing_user_OtherID', null);

                        update_user_meta( $bpc_logged_in_user, 'other_identification_type', null );
                        update_user_meta( $bpc_logged_in_user, 'billing_user_OIDT', null);

                        update_user_meta( $bpc_logged_in_user, 'other_identification_issue', null );
                        update_user_meta( $bpc_logged_in_user, 'billing_user_OIDI', null);

                    } elseif(isset($secondaryID)) {
                        
                        update_user_meta( $bpc_logged_in_user, 'user_identification_number', null );
                        update_user_meta( $bpc_logged_in_user, 'billing_user_SAID', null );

                        update_user_meta( $bpc_logged_in_user, 'other_identification_number', $secondaryID);
                        update_user_meta( $bpc_logged_in_user, 'billing_user_OtherID', $secondaryID);
                        

                        update_user_meta( $bpc_logged_in_user, 'other_identification_type', $secondaryType);
                        update_user_meta( $bpc_logged_in_user, 'billing_user_OIDT', $secondaryType);

                        update_user_meta( $bpc_logged_in_user, 'other_identification_issue', $secondaryIssue );
                        update_user_meta( $bpc_logged_in_user, 'billing_user_OIDI', $secondaryIssue);
                    }
                }

                if(isset($priorityID)) unset($priorityID);
                if(isset($secondaryID)) unset($secondaryID);
                if(isset($secondaryType)) unset($secondaryType);
                if(isset($secondaryIssue)) unset($secondaryIssue);
                if(isset($userOtherIDis)) unset($userOtherIDis);
                if(isset($userOtherIDtypeIs)) unset($userOtherIDtypeIs);
                if(isset($userOtherIDIssueIs)) unset($userOtherIDIssueIs);
                if(isset($billUserOtherIDis)) unset($billUserOtherIDis);
                if(isset($billUserOtherIDtypeIs)) unset($billUserOtherIDtypeIs);
                if(isset($billUserOtherIDIssueIs)) unset($billUserOtherIDIssueIs);
            }           

            add_action('woocommerce_checkout_process', 'be_popiaCompliant_check_if_selected');
            // Add BPC to WooCommerce Checkout
            function be_popiaCompliant_check_if_selected() {  
                    if (empty($_POST['billing_user_SAID']) && empty($_POST['billing_user_OtherID'])) {
                            wc_add_notice('<strong>(Without an authentication identifier, you will never be able to <a href="https://www.manageconsent.co.za" target="blank">Manage Your Consent</a></strong>:<br>Please enter your South African ID Number (if South African) <br>OR<br>Passport, Social Security or other Identification Number (if not using South African ID Number). If you opt for this otion we will also need to know what type of identication was used, and what country issued the document.<br>', 'error');
                    }

                    if (!empty($_POST['billing_user_SAID']) && !empty($_POST['billing_user_OtherID'])) {
                        wc_add_notice('<strong>Provide only one (1) Identification Number</strong>:<br>If you are a South African Citizen, please only enter your South African Identification Number.<br><br>If you are a foreign citizen, please leave "South African Identity Number" blank and provide:<br> - Your Local Identification number or Passport number.<br>- The type of Identification number you are using.<br>- The country that issued the Identification number.<br><br>', 'error');
                    }

                    if (!empty($_POST['billing_user_SAID']) && (strlen($_POST['billing_user_SAID']) != 13)) {
                        wc_add_notice('<strong>South African ID Number</strong>:<br>Your South African Identity Number does not seem to be correct.<br>', 'error');
                    }

                    if (!empty($_POST['billing_user_OtherID']) && (empty($_POST['billing_user_OIDT'])) && (empty($_POST['billing_user_OIDI']))) {
                        wc_add_notice('<strong>When using Passport, Social Security or other Identification Number</strong>:<br>Please also provide your Identification Type and Country of Issue.<br>', 'error');
                    }

                    if (!empty($_POST['billing_user_OtherID']) && (empty($_POST['billing_user_OIDT'])) && (!empty($_POST['billing_user_OIDI']))) {
                        wc_add_notice('<strong>When using Passport, Social Security or other Identification Number</strong>:<br>Please also provide the Type of Identification Number you are using.<br>', 'error');
                    }

                    if (!empty($_POST['billing_user_OtherID']) && (!empty($_POST['billing_user_OIDT'])) && (empty($_POST['billing_user_OIDI']))) {
                        wc_add_notice('<strong>When using Passport, Social Security or other Identification Number</strong>:<br>Please also provide the Country of Issue for the Identification number you are using.<br>', 'error');
                    }

                    if (!empty($_POST['billing_user_OtherID']) && (strlen($_POST['billing_user_OtherID']) < 7)) {
                        wc_add_notice('<strong>Other Identificatin number</strong>:<br>Please provide a number that we will be able to confirm your Identity with, when providing a fake number, you will never be able to <a href="https://www.manageconsent.co.za" target="blank">Manage Your Consent</a>.<br>', 'error');
                    }

                    if (!empty($_POST['billing_user_OtherID']) && (strlen($_POST['billing_user_OIDT']) < 4) && (!empty($_POST['billing_user_OIDT']))) {
                        wc_add_notice('<strong>Other Identificatin Type</strong>:<br>Please write out the name of the Identification Type, do not use the abbreviation.<br>', 'error');
                    }

                    if (!empty($_POST['billing_user_OtherID']) && (strlen($_POST['billing_user_OIDI']) < 4) && (!empty($_POST['billing_user_OIDI']))) {
                        wc_add_notice('<strong>Country of Issue</strong>:<br>Ensure your Country of Issue is correct and fully written out.<br>', 'error');
                    }
                return $errors;
            }


            add_filter('woocommerce_registration_errors', 'account_registration_field_validation', 10, 3);
            // registration WooCommerce Field validation
            function account_registration_field_validation($errors, $username, $email) {
                if (empty($_POST['user_identification_number']) && empty($_POST['other_identification_number']) && empty($_POST['billing_user_SAID']) && empty($_POST['billing_user_OIDI']) ) {
                    $errors->add('user_identification_number', __('<strong>We require some form of Identificatin for POPIA (Without an authentication identifier, you will never be able to <a href="https://www.manageconsent.co.za" target="blank">Manage Your Consent</a></strong>:<br><br>Please enter your South African ID Number (if South African) <br><br>OR<br><br>Passport, Social Security or other Identification Number (if not using South African ID Number). If you opt for this otion we will also need to know what type of identication was used, and what country issued the document.<br><br>', 'woocommerce'));
                }

                if (!empty($_POST['user_identification_number']) && !empty($_POST['other_identification_number'])) {
                    $errors->add('user_identification_number', __('<strong>Provide only one (1) Identification Number</strong>:<br>If you are a South African Citizen, please only enter your South African Identification Number.<br><br>If you are a foreign citizen, please leave "South African Identity Number" blank and provide:<br> - Your Local Identification number or Passport number.<br>- The type of Identification number you are using.<br>- The country that issued the Identification number.<br><br>', 'woocommerce'));
                }

                if (!empty($_POST['user_identification_number']) && (strlen($_POST['user_identification_number']) != 13)) {
                    $errors->add('user_identification_number', __('<strong>South African ID Number</strong>:<br>Your South African Identity Number does not seem to be correct.<br>', 'woocommerce'));
                }

                if (!empty($_POST['other_identification_number']) && (empty($_POST['other_identification_type'])) && (empty($_POST['other_identification_issue']))) {
                    $errors->add('other_identification_type', __('<strong>When using Passport, Social Security or other Identification Number</strong>:<br>Please also provide your Identification Type and Country of Issue.<br>', 'woocommerce'));
                }

                if (!empty($_POST['other_identification_number']) && (empty($_POST['other_identification_type'])) && (!empty($_POST['other_identification_issue']))) {
                    $errors->add('other_identification_type', __('<strong>When using Passport, Social Security or other Identification Number</strong>:<br>Please also provide the Type of Identification Number you are using.<br>', 'woocommerce'));
                }

                if (!empty($_POST['other_identification_number']) && (!empty($_POST['other_identification_type'])) && (empty($_POST['other_identification_issue']))) {
                    $errors->add('other_identification_issue', __('<strong>When using Passport, Social Security or other Identification Number</strong>:<br>Please also provide the Country of Issue for the Identification number you are using.<br>', 'woocommerce'));
                }

                if (!empty($_POST['other_identification_number']) && (strlen($_POST['other_identification_number']) < 7)) {
                    $errors->add('other_identification_number', __('<strong>Other Identificatin number</strong>:<br>Please provide a number that we will be able to confirm your Identity with, when providing a fake number, you will never be able to <a href="https://www.manageconsent.co.za" target="blank">Manage Your Consent</a>.<br>', 'woocommerce'));
                }

                if (!empty($_POST['other_identification_number']) && (strlen($_POST['other_identification_type']) < 4) && (!empty($_POST['other_identification_type']))) {
                    $errors->add('other_identification_issue', __('<strong>Other Identificatin Type</strong>:<br>Please write out the name of the Identification Type, do not use the abbreviation.<br>', 'woocommerce'));
                }

                if (!empty($_POST['other_identification_number']) && (strlen($_POST['other_identification_issue']) < 4) && (!empty($_POST['other_identification_issue']))) {
                    $errors->add('other_identification_issue', __('<strong>Country of Issue</strong>:<br>Ensure your Country of Issue is correct and fully written out.<br>', 'woocommerce'));
                }
                return $errors;
            }



            add_action('woocommerce_created_customer', 'account_registration_field_save');

            function account_registration_field_save($customer_id) {
                if (!empty($_POST['user_identification_number'])) {
                    if (strlen($_POST['user_identification_number']) == 13) {
                        update_user_meta($customer_id, 'user_identification_number', $_POST['user_identification_number']);
                    }
                }
                if (!empty($_POST['other_identification_number'])) {
                    update_user_meta($customer_id, 'other_identification_number', $_POST['other_identification_number']);
                }
                if (!empty($_POST['other_identification_type'])) {
                    update_user_meta($customer_id, 'other_identification_type', $_POST['other_identification_type']);
                }
                if (!empty($_POST['other_identification_issue'])) {
                    update_user_meta($customer_id, 'other_identification_issue', $_POST['other_identification_issue']);
                }
            }
        }
    }
}
