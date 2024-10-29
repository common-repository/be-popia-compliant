<?php

function servdp() { 
    $serverName = $_SERVER['SERVER_NAME'];
    $serverName = trim($serverName);
    return $serverName;
}

// Check if daily cron is active and unschedule it
if (wp_next_scheduled('be_popia_compliant_daily_event')) {
    wp_clear_scheduled_hook('be_popia_compliant_daily_event');
}

// Define hourly cron schedule
add_filter('cron_schedules', 'be_popia_compliant_add_hourly');
function be_popia_compliant_add_hourly($schedules) {
    $schedules['hourly'] = array(
        'interval'  => 3600, // in seconds, 60 * 60 = 3600
        'display'   => __('Once Hourly', 'be_popia_compliant')
    );
    return $schedules;
}

// Schedule an action if it's not already scheduled
if (!wp_next_scheduled('be_popia_compliant_hourly_event')) {
    wp_schedule_event(time(), 'hourly', 'be_popia_compliant_hourly_event');
}

add_action('be_popia_compliant_add_hourly', 'bpc_popia_data_processing');
// Hook into that action that'll fire every hour
function bpc_popia_data_processing() {
    $t = time();
    update_option('cron_last_fired_at', $t);
    
    // Function that will get the domain refference if not set.
    $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/getconsentchangesexternally/" . esc_html(servdp()));
    $args = array('headers' => array('Content-Type' => 'application/json',), 'body' => array(),);
    $response = wp_remote_get(wp_http_validate_url($url), $args);
    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if (200 === $response_code) {
        $body = json_decode($body);
        foreach ($body as $data) {
            $consent_to_update = $data->consentsChanged;
            $data_request = $data->data_request;
            $data_request_approved = $data->data_request_approved;
            $data_deletion = $data->data_deletion;
            $data_deletion_approved = $data->data_deletion_approved;
            $id = $data->id;
            if (isset($id)) {
                update_option('bpc_refference', $id);
            }
            if (isset($consent_to_update)) {
                if (strpos($consent_to_update, ',') !== false) {
                    $consent_to_update = explode(",", $consent_to_update);
                    update_option('bpc_consent_to_update', $consent_to_update);
                } else {
                    $consent_to_update = [$consent_to_update];
                    update_option('bpc_consent_to_update', $consent_to_update);
                }
            }
            if (isset($data_request)) {
                if (strpos($data_request, ',') !== false) {
                    $data_request = explode(",", $data_request);
                    update_option('bpc_data_request', $data_request);
                } else {
                    $data_request = [$data_request];
                    update_option('bpc_data_request', $data_request);
                }
            }
            if (isset($data_request_approved)) {
                if (strpos($data_request_approved, ',') !== false) {
                    $data_request_approved = explode(",", $data_request_approved);
                    update_option('bpc_data_request_approved', $data_request_approved);
                } else {
                    $data_request_approved = [$data_request_approved];
                    update_option('bpc_data_request_approved', $data_request_approved);
                }
            }
            if (isset($data_deletion)) {
                if (strpos($data_deletion, ',') !== false) {
                    $data_deletion = explode(",", $data_deletion);
                    update_option('bpc_data_deletion', $data_deletion);
                } else {
                    $data_deletion = [$data_deletion];
                    update_option('bpc_data_deletion', $data_deletion);
                }
            }
            if (isset($data_deletion_approved)) {
                if (strpos($data_deletion_approved, ',') !== false) {
                    $data_deletion_approved = explode(",", $data_deletion_approved);
                    update_option('bpc_data_deletion_approved', $data_deletion_approved);
                } else {
                    $data_deletion_approved = [$data_deletion_approved];
                    update_option('bpc_data_deletion_approved', $data_deletion_approved);
                }
            }
        }
    }

    // Ping url to ensure plugin is active
    $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/pingwordpressplugin/" . $id . "/");
    $t = time();
    $bpcV = get_option('bpc_v');
    $vType = 1;
    update_option('bpc_hasPro', $vType);
    $PIV = $bpcV . ' PRO';

    include_once(ABSPATH . '/wp-admin/includes/plugin.php');
    $all_plugins = get_plugins();
    // Get active plugins
    $active_plugins = get_option('active_plugins');
    $pi_count = 0;
    $active_count = 0;
    $domain_plugin_names = '';
    $this_count = 0;
    foreach ($all_plugins as $key => $value) {
        $pi_count++;
        $is_active = (in_array($key, $active_plugins)) ? true : false;

        if ($is_active) ++$active_count;
        $domain_plugins[$key] = array('name' => $value['Name'], 'version' => $value['Version'], 'description' => $value['Description'], 'active'  => $is_active,);
    }
    foreach ($all_plugins as $key => $value) {
        $is_active = (in_array($key, $active_plugins)) ? true : false;
        if ($is_active) {
            ++$this_count;
            $domain_plugin_name = $value['Name'];
            if ($active_count > $this_count) {
                $domain_plugin_name = $domain_plugin_name . ', ';
            } else {
                $domain_plugin_name = $domain_plugin_name . '.';
            }
            $domain_plugin_names = $domain_plugin_names . $domain_plugin_name;
        }
    }
    $PIC = '' . $active_count . '/' . $pi_count . '';
    update_option('testIDs', $PIC);
    $PLIA = '[N]' . $domain_plugin_names . '[/N] [D]' . wp_json_encode($domain_plugins) . '[/D]';
    if (get_option('main_bpc')) {
        $main_bpc = get_option('main_bpc');
    } else {
        $main_bpc = '';
    }
    $body = array('last_pinged' => $t, 'PIV' => $PIV, 'PIC' => $PIC, 'domain_plugins' => $PLIA, 'main_bpc' => $main_bpc,);

    $args = array('headers' => array('Content-Type'   => 'application/json',), 'body' => wp_json_encode($body), 'method' => 'PATCH');
    $result = wp_remote_request(wp_http_validate_url($url), $args);
    if (get_option('bpc_hasPro') == 1) {
        /* Data Consent Processing Starts*/
        $ids = get_option('bpc_consent_to_update');
        if (isset($ids) && $ids != '') {
            foreach ($ids as $id) {
                $id = str_replace(' ', '', $id);
                $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/getconsentchanges/" . $id . "/");
                $args = array('headers' => array('Content-Type' => 'application/json',), 'body' => array(),);
                $response = wp_remote_get(wp_http_validate_url($url), $args);
                $response_code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                if (200 === $response_code) {
                    $body = json_decode($body);
                    $id = $body->consent_user_id;
                    $date = $body->timestamp;
                    $consent_url = $body->consent_url;
                    $c_phone = $body->c_phone;
                    $c_sms = $body->c_sms;
                    $c_whatsapp = $body->c_whatsapp;
                    $c_messenger = $body->c_messenger;
                    $c_telegram = $body->c_telegram;
                    $c_email = $body->c_email;
                    $c_custom1 = $body->c_custom1;
                    $c_custom2 = $body->c_custom2;
                    $c_custom3 = $body->c_custom3;
                    $m_phone = $body->m_phone;
                    $m_sms = $body->m_sms;
                    $m_whatsapp = $body->m_whatsapp;
                    $m_messenger = $body->m_messenger;
                    $m_telegram = $body->m_telegram;
                    $m_email = $body->m_email;
                    $m_custom1 = $body->m_custom1;
                    $m_custom2 = $body->m_custom2;
                    $m_custom3 = $body->m_custom3;
                    $value = array($date, $consent_url, $c_phone, $c_sms, $c_whatsapp, $c_messenger, $c_telegram, $c_email, $c_custom1, $c_custom2, $c_custom3, $m_phone, $m_sms, $m_whatsapp, $m_messenger, $m_telegram, $m_email, $m_custom1, $m_custom2, $m_custom3);
                    update_user_meta($id, 'bpc_comms_market_consent', $value);
                }
            }
            // Remove from changes on BPC
            $removeId = get_option('bpc_refference');
            $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/updateconsentchangedarray/" . $removeId . "/");
            $body = array('consentsChanged' => null);
            $args = array('headers' => array('Content-Type' => 'application/json', ),'body' => wp_json_encode($body), 'method' => 'PUT');
            $result =  wp_remote_request(wp_http_validate_url($url), $args);
            update_option('bpc_consent_to_update', null);
        }
        /*  Data Consent Processing Ends */
        /* --------------------------------------------------------------------------------------------------------------------------*/
        /* Data Request Processing Approved Starts */
        $ids = get_option('bpc_data_request_approved');
        if (isset($ids) && $ids != '') {
            foreach ($ids as $id) {
                $tb_count = 0;
                $data_to_send = '[domain_id]' . get_option('bpc_refference') . '[/domain_id][user_id]' . $id . '[/user_id]';
                $id = str_replace(' ', '', $id);
                if (get_userdata($id) !== null) {
                    $tb_name = $wpdb->base_prefix . 'users';
                    $r_count = 0;
                    $tb_count++;
                    $data_to_send = '[t' . $tb_count . '][tn]' . $tb_name . '[/tn][dbs]1[/dbs][pk]ID[/pk]';
                    $data = get_userdata($id);
                    $ID = $data->ID;
                    //Get Personal Data from Basic WordPress Data via wp_users
                    if ($ID != '') {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']ID,' . $ID . ',n,s[/r' . $r_count . ']';}
                    $user_login = $data->user_login;
                    if ($user_login != '') {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']user_login,' . $user_login . ',n,s[/r' . $r_count . ']';}
                    $user_nicename = $data->user_nicename;
                    if ($user_nicename != '') {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']user_nicename,' . $user_nicename . ',y,s[/r' . $r_count . ']';}
                    $user_email = $data->user_email;
                    if ($user_email != '') {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']user_email,' . $user_email . ',y,s[/r' . $r_count . ']';}
                    $user_url = $data->user_url;
                    if ($user_url != '') {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']user_url,' . $user_url . ',n,s[/r' . $r_count . ']';}
                    $user_registered = $data->user_registered;
                    if ($user_registered != '') {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']user_registered,' . $user_registered . ',n,[/r' . $r_count . ']';}
                    $user_activation_key = $data->user_activation_key;
                    if ($user_activation_key != '') {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']user_activation_key,' . $user_activation_key . ',n,s[/r' . $r_count . ']';}
                    $user_status = $data->user_status;
                    if ($user_status != '') {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']user_status,' . $user_status . ',n,s[/r' . $r_count . ']';}
                    $display_name = $data->display_name;
                    if ($display_name != '') {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']display_name,' . $display_name . ',y,s[/r' . $r_count . ']';}
                    $data_to_send = $data_to_send . '[rc]' . $r_count . '[/rc]';
                    $data_to_send = $data_to_send . '[/t' . $tb_count . ']';
                }
                if (get_user_meta($id) !== null) {
                    //Get Personal Data from Basic WordPress Data via wp_usermeta -> non array values
                    $tb_name = $wpdb->base_prefix . 'usermeta';
                    $mk_count = 0;
                    $tb_count++;
                    $data_to_send = $data_to_send . '[t' . $tb_count . '][tn]' . $tb_name . '[/tn][dbs]2[/dbs]';
                    $data = get_user_meta($id);
                    foreach ($data as $key => $value) {
                        if ($value != [""] && $value != ["true"] && $value != ["false"] && $value != ["0"] && $value != ["1"]) {
                            if (!str_contains(wp_json_encode($value), '{') || !str_contains(wp_json_encode($value), '}')) {
                                $value = wp_json_encode($value);
                                $value = str_replace('["', '', $value);
                                $value = str_replace('"]', '', $value);
                                $mk_count++;
                                $data_to_send = $data_to_send . '[mkv' . $mk_count . ']' . $key . ',' . $value . '[/mkv' . $mk_count . ']';
                            }
                        }
                    }
                    $data_to_send = $data_to_send . '[mkc]' . $mk_count . '[/mkc]';
                    $data_to_send = $data_to_send . '[/t' . $tb_count . ']';
                }

                if (get_user_meta($id) !== null) {
                    //Get Personal Data from Basic WordPress Data via wp_usermeta -> bpc_comms_market_consent
                    $tb_name = $wpdb->base_prefix . 'bpc_comms_market_consent';
                    $bpc_count = 0;
                    $tb_count++;
                    $data_to_send = $data_to_send . '[t' . $tb_count . '][tn]' . $tb_name . '[/tn][dbs]3[/dbs]';
                    $data = get_user_meta($id, $tb_name);
                    foreach ($data as $key => $value) {
                        $date_time_format = 'j M, Y H:i';
                        $date_format = 'j M, Y';
                        $time_format = 'H:i';
                        if ($value != [""] && $value != ["true"] && $value != ["false"] && $value != ["0"] && $value != ["1"]) {
                            if (gmdate($time_format, intval($value[0])) == "00:00") {
                                $friendly_date = gmdate($date_format, intval($value[0]));
                                $bpc_count++; // This was not included pre DSTV CCA Support
                            } else {$friendly_date = gmdate($date_time_format, intval($value[0])); $bpc_count++;}
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 0 . ',timestamp,Consent Provided Date,' . $value[0] . ',' . $friendly_date . '[/bpc' . $bpc_count . ']';
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 1 . ',consent_link,Signed Consent Form,' . $value[1] . ',' . $value[1] . '[/bpc' . $bpc_count . ']';
                            if ($value[2] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 2 . ',cs,Contractual Communication Consent via Phone,' . $value[2] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[3] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 3 . ',cs,Contractual Communication Consent via SMS,' . $value[3] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[4] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 4 . ',cs,Contractual Communication Consent via WhatsApp,' . $value[4] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[5] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 5 . ',cs,Contractual Communication Consent via Messenger,' . $value[5] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[6] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 6 . ',cs,Contractual Communication Consent via Telegram,' . $value[6] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[7] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 7 . ',cs,Contractual Communication Consent via email,' . $value[7] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[8] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 8 . ',cs,Contractual Communication Consent via Custom Type 1,' . $value[8] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[9] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 9 . ',cs,Contractual Communication Consent via Custom Type 2,' . $value[9] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[10] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 10 . ',cs,Contractual Communication Consent via Custom Type 3,' . $value[10] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[11] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 11 . ',cs,Marketing Communication Consent via Phone,' . $value[11] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[12] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 12 . ',cs,Marketing Communication Consent via SMS,' . $value[12] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[13] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 13 . ',cs,Marketing Communication Consent via WhatsApp,' . $value[13] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[14] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 14 . ',cs,Marketing Communication Consent via Messenger,' . $value[14] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[15] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 15 . ',cs,Marketing Communication Consent via Telegram,' . $value[15] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[16] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 16 . ',cs,Marketing Communication Consent via email,' . $value[16] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[17] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 17 . ',cs,Marketing Communication Consent via Custom Type 1,' . $value[17] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[18] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 18 . ',cs,Marketing Communication Consent via Custom Type 2,' . $value[18] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                            if ($value[19] == 1) {$sv = "Yes";} else {$sv = "No";}
                            $bpc_count++;
                            $data_to_send = $data_to_send . '[bpc' . $bpc_count . ']' . 19 . ',cs,Marketing Communication Consent via Custom Type 3,' . $value[19] . ',' . $sv . ',[/bpc' . $bpc_count . ']';
                        }
                    }
                    $data_to_send = $data_to_send . '[bpc]' . $bpc_count . '[/bpc]';
                    $data_to_send = $data_to_send . '[/t' . $tb_count . ']';
                }

                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.DirectQuery
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                global $wpdb;
                $table_name = 'arf_payfast_order';
                $prefix_table_name = $wpdb->prefix . $table_name;
                // Define the prepared SQL statement with a placeholder
                $query = $wpdb->prepare("SHOW TABLES LIKE %s", '%' . $wpdb->esc_like($prefix_table_name) . '%');
                // Retrieving the table names matching the pattern
                // @phpcsSuppress WordPress.DB.PreparedSQL.NotPrepared
                /*FALSE-POSITIVE -> USED PLACEHOLDERS AND WAS ALREADY PREPARED IN PREVIOUS LINE*/ $tables = $wpdb->get_results($query, ARRAY_N);
                // Extracting the first table name from the results
                $first_table = isset($tables[0][0]) ? $tables[0][0] : '';

                if ($first_table === $prefix_table_name) {
                    // Disable caching for this query intentionally
                    $table_name = 'arf_payfast_order';
                    $wpdb->show_errors();
                    // @phpcsSuppress WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    /* FALSE-POSITIVE -> PLACEHOLDER IS USED FOR VARIABLE, IF USED FOR TABLE NAME THE CODE BREAKS */ $prepared_query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$table_name} WHERE `payer_email` = %s", $user_email); 
         
                    //Get Personal Data from arf_payfast_order multiple rows possible
                    update_option('multi_result', $result);
                    if (count($result) > 0) {
                        foreach($result as $theResult) {
                            $tb_count++;
                            $tb_name = $wpdb->base_prefix . 'arf_payfast_order';
                            $data_to_send = $data_to_send . '[t' . $tb_count . '][tn]' . $tb_name . '[/tn][dbs]1[/dbs][pk]id[/pk]';
                            $r_count = 0;
                            $id = $theResult->id;
                            if(isset($id)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']id,' . $id . ',n,i,11[/r' . $r_count . ']';}
                            $item_name = $theResult->item_name;
                            if(isset($item_name)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']item_name,' . $item_name . ',n,v,255[/r' . $r_count . ']';}
                            $txn_id = $theResult->txn_id;
                            if(isset($txn_id)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']txn_id,' . $txn_id . ',n,v,255[/r' . $r_count . ']';}
                            $payment_status = $theResult->payment_status;
                            if(isset($payment_status)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']payment_status,' . $payment_status . ',n,v,225[/r' . $r_count . ']';}
                            $mc_gross = $theResult->mc_gross;
                            if(isset($mc_gross)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']mc_gross,' . $mc_gross . ',n,f,11.2[/r' . $r_count . ']';}
                            $mc_currency = $theResult->mc_currency;
                            if(isset($mc_currency)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']mc_currency,' . $mc_currency . ',n,v,255[/r' . $r_count . ']';}
                            $quantity = $theResult->quantity;
                            if($quantity != '') {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']quantity,' . $quantity . ',n,v,255[/r' . $r_count . ']';}
                            $payer_email = $theResult->payer_email;
                            if(isset($payer_email)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']payer_email,' . $payer_email . ',y,v,255[/r' . $r_count . ']';}
                            $payer_name = $theResult->payer_name;
                            if(isset($payer_name)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']payer_name,' . $payer_name . ',y,v,255[/r' . $r_count . ']';}
                            $payment_type = $theResult->payment_type;
                            if($payment_type != 0) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']payment_type,' . $payment_type . ',n,v,255[/r' . $r_count . ']';}
                            $user_id = $theResult->user_id;
                            if($user_id != 0) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']user_id,' . $user_id . ',n,i,11[/r' . $r_count . ']';}
                            $entry_id = $theResult->entry_id;
                            if(isset($entry_id)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']entry_id,' . $entry_id . ',n,i,11[/r' . $r_count . ']';}
                            $form_id = $theResult->form_id;
                            if(isset($form_id)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']form_id,' . $form_id . ',n,i,11[/r' . $r_count . ']';}
                            $payment_date = $theResult->payment_date;
                            if(isset($payment_date)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']payment_date,' . $payment_date . ',n,v,255[/r' . $r_count . ']';}
                            $created_at = $theResult->created_at;
                            if(isset($created_at)) { $r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']created_at,' . $created_at . ',n,D[/r' . $r_count . ']';}
                            $is_verified = $theResult->is_verified;
                            if(isset($is_verified)) {$r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']is_verified,' . $is_verified . ',n,i,1[/r' . $r_count . ']';}
                            $token = $theResult->token;
                            if($token != '') { $r_count++; $data_to_send = $data_to_send . '[r' . $r_count . ']token,' . $token . ',n,v,255[/r' . $r_count . ']';}
                            $data_to_send = $data_to_send . '[rc]' . $r_count . '[/rc]';
                            $data_to_send = $data_to_send . '[/t' . $tb_count . ']';
                        }
                    }
                }
                
                $bpc_ref = get_option('bpc_refference');
                $bpc_company_ref = get_option('this_domain_identity');
                $data_to_send = $data_to_send . '[tc]' . $tb_count . '[/tc][user_email]' . $user_email . '[/user_email][cid]' . $bpc_company_ref . '[/cid][did]' . $bpc_ref . '[/did]';
                $date_time_format = 'j M, Y H:i';
                $friendly_date = gmdate($date_time_format, time());
                //  Now use the opportunity to post this collected data to the BPC Portal for further processing, then repeat for next person if applicable
                $url  = wp_http_validate_url('https://py.bepopiacompliant.co.za/api/datarequestdisplay/');
                $body = array('data' => $data_to_send, 'email' => $user_email, 'domain_id' => $bpc_company_ref, 'time' => $friendly_date, 'name' => $display_name,);
                $args = array('method' => 'POST', 'timeout' => 45, 'sslverify' => false, 'headers' => array('Content-Type' => 'application/json',),'body' => wp_json_encode($body),);
                $request = wp_remote_post(wp_http_validate_url($url), $args);

                if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {error_log(print_r($request, true));}
            }
            // Remove from changes on BPC
            $removeId = get_option('bpc_refference');
            $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/updateconsentchangedarray/" . $removeId . "/");
            $body = array('data_request_approved' => null);
            $args = array('headers' => array('Content-Type'   => 'application/json',), 'body' => wp_json_encode($body), 'method' => 'PUT');
            $result =  wp_remote_request(wp_http_validate_url($url), $args);
            update_option('bpc_data_request_approved', null);
        }
        /* Data Request Processing Approved Ends */
        /* --------------------------------------------------------------------------------------------------------------------------*/
        /* Data Request Processing Requests Starts*/
        $ids = get_option('bpc_data_request');
        if (isset($ids) && $ids != '') {
            $data_to_send = ''; foreach ($ids as $id) {$id = str_replace(' ', '', $id); $data = get_userdata($id); $user_email = $data->user_email; $consent_domain_id = get_option('this_domain_identity'); $data_to_send = $data_to_send . $id . ',' . $consent_domain_id . ',' . $user_email . ',';}
            $data_to_send = $data_to_send . ']';
            $data_to_send = str_replace(',]', '', $data_to_send);
            update_option('data_to_send', $data_to_send);
            $bpc_ref = get_option('bpc_refference');
            //  Now use the opportunity to post this collected data to the BPC Portal for further processing, then repeat for next person if applicable
            $url  = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/datarequestwp/" . $bpc_ref . "/");
            $body = array('data' => $data_to_send);
            $args = array('method' => 'PUT', 'timeout' => 45, 'sslverify' => false, 'headers' => array('Content-Type' => 'application/json',), 'body' => wp_json_encode($body),);
            $request = wp_remote_post(wp_http_validate_url($url), $args);
            if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {error_log(print_r($request, true));}
            // Remove from changes on BPC
            $removeId = get_option('bpc_refference');
            $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/updateconsentchangedarray/" . $removeId . "/");
            $body = array('data_request' => null);
            $args = array('headers' => array('Content-Type'   => 'application/json',), 'body' => wp_json_encode($body), 'method' => 'PUT');
            $result =  wp_remote_request(wp_http_validate_url($url), $args);
            update_option('bpc_data_request', null);
        } /* Data Request Processing Ends  */
    }

    /*Data Deletion Processing Approved Starts */
    $ids = get_option('bpc_data_deletion_approved');
    if (isset($ids) && $ids != '') {
        $ids = str_replace(' ', '', $ids);
        foreach ($ids as $id) {
            $data = get_userdata($id);
            $this_user_email = $data->user_email;
            $consent_domain_id = get_option('this_domain_identity');
            $this_user_name = $data->display_name;
            $bpc_ref = get_option('bpc_refference');
            $redacted = new WP_User_Query(array('search' => '*' . esc_attr('@redacted.') . '*', 'search_columns' => array('user_email'),));
            $users_found = $redacted->get_results();
            $red_count = 0;
            if ($users_found != []) {foreach ($users_found as $data) {$red_count++; $email = $data->user_email;}}
            $red_count++;
            $time_now = time();
            $red_name = 'redacted' . $red_count;
            $red_email = $time_now . '@redacted.' . $red_count . '.' . esc_html(servdp());
            $red_link = 'https://redacted.bepopiacompliant.co.za/redacted.php?count=' . $red_count . '&time=' . $time_now . '&domain=' . esc_html(servdp());
            $red_ID = substr('0000000000000' . $red_count, -13);
            $red_IP = '000.000.000.000';
            // Single occurance only
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.DirectQuery
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
            global $wpdb;
            $wpdb->update($wpdb->users, ['user_login' => $red_name], ['ID' => $id]);
            $user_data = wp_update_user(array('ID' => $id, 'user_nicename' => $red_name));
            $user_data = wp_update_user(array('ID' => $id, 'user_email' => $red_email));
            $user_data = wp_update_user(array('ID' => $id, 'user_url' => $red_link));
            $user_data = wp_update_user(array('ID' => $id, 'display_name' => $red_name));
            update_user_meta($id, 'nickname', $red_name);
            update_user_meta($id, 'first_name', $red_name);
            update_user_meta($id, 'last_name', $red_name);
            update_user_meta($id, 'description', $red_name);
            $value = array('', $red_link, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
            update_user_meta($id, 'bpc_comms_market_consent', $value);
            update_user_meta($id, 'user_identification_number', $red_ID);
            update_user_meta($id, 'other_identification_issue', $red_name);
            update_user_meta($id, 'other_identification_type', $red_name);
            update_user_meta($id, 'other_identification_number', $red_name);
            // Single occurance only
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.DirectQuery
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
            global $wpdb;
            $table_name = 'newsletter';
            // @phpcsSuppress WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            /* FALSE-POSITIVE -> PLACEHOLDER IS USED FOR VARIABLE, IF USED FOR TABLE NAME THE CODE BREAKS */ $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$table_name} WHERE email = %d", $this_user_email));


            if (count($result) > 0) {
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('updated' => $time_now), array('id' => $id));
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('surname' => $red_name), array('id' => $id));
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('sex' => $red_name), array('id' => $id));
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('ip' => $red_IP), array('id' => $id));
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('geo' => 0), array('id' => $id));
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('country' => $red_name), array('id' => $id));
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('region' => $red_name), array('id' => $id));
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('city' => $red_name), array('id' => $id));
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('bounce_type' => $red_name), array('id' => $id));
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('email' => $red_email), array('id' => $id));
                // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('email' => $red_email), array('id' => $id));
            }
            // Multiple occurance possible
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.DirectQuery
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
            global $wpdb;
            $table_name = 'wpml_mails';
            $result = $wpdb->get_results(
                // @phpcsSuppress WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                /* FALSE-POSITIVE -> PLACEHOLDER IS USED FOR VARIABLE, IF USED FOR TABLE NAME THE CODE BREAKS */ $wpdb->prepare("SELECT mail_id FROM {$wpdb->prefix}{$table_name} WHERE `receiver` = %s", $this_user_email));
            if (count($result) > 0) {
                foreach ($result as $thisId) {
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('subject' => $red_name), array('receiver' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $red_mail_body = '<!doctype html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width"><title>WP Mail SMTP Test Email</title><style type="text/css">@media only screen and (max-width: 599px) {table.body .container {width: 95% !important;}.header {padding: 15px 15px 12px 15px !important;}.header img {width: 200px !important;height: auto !important;}.content, .aside {padding: 30px 40px 20px 40px !important;}}</style></head><body style="height: 100% !important; width: 100% !important; min-width: 100%; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box; -webkit-font-smoothing: antialiased !important; -moz-osx-font-smoothing: grayscale !important; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; margin: 0; Margin: 0; font-size: 14px; mso-line-height-rule: exactly; line-height: 140%; background-color: #f1f1f1; text-align: center;"><table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%" class="body" style="border-collapse: collapse; border-spacing: 0; vertical-align: top; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; height: 100% !important; width: 100% !important; min-width: 100%; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box; -webkit-font-smoothing: antialiased !important; -moz-osx-font-smoothing: grayscale !important; background-color: #f1f1f1; color: #444; font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; margin: 0; Margin: 0; text-align: left; font-size: 14px; mso-line-height-rule: exactly; line-height: 140%;"><tr style="padding: 0; vertical-align: top; text-align: left;"><td align="center" valign="top" class="body-inner wp-mail-smtp" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; font-weight: normal; padding: 0; margin: 0; Margin: 0; font-size: 14px; mso-line-height-rule: exactly; line-height: 140%; text-align: center;"> The user this was sent too requested that their data be deleted oat the folowing timestamp:' . $time_now . '</td></tr></table></body></html>';
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('subject' => $red_name), array('receiver' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('message' => $red_mail_body), array('receiver' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('attachements' => NULL), array('receiver' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('receiver' => $red_email), array('receiver' => $this_user_email));
                }
            }
            // Multiple occurance possible
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.DirectQuery
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
            global $wpdb;
            $table_name = 'wc_customer_lookup';
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching | // @phpcsSuppress WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            /* FALSE-POSITIVE -> PLACEHOLDER IS USED FOR VARIABLE, IF USED FOR TABLE NAME THE CODE BREAKS, IS PREPARED | DELIBERATELY DID NOT CACHE */ $result = $wpdb->get_results($wpdb->prepare("SELECT customer_id FROM {$wpdb->prefix}{$table_name} WHERE `email` = %s", $this_user_email));
            
            if (count($result) > 0) {
                foreach ($result as $thisId) {
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('username' => $red_name), array('email' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('first_name' => $red_name), array('email' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('last_name' => $red_name), array('email' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('country' => $red_name), array('email' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('postcode' => '0000'), array('email' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('city' => $red_name), array('email' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('state' => $red_name), array('email' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('email' => $red_email), array('email' => $this_user_email));
                }
            }
            // Multiple occurance possible
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.DirectQuery
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
            global $wpdb;
            $table_name = 'cartflows_ca_cart_abandonment';
            // @phpcsSuppress WordPress.DB.PreparedSQL.InterpolatedNotPrepared | @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
            /* FALSE-POSITIVE -> PLACEHOLDER IS USED FOR VARIABLE, IF USED FOR TABLE NAME THE CODE BREAKS | FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $result = $wpdb->get_results($wpdb->prepare("SELECT customer_id FROM {$wpdb->prefix}{$table_name} WHERE `email` = %s", $this_user_email));
            
            if (count($result) > 0) {
                foreach ($result as $thisId) {
                    $value = array('wcf_billing_company', '', 'wcf_billing_address_1', '', 'wcf_billing_address_2', '', 'wcf_billing_state', '', 'wcf_billing_postcode', '', 'wcf_shipping_first_name', '', 'wcf_shipping_last_name', '', 'wcf_shipping_company', '', 'wcf_shipping_country', '', 'wcf_shipping_address_1', '', 'wcf_shipping_address_2', '', 'wcf_shipping_city', '', 'wcf_shipping_state', '', 'wcf_shipping_postcode', '', 'wcf_order_comments', '', 'wcf_first_name', '', 'wcf_last_name', '', 'wcf_phone_number', '', 'wcf_location', '');
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('other_fields' => $value), array('email' => $this_user_email));
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('email' => $red_email), array('email' => $this_user_email));
                }
            }
            // Multiple occurance possible
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.DirectQuery
            // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
            global $wpdb;
            $table_name = $wpdb->prefix . 'mailchimp_carts';
            // @phpcsSuppress WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            /* FALSE-POSITIVE -> PLACEHOLDER IS USED FOR VARIABLE, IF USED FOR TABLE NAME THE CODE BREAKS, IS PREPARED | DELIBERATELY NOT CACHED*/ $result = $wpdb->get_results($wpdb->prepare("SELECT customer_id FROM {$wpdb->prefix}{$table_name} WHERE `email` = %s", $this_user_email));
            
            if (count($result) > 0) {
                foreach ($result as $thisId) {
                    // @phpcsSuppress WordPress.DB.DirectDatabaseQuery.NoCaching
                    /* FALSE-POSITIVE -> IS PREPARED | DELIBERATELY DID NOT CACHE */ $wpdb->update($wpdb->prefix.$table_name, array('email' => $red_email), array('email' => $this_user_email));
                }
            }
            if (is_wp_error($user_data)) {echo 'Error.';} else {echo 'User profile updated.';}
            //  Now use the opportunity to notify the user that their data had been redacted, then repeat for next person if applicable
            $url  = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/deleteddata/");
            $body = array(
                'name' => $this_user_name,
                'email' => $this_user_email,
                'domain_id' => $consent_domain_id,
            );
            $args = array('method' => 'PUT', 'timeout' => 45, 'sslverify' => false, 'headers' => array('Content-Type' => 'application/json',), 'body' => wp_json_encode($body),);
            $request = wp_remote_post(wp_http_validate_url($url), $args);
            if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {error_log(print_r($request, true));}
        }
        // Remove from changes on BPC
        $removeId = get_option('bpc_refference');
        $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/updateconsentchangedarray/" . $removeId . "/");
        $body = array('data_deletion_approved' => null);
        $args = array('headers' => array('Content-Type'   => 'application/json',), 'body' => wp_json_encode($body), 'method' => 'PUT');
        $result =  wp_remote_request(wp_http_validate_url($url), $args);
        update_option('bpc_data_deletion_approved', null);
    }
    /* Data Deletion Processing Approved Ends  */
    /* --------------------------------------------------------------------------------------------------------------------------*/
    /*Data Deletion Processing Requests Starts*/
    $ids =  get_option('bpc_data_deletion');
    if (isset($ids) && $ids != '') {
        $data_to_send = '[';
        foreach ($ids as $id) {
            $id = str_replace(' ', '', $id);
            $data = get_userdata($id);
            $user_email = $data->user_email;
            $consent_domain_id = get_option('this_domain_identity');
            $data_to_send = $data_to_send . '{' . $id . ', ' . $consent_domain_id . ', ' . $user_email . '}, ';
        }
        $data_to_send = $data_to_send . ']';
        $data_to_send = str_replace('}, ]', '}]', $data_to_send);
        $data_to_send = str_replace('[{', '', $data_to_send);
        $data_to_send = str_replace('}]', '', $data_to_send);
        $bpc_ref = get_option('bpc_refference');
        //  Now use the opportunity to post this collected data to the BPC Portal for further processing, then repeat for next person if applicable
        $url  = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/datadeletewp/" . $bpc_ref . "/");
        $body = array('data' => $data_to_send);
        $args = array('method' => 'PUT', 'timeout' => 45, 'sslverify' => false, 'headers' => array('Content-Type' => 'application/json',), 'body' => wp_json_encode($body),);
        $request = wp_remote_post(wp_http_validate_url($url), $args);
        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {error_log(print_r($request, true));}
        // Remove from changes on BPC
        $removeId = get_option('bpc_refference');
        $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/updateconsentchangedarray/" . $removeId . "/");
        $body = array('data_deletion' => null);
        $args = array('headers' => array('Content-Type' => 'application/json',), 'body' => wp_json_encode($body), 'method' => 'PUT');
        $result = wp_remote_request(wp_http_validate_url($url), $args);
        update_option('bpc_data_deletion', null);
    }
    /* Data Deletion Processing Requests Ends */
    /* --------------------------------------------------------------------------------------------------------------------------*/
}
