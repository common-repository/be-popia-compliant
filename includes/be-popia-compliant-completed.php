<?php

function be_popia_compliant_active_check()
{
    if (isset($_REQUEST)) {
        global $wpdb;
        $url = "https://py.bepopiacompliant.co.za/api/domain/check_expiry/" . $_SERVER['SERVER_NAME'];
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => array(),
        );
        $response = wp_remote_get($url, $args);
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if (401 === $response_code) {
            echo "Unauthorized access, You do not seem to be authorised to access this data!";
        }
        if (200 === $response_code) {
            $trim_brackets = trim($body, "[{}]");
            $explode = explode(',', $trim_brackets);
            $trim_date = str_replace('"renew_date":', '', $explode[1]);
            $trim_date = str_replace('"', '', $trim_date);
            $go_on = str_replace('"is_subscribed":', '', $explode[2]);
            $consent_form_complete = str_replace('"consent_form_complete":', '', $explode[3]);
            $domain_form_complete = str_replace('"domain_form_complete":', '', $explode[4]);
            $domain_form_complete = str_replace('"', '', $domain_form_complete);
            $domain_form_complete = str_replace('"', '', $domain_form_complete);
            $other_parties = str_replace('"other_parties":', '', $explode[5]);
            $other_parties = str_replace('"', '', $other_parties);
            $trim_date = trim($trim_date, '"');
            $go_on = trim($go_on, '"');
            $date = strtotime($trim_date);
            $date = date('Y-m-d', $date);
            if ($date >= date("Y-m-d") && $consent_form_complete == 1 && $domain_form_complete == 1 && ($other_parties != null) || ($other_parties != '')) {
                if ($go_on == 1) {
                    global $wpdb;
                    $privacy = '';
                    $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
                    $wpdb->update($table_name, array('value' => 0), array('id' => 3));
                    
                    $banner_background = get_option('be_popia_compliant_banner-field11-background-color');
                    $banner_text = get_option('be_popia_compliant_banner-field12-text-color');
                    echo '<style>
                            .BePopiaCompliant {
                                background-color:' . $banner_background . ';
                                color:' . $banner_text . ';
                                text-align: center;
                                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                            }
                            .cont1 {
                                margin: auto;
                                width: 90%;
                                height: 80px;
                                display: flex;
                            }
                            .be_popia_compliant_img {
                                margin: 20px auto auto 10%;
                                width: 150px;
                            }
                            span.bpc_contnents {
                                padding: 2%;
                            }
                            .be_popia_compliant_links {
                                margin: auto auto auto 0;
                                width: -webkit-fill-available;
                                font-weight:500;
                                font-size: 20px;
                            }
                            .be_popia_compliant_links a {
                                color:' . $banner_text . ';
                                text-decoration: none;
                                font-variant-caps: all-petite-caps;
                                font-weight: 500;
                            }
                            @media only screen and (max-width: 748px) {    
                                .be_popia_compliant_img {
                                    margin: auto auto auto auto;
                                    padding: 15px;
                                }
                                .be_popia_compliant_links {
                                    margin: auto auto auto auto;
                                    width: 100%;
                                    font-weight: 700;
                                    font-size: 23px;
                                }
                                .cont1 {
                                    margin: auto;
                                    width: 80%;
                                    height: 245px;
                                    display: block;  
                                }
                            }
                        </style>
                        <div class="BePopiaCompliant">
                            <div class="cont1">
                                <div class="be_popia_compliant_img">';
                                    if(strval(get_option('be_popia_compliant_cookie-field10-banner-logo-selector')) == 'white') {
                                    echo '<a href="https://bepopiacompliant.co.za" target="_blank"><img alt="POPIA Compliant" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAAAqCAMAAABm11LeAAAACXBIWXMAAC4jAAAuIwF4pT92AAACqVBMVEVHcEz////////+/v7////////////////////////////9/f3////////////////////////////////////////////////+/v7////////////////////////////////////////////+/v7////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////+/v7////////////////////////////////////////////////////////////////////////////////////////////////////////+/v7////////////////////////////////////////////////////////////////////////////////////////9/f3////////+/v7+/v77+/v///+5qEj3AAAA4nRSTlMAaJxnegdj+pgEuQGburhhCJ15pAOQjWICjqimbgLwo+1sp+8HD5HxpZeiqZWZ+wpJ3hA/9ukLmrsG678Mj8P+W+5P/RyvX4SzfpOUGEt0sj7800359dAb6kFI4toVBQ0hZhYmti2DklpGCdzn5V030eOFlkCg+CiJnjpVxtXkDiS1gYDXOW1xIrz0vfK+PXNM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" oncontextmenu="return false;" />
                                    </a>';
                                } elseif(strval(get_option('be_popia_compliant_cookie-field10-banner-logo-selector')) == 'black') {
                                    echo '<a href="https://bepopiacompliant.co.za" target="_blank"><img alt="POPIA Compliant" src="data:image/png;base64,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" oncontextmenu="return false;" />
                                    </a>';
                                } elseif(strval(get_option('be_popia_compliant_cookie-field10-banner-logo-selector')) == 'grey') {
                                    echo '<a href="https://bepopiacompliant.co.za" target="_blank"><img alt="POPIA Compliant" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAAAqCAMAAABm11LeAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAJcEhZcwAALiMAAC4jAXilP3YAAAH4UExURUdwTIaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaHioaIioaIioaIioaIioaIioaHioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioSIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIioaIivDS1skAAACndFJOUwBjJL5GBNrb8sYjvQOU3GIHlwGslq7oqAIM/AXZo7EGk+Xs9PgVrZXMWBHSq6+yU7+qqRpl9ZvQKQrg5jL+MJ6g11YLDk4JGB04a2dFjMr3dLDqgLoEwByZLlSnzyUI8EgrtePh/V3VgxPBfz5qJrQ2SsSR3kN+Ifo6PGAtNN1uIHBSQI+KfLd3hmZbH/mSECeljkfv2ISHesjr7Z0XWXJNuIjCxbxMtvszUwAACdxJREFUWMPVmGlbGskWgEsNShQVYWiRJQQQRXFHEREQBJSALCJXExQjUVHc0LjivkZN1EjiLmoSk5y/eatxw2eSeWbu3Lnxvh/ohaL7rerTp06B0I8xLiWE0INj8jdqRfmDswolgKPX+MCkGN97wHGQ9cCs2LUq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" oncontextmenu="return false;" />
                                    </a>';
                                } else {  echo '<a href="https://bepopiacompliant.co.za" target="_blank"><img alt="POPIA Compliant" src="data:image/png;base64,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" oncontextmenu="return false;" />
                                </a>';
                                }

                                echo '</div>
                                <div class="be_popia_compliant_links">
                                        ';
                    echo '<center>
                    <span class="bpc_contnents"><a href="' . esc_url('https://bepopiacompliant.co.za/#/privacy/' . $_SERVER['SERVER_NAME']) . '" target="_blank"><span style="white-space:nowrap">PRIVACY POLICY</span></a></span>
                    <span class="bpc_contnents"><a href="' . esc_url('https://manageconsent.co.za/#/main/request/' . $_SERVER['SERVER_NAME']) . '" target="_blank"><span style="white-space:nowrap">MANAGE CONSENT</span></a></span>
                    <span class="bpc_contnents"><a href="' . esc_url('https://bepopiacompliant.co.za/#/details/' . $_SERVER['SERVER_NAME']) . '" target="_blank"><span style="white-space:nowrap">RESPONSIBLE PARTIES</span></a></span>
                    <span class="bpc_contnents"><a href="https://bepopiacompliant.co.za/#/regulator/' . $_SERVER['SERVER_NAME']  . '" target="_blank"><span style="white-space:nowrap">INFORMATION REGULATOR</span></a></span></center>';
                    echo '</div>
                    <span style="font-size:0px; position:absolute;">';
                    update_option('bpc_report', '8');
                    echo "BPC REPORT 8: " . get_option("bpc_v");
                    $has_active_keys = get_option('has_active_keys');
                    if ($has_active_keys == 1) {
                        echo " PRO ";
                    } else {
                        echo " Free ";
                    }
                    if (get_option("cron_last_fired_at")) {
                        echo date("d/m/Y H:i:s", get_option("cron_last_fired_at") + 7200);
                    } else {
                        echo "No Run";
                    }
                    if (get_option("be_popia_compliant_cookie-field9-disable-bpc-cookie-banner") != 1) {
                        echo " Active ";
                    } else {
                        echo " Deactivated ";
                    }
                    if (is_ssl()) {
                        echo "Has SSL";
                    } else {
                        echo "No SSL";
                    }
                    echo '</span>
                            </div>
                        </div>';
                } else {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'be_popia_compliant_admin';
                    $wpdb->update($table_name, array('value' => 1), array('id' => 3));
                    $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';
                    $needComms = $wpdb->get_var("SELECT `does_comply` FROM $table_name WHERE id = 2");
                    $needMarketing = $wpdb->get_var("SELECT `does_comply` FROM $table_name WHERE id = 3");
                    if ($needComms == 1 && $needMarketing == 0) {
                        $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 3) AND (id != 59) AND is_active = 1");
                        $rowcount = $wpdb->num_rows;
                        $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 3) AND (id != 59) AND is_active = 1");
                        $rowcount2 = $wpdb->num_rows;
                    } elseif ($needComms == 0 && $needMarketing == 1) {
                        $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 2) AND (id != 58) AND is_active = 1");
                        $rowcount = $wpdb->num_rows;
                        $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 2) AND (id != 58) AND is_active = 1");
                        $rowcount2 = $wpdb->num_rows;
                    } elseif ($needComms == 1 && $needMarketing == 1) {
                        $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND is_active = 1");
                        $rowcount = $wpdb->num_rows;
                        $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND is_active = 1");
                        $rowcount2 = $wpdb->num_rows;
                    } elseif ($needMarketing == 0 && $needComms == 0) {
                        $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 2) AND (id != 3) AND (id != 58) AND (id != 59) AND is_active = 1");
                        $rowcount = $wpdb->num_rows;
                        $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 2) AND (id != 3) AND (id != 58) AND (id != 59) AND is_active = 1");
                        $rowcount2 = $wpdb->num_rows;
                    }
                    update_option('bpc_rowcount', $rowcount);
                    --$rowcount2;
                    update_option('bpc_rowcount2', $rowcount2);
                    $rowcount = ($rowcount / $rowcount2) * 100;
                    $rowcount = sanitize_text_field(get_option('bpc_rowcount'));
                    $rowcount2 = sanitize_text_field(get_option('bpc_rowcount2'));
                    $rowcount = ($rowcount / $rowcount2) * 100;
                    echo '<br>';
                    $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/plugindetailscheck/" . $_SERVER['SERVER_NAME']);
                    $args = array(
                        'headers' => array(
                            'Content-Type' => 'application/json',
                        ),
                        'body' => array(),
                    );
                    $response = wp_remote_get($url, $args);
                    $response_code = wp_remote_retrieve_response_code($response);
                    $body = wp_remote_retrieve_body($response);

                    if (401 === $response_code) {
                        echo "Unauthorized access";
                    }

                    if (200 === $response_code) {
                        $body = json_decode($body);
                        if ($body != []) {
                            foreach ($body as $data) {
                                $is_approved = $data->is_approved;
                                // IF Premium expired and the free vresion is 100% and not blocked by PBC Office it will show the free footer instead.
                                if ($is_approved) {
                                    if ($rowcount == 100) {
                                        $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';
                                        $privacy = $wpdb->get_var("SELECT content FROM $table_name WHERE id = 6");
                                        $data = $wpdb->get_var("SELECT content FROM $table_name WHERE id = 21");
                                        $parties = $wpdb->get_var("SELECT content FROM $table_name WHERE id = 32");
                                        echo '<style>
                                            .BePopiaCompliant {
                                                background-color: whitesmoke;
                                                color: #000;
                                                text-align: center;
                                                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                                            }
                                            .cont1 {
                                                margin: auto;
                                                width: 50%;
                                                height: 125px;
                                                display: flex;
                                            }
                                            .be_popia_compliant_img {
                                                margin: auto 0 auto auto;
                                            }
                                            .be_popia_compliant_links {
                                                margin: auto auto auto 0;
                                                width: 75%;
                                                font-weight:900;
                                                font-size: 23px;
                                            }
                                            .be_popia_compliant_links a {
                                                color: #BD2E2E;
                                                text-decoration: none;
                                                font-variant-caps: all-petite-caps;
                                            }
                                            @media only screen and (max-width: 748px) {    
                                                .be_popia_compliant_img {
                                                    margin: auto 0 auto auto;
                                                }
                                                .be_popia_compliant_links {
                                                    margin: auto auto auto 0;
                                                    width: 100%;
                                                    font-weight: 900;
                                                    font-size: 23px;
                                                }
                                                .cont1 {
                                                    margin: auto;
                                                    width: 50%;
                                                    height: 245px;
                                                    display: block;
                                                }
                                            }
                                        </style>
                                        <div class="BePopiaCompliant">
                                            <div class="cont1">
                                                <div class="be_popia_compliant_img">
                                                    <a href="https://bepopiacompliant.co.za" target="_blank"><img alt="POPIA Compliant" src="data:image/png;base64,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" oncontextmenu="return false;" />
                                                    </a>
                                                </div>
                                                <div class="be_popia_compliant_links">
                                                    <a href="' . esc_url($privacy) . '" target="_blank"><span style="white-space:nowrap">PRIVACY POLICY</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="' . esc_url($data) . '" target="_blank"><span style="white-space:nowrap">DATA REQUESTS</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="' . esc_url($parties) . '" target="_blank"><span style="white-space:nowrap">RESPONSIBLE PARTIES</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="https://bepopiacompliant.co.za/information_regulator" target="_blank"><span style="white-space:nowrap">INFORMATION REGULATOR</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                </div>
                                                <span style="font-size:0px; position:absolute;">';
                                        update_option('bpc_report', '9');
                                        echo "BPC REPORT 9: " . get_option("bpc_v");
                                        $has_active_keys = get_option('has_active_keys');
                                        if ($has_active_keys == 1) {
                                            echo " PRO ";
                                        } else {
                                            echo " Free ";
                                        }
                                        if (get_option("cron_last_fired_at")) {
                                            echo date("d/m/Y H:i:s", get_option("cron_last_fired_at") + 7200);
                                        } else {
                                            echo "No Run";
                                        }
                                        if (get_option("be_popia_compliant_cookie-field9-disable-bpc-cookie-banner") != 1) {
                                            echo " Active ";
                                        } else {
                                            echo " Deactivated ";
                                        }
                                        if (is_ssl()) {
                                            echo "Has SSL";
                                        } else {
                                            echo "No SSL";
                                        }
                                        echo "is_subscribed = 0";
                                        echo '</span>
                                            </div>
                                        </div>';
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                global $wpdb;
                $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';
                $needComms = $wpdb->get_var("SELECT does_comply FROM $table_name WHERE id = 2");
                $needMarketing = $wpdb->get_var("SELECT does_comply FROM $table_name WHERE id = 3");
                if ($needComms == 1 && $needMarketing == 0) {
                    $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 3) AND (id != 59) AND is_active = 1");
                    $rowcount = $wpdb->num_rows;
                    $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 3) AND (id != 59) AND is_active = 1");
                    $rowcount2 = $wpdb->num_rows;
                } elseif ($needComms == 0 && $needMarketing == 1) {
                    $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 2) AND (id != 58) AND is_active = 1");
                    $rowcount = $wpdb->num_rows;
                    $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 2) AND (id != 58) AND is_active = 1");
                    $rowcount2 = $wpdb->num_rows;
                } elseif ($needComms == 1 && $needMarketing == 1) {
                    $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND is_active = 1");
                    $rowcount = $wpdb->num_rows;
                    $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND is_active = 1");
                    $rowcount2 = $wpdb->num_rows;
                } elseif ($needMarketing == 0 && $needComms == 0) {
                    $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND does_comply = 1 AND (id != 2) AND (id != 3) AND (id != 58) AND (id != 59) AND is_active = 1");
                    $rowcount = $wpdb->num_rows;
                    $wpdb->get_results("SELECT * FROM $table_name WHERE (type < 8 AND type > 0) AND (id != 2) AND (id != 3) AND (id != 58) AND (id != 59) AND is_active = 1");
                    $rowcount2 = $wpdb->num_rows;
                }
                --$rowcount2;
                $rowcounttotal = ($rowcount / $rowcount2) * 100;
                if ($rowcounttotal == 100) {
                    $url = wp_http_validate_url("https://py.bepopiacompliant.co.za/api/plugindetailscheck/" . $_SERVER['SERVER_NAME']);
                    $args = array(
                        'headers' => array(
                            'Content-Type' => 'application/json',
                        ),
                        'body' => array(),
                    );
                    $response = wp_remote_get(wp_http_validate_url($url), $args);
                    $response_code = wp_remote_retrieve_response_code($response);
                    $body         = wp_remote_retrieve_body($response);

                    if (401 === $response_code) {
                        echo "Unauthorized access";
                    }

                    if (200 === $response_code) {
                        $body = json_decode($body);
                        if ($body != []) {
                            foreach ($body as $data) {
                                $is_approved = $data->is_approved;
                                if ($is_approved) {
                                    $table_name = $wpdb->prefix . 'be_popia_compliant_checklist';
                                    $privacy = $wpdb->get_var("SELECT content FROM $table_name WHERE id = 6");
                                    $data = $wpdb->get_var("SELECT content FROM $table_name WHERE id = 21");
                                    $parties = $wpdb->get_var("SELECT content FROM $table_name WHERE id = 32");
                                    echo '<style>
                                        .BePopiaCompliant {
                                            background-color: whitesmoke;
                                            color: #000;
                                            text-align: center;
                                            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                                        }
                                        .cont1 {
                                            margin: auto;
                                            width: 50%;
                                            height: 125px;
                                            display: flex;
                                        }
                                        .be_popia_compliant_img {
                                            margin: auto 0 auto auto;
                                        }
                                        .be_popia_compliant_links {
                                            margin: auto auto auto 0;
                                            width: 75%;
                                            padding: 1%;
                                            font-weight:900;
                                            font-size: 23px;
                                        }
                                        .be_popia_compliant_links a {
                                            color: #BD2E2E;
                                            text-decoration: none;
                                            font-variant-caps: all-petite-caps;
                                        }
                                        @media only screen and (max-width: 748px) {    
                                            .be_popia_compliant_img {
                                                margin: auto 0 auto auto;
                                            }
                                            .be_popia_compliant_links {
                                                margin: auto auto auto 0;
                                                width: 100%;
                                                font-weight: 900;
                                                font-size: 23px;
                                            }
                                            .cont1 {
                                                margin: auto;
                                                width: 50%;
                                                height: 245px;
                                                display: block;
                                            }
                                        }
                                    </style>
                                    <div class="BePopiaCompliant">
                                        <div class="cont1">
                                            <div class="be_popia_compliant_img">
                                                <a href="https://bepopiacompliant.co.za" target="_blank"><img alt="Self Audited - POPIA Compliant" src="data:image/png;base64,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" oncontextmenu="return false;" />
                                                </a>
                                            </div>
                                            <div class="be_popia_compliant_links">
                                                <a href="' . esc_url($privacy) . '" target="_blank"><span style="white-space:nowrap">PRIVACY POLICY</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="' . esc_url($data) . '"target="_blank"><span style="white-space:nowrap">DATA REQUESTS</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="' . esc_url($parties) . '" target="_blank"><span style="white-space:nowrap">RESPONSIBLE PARTIES</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="https://bepopiacompliant.co.za/#/regulator/' . $_SERVER['SERVER_NAME'] . '" target="_blank"><span style="white-space:nowrap">INFORMATION REGULATOR</span></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            </div>
                                            <span style="font-size:0px; position:absolute;">';
                                    update_option('bpc_report', '10');
                                    echo "BPC REPORT 10: " . get_option("bpc_v");
                                    $has_active_keys = get_option('has_active_keys');
                                    if ($has_active_keys == 1) {
                                        echo " PRO ";
                                    } else {
                                        echo " Free ";
                                    }
                                    if (get_option("cron_last_fired_at")) {
                                        echo date("d/m/Y H:i:s", get_option("cron_last_fired_at") + 7200);
                                    } else {
                                        echo "No Run";
                                    }
                                    if (get_option("be_popia_compliant_cookie-field9-disable-bpc-cookie-banner") != 1) {
                                        echo " Active ";
                                    } else {
                                        echo " Deactivated ";
                                    }
                                    if (is_ssl()) {
                                        echo "Has SSL";
                                    } else {
                                        echo "No SSL";
                                    }
                                    echo '</span>
                                        </div>
                                    </div>';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

be_popia_compliant_active_check();