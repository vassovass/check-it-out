<?php

function borlabs_ok_to_go( $good2go ){
    if (function_exists('BorlabsCookieHelper') && $good2go['good2go'] === 1){

		$cookie_data = BorlabsCookieHelper()->getCookieData('handl-utm-grabber');
		if ( !$cookie_data ) {
			$cookie_data = BorlabsCookieHelper()->getCookieData('handl');
		}

		if ($cookie_data &&
		    isset($cookie_data['status']) &&
		    $cookie_data['status']){
			if (
				( BorlabsCookieHelper()->gaveConsent('handl-utm-grabber') || BorlabsCookieHelper()->gaveConsent('handl') ) ){
				$good2go['good2go'] = 1;
			}else{
				$good2go['good2go'] = 0;
			}
		}
    }elseif(defined("BORLABS_COOKIE_VERSION")){
		if (version_compare(BORLABS_COOKIE_VERSION, "3.0" ,">=")){
			global $wpdb;
			$table_name = $wpdb->prefix."borlabs_cookie_services";
			$result = $wpdb->get_results ( "SELECT * FROM $table_name WHERE `key` = 'handl-utm-grabber' AND `status` = 1");
			if (sizeof($result) > 0) {
				$good2go['good2go'] = 0;
				if ( isset( $_COOKIE['borlabs-cookie'] ) ) {
					$pluginCookie = json_decode( stripslashes( $_COOKIE['borlabs-cookie'] ) );
					$my_consents  = $pluginCookie->consents;
					if ( isset( $my_consents->marketing ) ) {
						if ( in_array( "handl-utm-grabber", $my_consents->marketing ) ) {
							$good2go['good2go'] = 1;
						} else {
							$good2go['good2go'] = 0;
						}
					}
				}
			}
        }
    }
    return $good2go;
}
add_filter( 'is_ok_to_capture_utms', 'borlabs_ok_to_go', 10, 1 );
