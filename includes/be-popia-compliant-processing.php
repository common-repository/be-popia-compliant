<?php

// Check if hourly cron is active and unschedule it
if (wp_next_scheduled('be_popia_compliant_hourly_event')) {
    wp_clear_scheduled_hook('be_popia_compliant_hourly_event');
}

// Define daily cron schedule
add_filter('cron_schedules', 'be_popia_compliant_add_daily');
function be_popia_compliant_add_daily($schedules) {
    $schedules['daily'] = array(
        'interval'  => 24 * 60 * 60, // 24 hours in seconds
        'display'   => __('Once Daily', 'be_popia_compliant')
    );
    return $schedules;
}

// Schedule a daily action if it's not already scheduled
if (!wp_next_scheduled('be_popia_compliant_daily_event')) {
    wp_schedule_event(time(), 'daily', 'be_popia_compliant_daily_event');
}

// Hook your function to the scheduled daily event
add_action('be_popia_compliant_daily_event', 'bpc_popia_processing');
function bpc_popia_processing() {
    // Your daily processing code here
    $t = time();
    update_option('cron_last_fired_at', $t);

    // Function that will get the domain refference if not set.
    $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/getconsentchangesexternally/" . esc_html(servn()));
    $args = array('headers' => array('Content-Type' => 'application/json',), 'body' => array(), );
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
        }
    }

    // Ping url to ensure plugin is active
    $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/pingwordpressplugin/" . $id . "/");
    $t = time();
    $bpcV = get_option('bpc_v');
    $vType = 0;
    update_option('bpc_hasPro', $vType);
    $PIV = $bpcV . ' FREE';
    
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
}
