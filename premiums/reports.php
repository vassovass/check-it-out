<?php
if ( ! function_exists( 'handl_utm_grabber_reports_menu' ) ) {
	function handl_utm_grabber_reports_menu() {
		add_submenu_page(
			'handl-utm-grabber.php',
			'Analytics',
			'Analytics',
			'manage_options',
			'handl_analytics',
			'handl_analytics',
			1
		);
	}
}
add_action( 'admin_menu', 'handl_utm_grabber_reports_menu',  11);

function handl_utm_grabber_enqueue_reports(){
	wp_register_script( 'handl-utm-grabber-chartjs', plugins_url( '/js/chart.js' , dirname( __FILE__ ) ) );
	wp_register_style('jquery-ui-theme', plugins_url( '/css/jquery-ui.css' , dirname( __FILE__ ) ));
}
add_action( 'admin_enqueue_scripts', 'handl_utm_grabber_enqueue_reports' );

if ( ! function_exists( 'handl_analytics' ) ) {
	function handl_analytics(){
		global $handl_active, $handl_fields_disabled;
		do_action( 'maybe_dispay_license_error_notice' );

//		$a = HandLReportGetHandLOptionFromReport("handl_report_gravity-form-id-1-date-2023-11-28-2023-12-12");
//        print "<pre>";
//        print_r($a);

//		settings_errors('handl_report_insight');

		$report_id = "";
        $is_single_report = false;
		$current_report_opt = [];
		$current_report_obj = [];
		$handl_saved_reports = [];
		if ( isset($_GET['report_id']) && (int) $_GET['report_id'] > 0 ) {
			$report_id = (int) $_GET['report_id'];
		}

        global $wpdb;
        $all_options = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE option_name LIKE 'handl_report_%'", OBJECT );
        foreach ($all_options as $opt){
//                print_r($opt);
            preg_match('/^handl_report_(.*)-id-(.*)-date-(.*)/', $opt->option_name, $report_name_parts);
            [$nothing_, $cur_form_name, $cur_form_ids, $cur_date_range] = $report_name_parts;
            $handl_saved_reports[$opt->option_id] = ucwords(preg_replace('/-/'," ",$cur_form_name))." ".$cur_form_ids." ".$cur_date_range;

            if ($report_id == $opt->option_id){
                $is_single_report = true;
                $current_report_opt = $opt;

                $current_report_obj = unserialize($opt->option_value);
                $current_report_obj["report_id"] = $opt->option_id;
                $current_report_obj["report_name"] = $opt->option_name;
            }
        }

//		    print_r($current_report_obj);

//        print_r($handl_saved_reports);



		wp_enqueue_style('handl-utm-grabber-admin-css');
		wp_enqueue_script('handl-utm-grabber-admin');
		wp_localize_script( 'handl-utm-grabber-admin', 'HandLAdminReportInsight', $current_report_obj );
		wp_enqueue_script('handl-utm-grabber-chartjs');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-theme');


		?>
		<div class='wrap' id="handl-utm-apps">
			<h2><span class="dashicons dashicons-screenoptions" style='line-height: 1.1;font-size: 30px; padding-right: 10px;'></span> HandL UTM Grabber: ✨ AI Powered Analytics (BETA)</h2>
			<p>Here, you can delve deeply into the effectiveness of your marketing strategies. You can gather insights about what is working and what is not, as well as forecast future trends and devise necessary actions.</p>
			<p>If you need us to support another form not listed here, please let us know via chat. <a target="_blank" href="https://utmgrabber.com">utmgrabber.com</a></p>

            <div class="handl-report-filter">
                <select name="handl-report-form-plugins" id="handl-report-form-plugins" <?php echo $handl_fields_disabled;?>>
                    <option value="">Select Form Plugin</option>
                    <?php
                        $forms = [
                                "gravity-form" => "Gravity Form",
                                "elementor-pro" => "Elementor Pro",
                                "woocommerce" => "WooCommerce",
                                "wpforms" => "WPForms",
                                "ninja-forms" => "Ninja Forms",
                                "contact-form-db-divi" => "Divi Forms",
                                "contact-form-cfdb7" => "Contact Form 7",
                                "formidable" => "Formidable",
                            ];

                        foreach ($forms as $form_slug => $form_name){
                    ?>
                        <option value="<?php echo $form_slug; ?>" <?php echo $is_single_report && $form_slug == $current_report_obj["selected_form_plugin"] ? "selected" : "";?>><?php echo $form_name; ?></option>
                    <?php
                        }
                    ?>
                </select>

                <span class="handl-hide-load">
                    <select name="handl-report-forms[]" id="handl-report-forms" multiple>
    <!--                    <option value="">Select Form</option>-->
                    </select>
                </span>

                <span id="handl-report-date" class="handl-hide">
                    <label for="handl-report-start-date-picker">Start Date</label>
                    <input type="text" id="handl-report-start-date-picker" class="date-picker" value="<?php print date('Y-m-d', strtotime('-14 days', time()));?>">

                    <label for="handl-report-end-date-picker">End Date</label>
                    <input type="text" id="handl-report-end-date-picker" class="date-picker" value="<?php print date('Y-m-d');?>">

                    <input type="hidden" id="handl-report-start-date" value="">
                    <input type="hidden" id="handl-report-end-date" value="">
                </span>

                <span>
                    <a id="handl-report-submit" class="handl-hide button button-<?php print $is_single_report ? "disabled" : "primary" ?>" style="vertical-align: middle;">Generate Report</a>
                </span>

                <?php if ($is_single_report) { ?>
                    <a href="admin.php?page=handl_analytics" class="button button-secondary" style="vertical-align: middle;">Go Back</a>
			    <?php } ?>

                <?php if (sizeof($handl_saved_reports) > 0){ ?>
                <select onchange="window.location.href = 'admin.php?page=handl_analytics&report_id='+this.value" name="handl-report-saved-reports" id="handl-report-saved-reports" <?php echo $handl_fields_disabled;?>>
                    <option value="">Select Saved Report</option>
		            <?php foreach ($handl_saved_reports as $saved_report_id => $saved_report_name){
			            ?>
                        <option value="<?php echo $saved_report_id; ?>" <?php echo $is_single_report && $saved_report_id == $report_id ? "selected" : "";?>><?php echo $saved_report_name; ?></option>
			            <?php
		            }
		            ?>
                </select>
	            <?php } ?>
            </div>


            <div class="handl-report-container handl-hide">

                <?php if (!$is_single_report || ( $is_single_report && $current_report_obj["insight_report_parsed"] == "" ) ) { ?>

			        <?php if ( $is_single_report && $current_report_obj["insight_report_parsed"] == "" ) { ?>
                        <p>It seems we encountered a problem while generating your report. We apologize for the inconvenience. Please click the button below to try regenerating it again.</p>
	                <?php } ?>

                    <span>
                        <a id="handl-report-generate-insight" class="button button-primary" style="vertical-align: middle;">✨ Generate Insight</a>
                    </span>

	                <div id="no-license-key" class="handl-hide">
                        <p>A license key has not been provided. Please ensure that you have activated your license key on this WordPress instance. Refer to our knowledge base for <a href="https://docs.utmgrabber.com/books/101-lets-start/page/how-to-activate-handl-utm-grabbertracker-v3" target="_blank">a step-by-step guide</a> on how to activate your license.</p>
                    </div>

                    <div id="license-not-active" class="handl-hide">
                        <p>Your license is currently inactive or your subscription has expired. Please ensure you purchase a new license or renew your membership at <a href="https://utmgrabber.com/" target="_blank">utmgrabber.com</a>.</p>
                    </div>

                    <div id="not-enough-credits" class="handl-hide">
                        <p>You do not have sufficient credits to generate the report. Please purchase additional credits using the button below.</p>
                        <script async
                                src="https://js.stripe.com/v3/buy-button.js">
                        </script>

                        <stripe-buy-button
                                client-reference-id="<?php print get_option( 'license_key_handl-utm-grabber-v3' );?>"
                                buy-button-id="buy_btn_1OMo0WAazLOJAQUCeVny4T7Y"
                                publishable-key="pk_live_51H3skGAazLOJAQUCrffcRCrCPy9zCNZqdT3lojCSSf53tE21plMOFXq5tzJzAcuA4t2BEG0i557yTx05AEVkEcy7008mdT7ndn"
                        </stripe-buy-button>
                    </div>

                <?php } ?>

                <?php
                 if ($is_single_report && isset($current_report_obj["insight_report_parsed"])) {

                     $insight_parts = json_decode($current_report_obj["insight_report_parsed"], true);
                ?>
                <div class="handl-accordion">
                    <div class="handl-insight">
                        <h1>✨ AI-Powered Insights Just For You</h1>
                        <ul>
	                        <?php
                            $dashicons = ["good" => "yes",
                                          "bad" => "warning",
                                          "action_items" => "star-filled",
                                          "other" => "list-view"];
                            foreach ([
                                    "good" => "Things are going well",
                                    "bad" => "Things can be improved",
                                    "action_items" => "Action items",
                                    "other" => "Other important observations",

                            ] as $insight_cat_slug => $insight_cat) {
                            if (isset($insight_parts[$insight_cat_slug])){ ?>
                            <li>
                                <input type="checkbox" <?php echo $insight_cat_slug == "good" ? "" : "checked";?>>
                                <i></i>
                                <h2><span class="dashicons dashicons-<?php print $dashicons[$insight_cat_slug];?>"></span><?php echo $insight_cat; ?></h2>
                                <p><?php echo $insight_parts[$insight_cat_slug] ?></p>
                            </li>
		                    <?php } } ?>
                        </ul>
                    </div>
                </div>
                 <?php } ?>

                <div class="handl-report-body">
                    <?php
                    $fields = ['traffic_source','utm_campaign','utm_source','utm_medium','utm_content','utm_term'];
                    foreach ($fields as $field){
                    ?>
                    <div class="handl-report card">
                        <div class="container">
                            <span class="handl-report-chart">
                                <canvas id="handl_report_<?php echo $field;?>_chart" ></canvas>
                            </span>
                            <span id="handl_report_<?php echo $field;?>_table" class="handl-report-table-container"></span>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                </div>

                <?php if ($is_single_report) { ?>
                        <span>
                            <p class="small-text"><a href="javascript:void(0)" id="handl-report-delete-report">Delete the report</a></p>
                        </span>
			    <?php } ?>
            </div>


            <div id="handl-report-error" class="notice notice-error settings-error is-dismissible handl-hide"></div>

		</div>
		<?php
	}
}


if ( ! function_exists( 'handl_report_list_forms' ) ) {
	function handl_report_list_forms() {
		$response = [];
        if (isset($_POST['selected_form_plugin'])){
	        $forms_res = [];
	        $selected_form_plugin = $_POST['selected_form_plugin'];
            if ($selected_form_plugin == "gravity-form"){
	            if (is_plugin_active('gravityforms/gravityforms.php')){
		            $forms = GFAPI::get_forms();
                    if (sizeof($forms) > 0){
                        foreach ($forms as $form){
                            array_push($forms_res, ["value" => $form['id'], "name" => $form['title']." (".$form['id'].")"]);
                        }
                    }else{
	                    $response['error'] = "No forms found";
                    }
	            }else{
		            $response['error'] = $selected_form_plugin." plugin is not active";
                }
            } elseif ( $selected_form_plugin == "elementor-pro" ) {
	            if ( is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
                    global $wpdb;
		            $forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '__elementor_forms_snapshot'", OBJECT );
		            foreach ($forms as $form){
                        $cur_values = json_decode($form->meta_value)[0];
			            array_push($forms_res, ["value" => $form->post_id, "name" => $cur_values->name." (".$cur_values->id.")"]);
		            }
	            }else{
		            $response['error'] = $selected_form_plugin." plugin is not active";
	            }
            } elseif ( $selected_form_plugin == "wpforms" ) {
	            if ( is_plugin_active( 'wpforms/wpforms.php' ) ) {
		            global $wpdb;
		            $forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'wpforms'", OBJECT );
		            foreach ($forms as $form){
			            array_push($forms_res, ["value" => $form->ID, "name" => $form->post_title." (".$form->ID.")"]);
		            }
	            }else{
		            $response['error'] = $selected_form_plugin." plugin is not active";
	            }
            } elseif ( $selected_form_plugin == "ninja-forms" ) {
	            if ( is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
		            global $wpdb;
		            $forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}nf3_forms", OBJECT );
		            foreach ($forms as $form){
			            array_push($forms_res, ["value" => $form->id, "name" => $form->title." (".$form->id.")"]);
		            }
	            }else{
		            $response['error'] = $selected_form_plugin." plugin is not active";
	            }
            } elseif ( $selected_form_plugin == "woocommerce" ) {
	            if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		            array_push($forms_res, ["value" => "all", "name" => "All the orders"]);
	            }else{
		            $response['error'] = $selected_form_plugin." plugin is not active";
	            }
            } elseif ( $selected_form_plugin == "contact-form-db-divi" ) {
	            if ( is_plugin_active( 'contact-form-db-divi/index.php' ) ) {
		            array_push($forms_res, ["value" => "all", "name" => "All the forms"]);
	            }else{
		            $response['error'] = $selected_form_plugin." plugin is not active";
	            }
            } elseif ( $selected_form_plugin == "contact-form-cfdb7" ) {
	            if ( is_plugin_active( 'contact-form-cfdb7/contact-form-cfdb-7.php' ) ) {
		            $forms = WPCF7_ContactForm::find();
		            foreach ($forms as $form) {
			            array_push($forms_res, ["value" => $form->id(), "name" => $form->title()]);
		            }
	            }else{
		            $response['error'] = $selected_form_plugin." plugin is not active";
	            }
            } elseif ( $selected_form_plugin == "formidable" ) {
	            if ( is_plugin_active( 'formidable/formidable.php' ) ) {
		            global $wpdb;
		            $forms = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}frm_forms WHERE is_template=0", OBJECT );
		            foreach ($forms as $form){
			            array_push($forms_res, ["value" => $form->id, "name" => $form->name." (".$form->id.")"]);
		            }
	            }else{
		            $response['error'] = $selected_form_plugin." plugin is not active";
	            }
            } else {
	            $response['error'] = $selected_form_plugin." is not supported yet. Please contact with us";
            }
        }else{
	        $response['error'] = "No form found";
        }

		$response["forms"] = $forms_res;
		wp_send_json($response);
        wp_die();
	}
}
add_action( 'wp_ajax_handl_report_list_forms', 'handl_report_list_forms' );

function handl_report_get_entries_func($selected_form_plugin, $selected_form_ids, $search_criteria){
	$entries_res = [];

	if (in_array(site_url() , ["https://handl-sandbox","http://localhost/mywordpress"]) and 0){
		$test_json = plugin_dir_path(__FILE__)."../../handl_report_insight_test.json";
		$entries_res = json_decode(file_get_contents($test_json), true);
	}else{
		$fields = [
			'email',
			'utm_campaign',
			'utm_source',
			'utm_medium',
			'utm_content',
			'utm_term',
			'traffic_source'
		];
		if ( $selected_form_plugin == "gravity-form" ) {
			if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {

				$entries = GFAPI::get_entries( $selected_form_ids, $search_criteria );
				//                    print_r($entries);
				$form_fields = [];
				foreach ( $entries as $entry ) {
					//                            print_r($entry);
					$form_id = $entry["form_id"];

					if ( ! isset( $form_fields[ $form_id ] ) ) {
						$form = GFAPI::get_form( $form_id );

						$cur_form_fields = [];
						foreach ( $form["fields"] as $field ) {
							$cur_form_fields[ $field["id"] ] = $field["inputName"];
						}

						$form_fields[ $form_id ] = $cur_form_fields;
					}

					$cur_data         = [];
					$cur_data['date'] = $entry["date_created"];
					foreach ( $fields as $field ) {
						$field_index        = array_search( $field, $form_fields[ $form_id ] );
						$cur_data[ $field ] = isset( $entry[ $field_index ] ) ? $entry[ $field_index ] : "";
					}
					array_push( $entries_res, $cur_data );
				}
			} else {
				return new WP_Error( 'handl-403', $selected_form_plugin . " plugin is not active" );
			}
		} elseif ( $selected_form_plugin == "elementor-pro" ) {
			if ( is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
				global $wpdb;
                $selected_form_ids_str = implode(",",$selected_form_ids);
                $start_date = $search_criteria['start_date'];
				$end_date = $search_criteria['end_date'];
				$entries = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}e_submissions WHERE post_id IN ($selected_form_ids_str) AND created_at BETWEEN '$start_date' AND DATE_ADD('$end_date', INTERVAL 1 DAY)", ARRAY_A );
				foreach ( $entries as $entry ) {
					$form_id = $entry["id"];

					$form = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}e_submissions_values WHERE submission_id = $form_id", ARRAY_A );

                    $cur_data         = [];
					$cur_data['date'] = $entry["created_at"];
					foreach ( $form as $field ) {
                        if (in_array($field["key"], $fields)){
	                        $cur_data[ $field["key"] ] = $field["value"];
                        }
					}

					array_push( $entries_res, $cur_data );
				}
            }
		} elseif ( $selected_form_plugin == "wpforms" ) {
			if ( is_plugin_active( 'wpforms/wpforms.php' ) ) {
				global $wpdb;
				$selected_form_ids_str = implode(",",$selected_form_ids);
				$start_date = $search_criteria['start_date'];
				$end_date = $search_criteria['end_date'];

				$entries = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpforms_entries WHERE form_id IN ($selected_form_ids_str) AND date BETWEEN '$start_date' AND DATE_ADD('$end_date', INTERVAL 1 DAY)", ARRAY_A );
				foreach ( $entries as $entry ) {
					$form = json_decode($entry["fields"], true);
					$cur_data         = [];
					$cur_data['date'] = $entry["date"];
					foreach ( $form as $field ) {
						$field_name_norm = strtolower(str_replace(" ","_",$field["name"]));
                        if (in_array($field_name_norm, $fields)){
							$cur_data[ $field_name_norm ] = $field["value"];
						}
					}

					array_push( $entries_res, $cur_data );
				}
			}
		} elseif ( $selected_form_plugin == "ninja-forms" ) {
			if ( is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
				global $wpdb;
				$selected_form_ids_str = implode(",",$selected_form_ids);
				$start_date = $search_criteria['start_date'];
				$end_date = $search_criteria['end_date'];

				$entries = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts p WHERE post_date BETWEEN '$start_date' AND DATE_ADD('$end_date', INTERVAL 1 DAY) AND post_type='nf_sub'", ARRAY_A );
				foreach ( $entries as $entry ) {
					$cur_data         = [];
					$cur_data['date'] = $entry["post_date"];
                    $post_id = $entry["ID"];
                    $cur_fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta p WHERE post_id = $post_id AND meta_key = '_form_id' AND meta_value IN ($selected_form_ids_str)", ARRAY_A );
                    $form_id_2_fields = [];
                    foreach ( $cur_fields as $field ) {
                        $form_id = $field["meta_value"];
                        if (!in_array($form_id, $form_id_2_fields)){
                            $utm_fields_arr = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}nf3_fields p WHERE parent_id = $form_id", ARRAY_A );
                            $utm_fields_obj = [];
                            foreach($utm_fields_arr as $utm_field){
                                $default_value_clean = str_replace(array('{', '}', 'handl:'), '', $utm_field["default_value"]);
                                if ( in_array($default_value_clean, $fields) ){
//                                    array_push($utm_fields, $utm_field['id']);
	                                $utm_fields_obj[$utm_field['id']] = $default_value_clean;
                                }elseif ($utm_field["type"] == "email"){
	                                $utm_fields_obj[$utm_field['id']] = "email";
                                }
                            }

	                        $form_id_2_fields[$form_id] = $utm_fields_obj;
                        }

	                    $utm_fields = $form_id_2_fields[$form_id];
                        $utm_fields_ids = array_keys($utm_fields);

	                    $utm_fields_ids_prefix = array_map(function ($str) { return "_field_$str"; }, $utm_fields_ids);
	                    $utm_fields_ids_prefix_str = implode("','", $utm_fields_ids_prefix);
	                    $cur_utm_fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta p WHERE post_id = $post_id AND meta_key IN ('$utm_fields_ids_prefix_str')", ARRAY_A );
                        foreach($cur_utm_fields as $cur_utm_field){
                            $cur_key = $utm_fields[(int)str_replace("_field_", "", $cur_utm_field["meta_key"])];
	                        if (in_array($cur_key, $fields)){
		                        $cur_data[ $cur_key ] = $cur_utm_field["meta_value"];
	                        }
                        }
					}

					array_push( $entries_res, $cur_data );
				}
			}
		} elseif ( $selected_form_plugin == "woocommerce" ) {
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				$start_date = $search_criteria['start_date'];
				$end_date = $search_criteria['end_date'];
				$orders = wc_get_orders( array( 'limit' => -1, 'post_type' => 'shop_order', 'date_created'=> $start_date .'...'. $end_date  ) );
                foreach ( $orders as $order ) {
	                $cur_data         = [];
                    $cur_date['email'] = $order->get_billing_email();
                    $cur_data['date'] = $order->order_date;
                    foreach ($fields as $field){
	                    $cur_data[$field] = $order->get_meta($field);
                    }
					array_push( $entries_res, $cur_data );
				}
			}
		} elseif ( $selected_form_plugin == "contact-form-db-divi" ) {
			if ( is_plugin_active( 'contact-form-db-divi/index.php' ) ) {
				$start_date = $search_criteria['start_date'];
				$end_date = $search_criteria['end_date'];
				$args = array(
					'numberposts' => -1,
					'post_type'   => 'lwp_form_submission',
					'date_query'  => array(
						array(
							'after'     => $start_date,
							'before'    => $end_date,
							'inclusive' => true,
						),
					),
				);

				$posts = get_posts($args);
				foreach ( $posts as $post ) {
					$submission_details = get_post_meta( $post->ID, 'processed_fields_values', true );
					$cur_data         = [];
					$cur_date['email'] = $submission_details["email"]["value"];
					$cur_data['date'] = $post->post_date;
					foreach ($fields as $field){
						$cur_data[$field] = $submission_details[$field]["value"];
					}
					array_push( $entries_res, $cur_data );
				}
			}
		} elseif ( $selected_form_plugin == "contact-form-cfdb7" ) {
			if ( is_plugin_active( 'contact-form-cfdb7/contact-form-cfdb-7.php' ) ) {
				global $wpdb;
				$start_date = $search_criteria['start_date'];
				$end_date = $search_criteria['end_date'];
				$placeholders = implode(',', array_fill(0, count($selected_form_ids), '%d'));

				$query = $wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}db7_forms WHERE form_post_id IN ($placeholders) AND DATE(form_date) >= %s AND DATE(form_date) <= %s",
					array_merge($selected_form_ids, [$start_date, $end_date])
				);
				$results = $wpdb->get_results($query, ARRAY_A);

				foreach ($results as $row) {
					$data = unserialize($row["form_value"]);
					$cur_data = [];
					foreach ($fields as $field) {
						$cur_data[$field] = '';
						foreach ($data as $key => $value) {
							if (strpos($key, $field) === 0) {
								$cur_data[$field] = $value;
								break;
							}
						}
					}
					$cur_data['email'] = isset($data["your-email"]) ? $data["your-email"] : '';
					$cur_data['date'] = $row["form_date"];
					array_push($entries_res, $cur_data);
				}
			}
		} elseif ( $selected_form_plugin == "formidable" ) {
			if ( is_plugin_active( 'formidable/formidable.php' ) ) {
				global $wpdb;
				$selected_form_ids_str = implode(",", $selected_form_ids);
				$start_date = $search_criteria['start_date'];
				$end_date = $search_criteria['end_date'];

				$entries = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}frm_items WHERE created_at BETWEEN '$start_date' AND DATE_ADD('$end_date', INTERVAL 1 DAY) AND form_id IN ($selected_form_ids_str)", ARRAY_A );
				foreach ( $entries as $entry ) {
					$cur_data = [];
					$cur_data['date'] = $entry["created_at"];
					$item_id = $entry["id"];
					$form_id = $entry["form_id"];

					if (!isset($form_id_2_fields[$form_id])) {
						$utm_fields_arr = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}frm_fields WHERE form_id = $form_id", ARRAY_A );
						$utm_fields_obj = [];
						foreach($utm_fields_arr as $utm_field) {
							$default_value_clean = $utm_field["field_key"];
							$field_match = array_filter($fields, function($field) use ($default_value_clean) {
								return strpos($default_value_clean, $field) === 0;
							});
							if ($field_match) {
								$utm_fields_obj[$utm_field['id']] = array_values($field_match)[0];
							} elseif ($utm_field["type"] == "email") {
								$utm_fields_obj[$utm_field['id']] = "email";
							}
						}
						$form_id_2_fields[$form_id] = $utm_fields_obj;
					}

					$utm_fields = $form_id_2_fields[$form_id];
					$utm_fields_ids = array_keys($utm_fields);

					$utm_fields_ids_str = implode(",", $utm_fields_ids);
					$cur_utm_fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}frm_item_metas WHERE item_id = $item_id AND field_id IN ($utm_fields_ids_str)", ARRAY_A );
					foreach($cur_utm_fields as $cur_utm_field) {
						$cur_key = $utm_fields[$cur_utm_field["field_id"]];
						if (in_array($cur_key, $fields)) {
							$cur_data[$cur_key] = $cur_utm_field["meta_value"];
						}
					}

					array_push($entries_res, $cur_data);
				}
			}
		} else {
			return new WP_Error( 'handl-404', $selected_form_plugin . " is not supported yet. Please contact with us" );
		}
	}
	$entries_res = array_slice($entries_res, 0, 75);
    return $entries_res;
}

if ( ! function_exists( 'handl_report_get_entries' ) ) {
	function handl_report_get_entries() {
		$response = [];
		if (isset($_POST['selected_form_plugin'])){

			$selected_form_plugin = $_POST['selected_form_plugin'];
            $selected_form_ids = (array)$_POST['selected_form_ids'];

			$search_criteria = [];

			$search_criteria['start_date'] = $_POST['selected_start_date'];
			$search_criteria['end_date'] = $_POST['selected_end_date'];
//                    print_r($search_criteria);

            $entries_res = handl_report_get_entries_func($selected_form_plugin, $selected_form_ids, $search_criteria);

			if( is_wp_error( $entries_res ) ) {
				$response['error'] = $entries_res->get_error_message();
			}else{
				if (sizeof($entries_res) > 0){
					$response["entries"] = $entries_res;
				}else{
					$response['error'] = "No data found";
				}
            }
		}else{
			$response['error'] = "No form found";
		}

		wp_send_json($response);
		wp_die();
	}
}
add_action( 'wp_ajax_handl_report_get_entries', 'handl_report_get_entries' );


if ( ! function_exists( 'handl_report_generate_insight' ) ) {
    function handl_report_generate_insight(){
	    settings_errors('your_setting_key');

	    $response = [];

	    $selected_form_plugin = $_POST['selected_form_plugin'];
	    $selected_form_ids = (array)$_POST['selected_form_ids'];
	    $selected_start_date = $_POST['selected_start_date'];
	    $selected_end_date = $_POST['selected_end_date'];

        $search_criteria = [];
	    $search_criteria['start_date'] = $selected_start_date;
	    $search_criteria['end_date'] = $selected_end_date;

	    $report_name = $selected_form_plugin."-id-".implode(",",$selected_form_ids)."-date-".$selected_start_date."-".$selected_end_date;
	    $report_obj = HandLReportGetHandLOptionFromReport("handl_report_".$report_name);

        if ( is_null($report_obj) || is_null($report_obj->insight_report_parsed) ){
	        $entries_res = handl_report_get_entries_func($selected_form_plugin, $selected_form_ids, $search_criteria);

	        if( !is_wp_error( $entries_res ) ) {
		        if (sizeof($entries_res) > 0){
			        $report_table = HandLReportObjectToTable($entries_res);
			        $args = [
				        "body" => [
					        "prompt" => $report_table,
					        "report" => $report_name,
					        "license_key" => get_option( 'license_key_handl-utm-grabber-v3' )
				        ],
				        "timeout" => 45
			        ];

			        $post_resp = wp_remote_post("https://plugin.utmgrabber.com",$args);

			        if (is_wp_error($post_resp)){
				        $response["error"] = $post_resp->get_error_code().": ".$post_resp->get_error_message();
			        }else{
				        $post_response_body = $post_resp["body"];
				        $pattern = '
/
\{              # { character
    (?:         # non-capturing group
        [^{}]   # anything that is not a { or }
        |       # OR
        (?R)    # recurses the entire pattern
    )*          # previous group zero or more times
\}              # } character
/x
';
				        preg_match_all($pattern, $post_response_body, $matches);

                        $response_json = json_decode($matches[0][0], true);
                        if (isset($response_json["error"])){
	                        $response["error"] = $response_json["error"];
//                            add_settings_error("handl_report_insight", "license_error", $response_json["error"]);
                        }else{
	                        $data = [
		                        "selected_form_plugin" => $selected_form_plugin,
		                        "selected_form_ids" => $selected_form_ids,
		                        "selected_start_date" => $selected_start_date,
		                        "selected_end_date" => $selected_end_date,
		                        "insight_report" => $post_response_body,
		                        "insight_report_parsed" => $matches[0][0]
	                        ];

	                        update_option("handl_report_".$report_name, $data,false);

	                        $report_obj = HandLReportGetHandLOptionFromReport("handl_report_".$report_name);
                        }
			        }
		        }
	        }
        }

        if (isset($report_obj->option_id)){
	        $response["report_id"] = $report_obj->option_id;
        }

        if (!isset($response["error"])){
	        $response["success"] = true;
        }

	    wp_send_json($response);
	    wp_die();
    }
}
add_action( 'wp_ajax_handl_report_generate_insight', 'handl_report_generate_insight' );

if ( ! function_exists( 'handl_report_delete_report' ) ) {
	function handl_report_delete_report() {
		$response = [];
		if (isset($_POST['report_name'])){
			$report_name = $_POST['report_name'];
//            $admin_url = add_query_arg(["page" => "handl_analytics"],get_admin_url("","admin.php"));
			delete_option($report_name);
		}else{
			$response['error'] = "No report name found";
		}

		$response["success"] = true;
		wp_send_json($response);
		wp_die();
	}
}
add_action( 'wp_ajax_handl_report_delete_report', 'handl_report_delete_report' );

function HandLReportObjectToTable($entries){
    $table = "";
    foreach ($entries as $id=>$entry){
        if ($id == 0){
            $table .= implode("\t", array_keys($entry))."\n";
        }
	    $table .= implode("\t", array_values($entry))."\n";
    }

    return $table;
}

function HandLReportGetHandLOptionFromReport($report_name){
    global $wpdb;
	return $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}options WHERE option_name = '$report_name'", OBJECT );
}