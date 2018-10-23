<?php
/*
Plugin Name: Modified - CongressLookup
Plugin URI: http://CongressLookup.com/
Description: CongressLookup is powered by data APIs from Google <a href="https://developers.google.com/civic-information/" targer="_blank" >Google Civic Information API</a> and <a href="http://www.google.com/intl/en-US_US/help/terms_maps.html" target="_blank">Google Maps</a>.
Version: 3.1.0a
Author: Constructive Growth LLC, Tom Madrid - Robertson+Partners
Author URI: http://constructivegrowth.net/, https://robertson.partners
License: GNU General Public License v2 or later
*/
if ( !defined('ABSPATH') ) exit();

define('LEGISLATORS_PATH', plugin_dir_url(__FILE__));
define('LEGISLATORS_PATH_BASE', plugin_dir_path(__FILE__));

register_activation_hook( __FILE__, 'CongressLookup_install' );

function CongressLookup_install()
{
	update_option('congress_cache', 1);
	update_option('congress_cache_time', 30);
	update_option('congress_themes', 'modern');
	//update_option('congress_select_choice' , 'all');
	update_option('congress_photos_last_modified', '1307992245');
	update_option('congress_options', array(0=>'title', 1=>'first_name', 2=>'last_name', 3=>'picture', 4=>'chamber', 5=>'state_rank', 6=> 'state_name', 7=> 'website', 8=> 'contact_form'));
}

add_action( 'wp_enqueue_scripts', 'congress_scripts' );
function congress_scripts(){
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script('google_map_api', 'https://maps.googleapis.com/maps/api/js?key=' . get_option('congress_google_map_api_key') . '&libraries=places&callback=initAutocomplete', array('jquery'), false, true ) ;
}

add_action('wp_head', 'legislators_head');
add_shortcode('CongressLookup', 'legislators_start');
add_action('admin_post_congress_admin_shortcode_submit', 'congress_admin_shortcode_submit');
add_action('admin_post_congress_admin_shortcode_delete', 'congress_admin_shortcode_delete');

// frontend ajax call to get congress api data
if ( is_admin() ) {
	add_action( 'wp_ajax_congress_get_api_data', 'congress_get_api_data_callback' );
	add_action( 'wp_ajax_nopriv_congress_get_api_data', 'congress_get_api_data_callback' );
}

add_action('admin_notices', 'congress_admin_notices');     
function congress_admin_notices(){
		
	$screen = get_current_screen();
	if( $screen->id != 'settings_page_mt-cglu' ) return false;

	
	/**********************************************/
	/* Messages after form submission             */
	/**********************************************/
	if( !isset( $_GET['message'] ) ) return false;
	
	switch($_GET['message']){			
		case 'update': 
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Configuration saved.', 'congresslookup' );
			break;
		case 'update_shortcode': 
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Shortcode setting saved.', 'congresslookup' );
			break;
		case 'delete_shortcode': 
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Shortcode deleted.', 'congresslookup' );
			break;
		case 'photos_updated': 
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Photos updated.', 'congresslookup' );
			break;
		case 'error_zip': 
			$class = 'notice warning is-dismissible';
			$message = __( 'An error occured while updating the Photos', 'congresslookup' );
			break;
		case 'error_unzip': 
			$class = 'notice warning is-dismissible';
			$message = __( 'An error occured while updating the Photos', 'congresslookup' );
			break;
	}
	
	if( isset($message) && isset($class) ){
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	}
	return true;		
}

if ( is_admin() ){ // admin actions
	add_action( 'admin_menu', 'qkCongressLookupMenu');
	add_action( 'admin_init', 'qkCongressLookup_registerSettings' );
}else{
	add_filter('widget_text', 'do_shortcode', 1);
} 

function qkCongressLookupMenu(){
	add_options_page('CongressLookup', 'CongressLookup', 'administrator', 'mt-cglu', 'qkCongressLookupSettings'); 
}

function congress_get_default_title($show = 'all'){
	if($show == 'representative'){
		$htext = 'Locate your Representatives';
	}elseif($show == 'senator'){
		$htext = 'Locate Your Senators';
	}else{
		$htext = 'Locate your Senators and Representative';
	}
	
	return $htext;
}

function congress_get_title($line = false){
	if( !$line ) return false;
	
	if( $line['congress_title'] == 'default'){
		$congress_title = congress_get_default_title($line['congress_show']);
	}elseif( $line['congress_title'] == 'empty'){
		$congress_title = '';
	}elseif( $line['congress_title'] == 'custom'){
		$congress_title = $line['congress_title_custom'];
	}
	
	return $congress_title;
}

function congress_get_default_map_center(){
	return '89106 OR 500 S Grand Central Parkway, Las Vegas, NV 89106';
}

function congress_get_default_map_size(){
	return '100%x250';
}

function congress_get_default_map_zoom(){
	return 14;
}

function congress_get_shortcodes($id = false){
	$list = get_option('congress_shortcodes');
	if($id){
		if( isset($list[$id]) ) return $list[$id];
		return false;
	}
	return $list;
}

function congress_update_shortcodes($new_list){
	update_option('congress_shortcodes', $new_list);
	return true;
}

function congress_get_photo($url){
	$url = trim($url);
	
	//return default image for empty url
	if( empty($url) ) return LEGISLATORS_PATH . 'photo/unknown.jpg';
	
	//return original url for non-ssl site
	if( !is_ssl() ) return $url;
	
	$photo = basename($url);
	$cache_key = 'congresslookup_photo_' . $photo;
	$cache_photo = get_transient( $cache_key );

	if ( $cache_photo === false ) {
		
		$request  = wp_remote_get( $url );
		$data_photo = wp_remote_retrieve_body( $request );
		
		$photo_file = LEGISLATORS_PATH_BASE . 'photo/' . $photo;
		$photo_url  = LEGISLATORS_PATH . 'photo/' . $photo;
		file_put_contents( $photo_file, $data_photo );

		set_transient( $cache_key, $photo_url, 1 * DAY_IN_SECONDS );
		$cache_photo = $photo_url;
	}

	return $url;
}

function congress_get_api_data_callback(){
	check_ajax_referer( 'my-special-string', 'security' );

	$atts_id = wp_kses($_POST['atts_id'], '');
	$showon = wp_kses($_POST['showon'], '');
	$address = wp_kses( $_POST['address'], '' );
	
	$data = ($atts_id) ? congress_get_shortcodes($atts_id) : array();
	// turn off this feature
	$data['congress_summary'] = 'no';
	
	// fix bug (senator or representative or all)?
	if( !isset($data['congress_show']) && $showon ) $data['congress_show'] = $showon;
	
	$output = '';
	if( $address ){

		$params = array(
			'key' => wp_kses( get_option( 'google_civic_key' ), '' ),
			'address' => $address
		);


		$myselection = $data['congress_show'];
		if($myselection == 'representative')
			$discard = '&roles=legislatorLowerBody&roles=deputyHeadOfGovernment&roles=executiveCouncil&roles=governmentOfficer&roles=headOfGovernment&roles=headOfState';
        elseif($myselection == 'senator')
	        $discard = '&roles=legislatorUpperBody';
		else
			$discard = '&roles=legislatorLowerBody&roles=legislatorUpperBody';

		$api_call = "https://www.googleapis.com/civicinfo/v2/representatives?" . http_build_query( $params ) . $discard;

		$cache_key = 'congresslookup_' . $address. $discard;
		$congress = get_transient( $cache_key );
		
		if ($congress === false || !get_option('congress_cache') ) {
			
			$request  = wp_remote_get( $api_call );
			$congress = wp_remote_retrieve_body( $request );
		
			set_transient($cache_key, $congress, get_option('congress_cache_time') * MINUTE_IN_SECONDS);
		
		}
			

			$congress = json_decode($congress);
			
			if( isset($data['congress_options']) && is_array($data['congress_options']) ){
				$a = $data['congress_options'];
			}else{
				$a = get_option("congress_options");
			}
			
			if ( !$a ) $a = array();

			
			///$output .= '<pre>' . print_r($congress, 1) . '</pre>';			
			
		    if( !empty($congress->officials) ){
			
                $output .= '<div class="legislators_list">';

                $result_array = array();

                foreach ($congress->officials as $c) {

                    if($c->address[0]->line1 == "The White House") {
                      continue;
                    }
                      

                    //_______________________________________________________
                    ///$result_array[] = (array)$c;
                    $result_array[] = array();
                    $index = sizeof($result_array) - 1;
                    $result_array[$index]['pic'] = $c->photoUrl;
                    $result_array[$index]['full_name'] = esc_html( $c->name );
                    //_______________________________________________________

                    //if( in_array("title", $a) ) {
                        $output .= "<div class='legislator_block'><h3 class='legislator'>" . esc_html( $c->name ) . "</h3>";
                    //}

                    if( in_array("picture", $a) ) {
						$photoUrl = congress_get_photo($c->photoUrl);
                        $output .= "<img class='legislator-pic' src='" . $photoUrl . "' width='70' height='88' alt='' />";
                    }

                    if ( $data['congress_summary'] == 'yes' && !empty($c->office->address[0]->line1) ) {

                        $output .= '<p>Office: ' . $c->office->address[0]->line1 . '</p>';
                        $output .= '<p style="clear: both;"></p>';

                    } else {

                        $output .= '<ul class="legislator-contact">';

						//best approach to get title
						if( in_array("title", $a) ) {
							$the_title = '';
							if($myselection == 'representative'){
								$the_title = 'Representative';
							}elseif($myselection == 'senator'){
								$the_title = 'Senator';
							}else{
								if( isset($c->roles[0]) && !empty($c->roles[0]) ){
									if($c->roles[0] == 'legislatorLowerBody'){
										$the_title = 'Representative';										
									}elseif($c->roles[0] == 'legislatorUpperBody'){
										$the_title = 'Senator';
									}else{
										$the_title = '';
									}
								}elseif( isset($c->urls[0]) && !empty($c->urls[0]) ){
									if( strpos($c->urls[0], 'senate.gov') !== false ){
										$the_title = 'Senator';
									}elseif( strpos($c->urls[0], 'house.gov') !== false ){
										$the_title = 'Representative';										
									}else{
										$the_title = '';
									}									
								}else{
									$the_title = '';
								}								
							}
							
                            $output .= '<li>' . esc_html__('Title','') . ' : ' . $the_title . '</li>';
						}
					
                        if ( !empty($c->party) && in_array("party", $a) ){
                            $output .= '<li>' . esc_html__('Party','') . ' : ' . $c->party . '</li>';
                        }

                        if (!empty($c->phones) && in_array("phone", $a) ){
                            $output .= '<li>' . esc_html__('Phone','') . ' : ' . implode(',',$c->phones) . '</li>';
                        }

                        if (!empty($c->emails) && in_array("email", $a)){
                            if (is_array($c->emails)){
                                $emails = array();
                                foreach ( $c->emails as $email ) {
                                    $emails[] = '<a href="mailto:' . $email . '" target="_blank">' . $email . '</a>';
                                }
                                $output .= '<li>' . esc_html__('Emails','') . ' : ' . implode(',',$emails) . '</li>';
                            }
                        }

                        if (!empty($c->address) ){
                            if (is_array($c->address)){
                                $addresses = array();
                                $zips = array();
                                $states = array();
                                foreach ( $c->address as $address ) {
                                    if(!empty($address->line1)){
                                        $addresses[] = $address->line1;
                                        $zips[] = $address->zip;
                                        $states[] = $address->state;
                                    }
                                }

                                if ( !empty($addresses) && in_array("office", $a) ) {
                                    $output .= '<li>' . esc_html__( 'Office', '' ) . ' : ' . implode( ',', $addresses ) . '</li>';
                                }

                                if ( !empty($zips) && in_array("zip_code", $a) ) {
                                    $output .= '<li>' . esc_html__( 'Zip code', '' ) . ' : ' . implode( ',', $zips ) . '</li>';
                                }

                                if (!empty($states)  && in_array("state_name", $a)  ){
                                    $output .= '<li>' . esc_html__('State','') . ' : ' . implode(',',$states) . '</li>';
                                }
                            }
                        }

                        if ( !empty($c->urls) && in_array("website", $a) ){

                            if (is_array($c->urls)){
                                $urls = array();
                                foreach ( $c->urls as $url ) {
                                    $urltxt = (strlen($url) > 30) ? substr($url,0,30).'...' : $url;
                                    $urls[] = '<a href="' . $url . '" target="_blank">' . $urltxt . '</a>';
                                }
                                $output .= '<li>' . esc_html__('Website','') . ' : ' . implode(',',$urls) . '</li>';
                            }

                        }

                        foreach ( $c->channels AS $key => $value ) {

                            $result_array[ $index ][ $value->type ] = $value->id;

                            $key_translated = $value_translated = '';
                            $key_translated = ucwords( str_replace( '_',
                                ' ', $value->type ) );



                            if ( empty( $value->id ) ) {
                                $value_translated = 'Not Available';

                            } elseif ( strpos( $value->id, 'http:' ) !== FALSE
                                       || strpos( $value->id, 'https:' )
                                          !== FALSE
                            ) {
                                $value_translated = '<a href="'
                                                    . esc_url_raw( $value->id )
                                                    . '" target="_blank">'
                                                    . esc_url( $value->id )
                                                    . '</a>';

                            } else
                                if ( $value->type == 'Facebook' && in_array("facebook_id", $a) ) {
                                    $fbtxt = (strlen($value->id) > 20) ? substr($value->id,0,20).'...' : $value->id;
                                    $value_translated = '<a href="'
                                                        . 'https://www.facebook.com/'
                                                        . $value->id
                                                        . '" target="_blank">'
                                                        . esc_url( 'https://www.facebook.com/'
                                                                   . $fbtxt )
                                                        . '</a>';

                                } elseif ( $value->type == 'Twitter' && in_array("twitter_id", $a) ) {
                                    $value_translated = '<a href="'
                                                        . esc_url_raw( 'https://twitter.com/'
                                                                       . $value->id )
                                                        . '" target="_blank">'
                                                        . esc_url( 'https://twitter.com/'
                                                                   . $value->id )
                                                        . '</a>';

                                } elseif ( $value->type == 'Votesmart' && in_array("votesmart_id", $a) ) {
                                    $value_translated = '<a href="'
                                                        . esc_url_raw( 'http://votesmart.org/candidate/'
                                                                       . $value->id )
                                                        . '" target="_blank">'
                                                        . esc_url( 'http://votesmart.org/candidate/'
                                                                   . $value->id )
                                                        . '</a>';

                                } elseif ( $value->type == 'YouTube' && in_array("youtube_id", $a) ) {
                                    $yttxt = (strlen($value->id) > 20) ? substr($value->id,0,20).'...' : $value->id;
                                    $value_translated = '<a href="'
                                                        . esc_url_raw( 'https://www.youtube.com/user'
                                                                       . $value->id )
                                                        . '" target="_blank">'
                                                        . esc_url_raw( 'http://www.youtube.com/user/'
                                                                       . $yttxt )
                                                        . '</a>';

                                } elseif ( $value->type == 'GooglePlus' && in_array("google_plus_id", $a) ) {
                                    $value_translated = '<a href="'
                                                        . esc_url_raw( 'https://plus.google.com/'
                                                                       . $value->id )
                                                        . '" target="_blank">'
                                                        . esc_url_raw( 'https://plus.google.com/'
                                                                       . $value->id )
                                                        . '</a>';

                                } else {
                                    $value_translated = $key_translated = '';
                                }



                            // Apply filters
							if($key_translated && $value_translated){
								$key_translated = apply_filters( 'congress_field', $key_translated, $value->type );
								$value_translated  = apply_filters( 'congress_value', $value_translated, $value->type, $value->id );
								$output .= apply_filters( 'congress_output',
									'<li>' . $key_translated . ' : '
									. $value_translated . '</li>', $value->type, $value->id,
									$key_translated, $value_translated,
									(array) $c );
							}
                        }

                        $output .= '</ul></div>';
                    }


                }

                // Apply filter
                $results_filter = apply_filters('congress_results', $result_array);
                if( !is_array($results_filter) ){
                    echo $results_filter;
                    // Reset output
                    $output = '';
                }else{
                    $result_array = $results_filter;
                }

                if($result_array){
                    $output .= '<script>';
                    $output .= 'var responseCongressJson = \'' . json_encode($result_array) . '\';';
                    $output .= 'if(typeof jsCongress !== \'undefined\' && jQuery.isFunction(jsCongress)){jsCongress(responseCongressJson);} ';
                    $output .= '</script>';
                }
                $output .= '<hr><p>This information is provided by the <a href="https://developers.google.com/civic-information/" rel="noopener nofollow" target="_blank">Google Civic Information Database</a>.</p><div style="clear:both"></div></div>';
			
		    }
		    else $output .= "Error1";
	}
	else $output .= "Error2";
	
	die($output);
}

function legislators_head() {
?>
	<link href='<?php echo LEGISLATORS_PATH; ?>style.css' rel='stylesheet' type='text/css' />
	<?php if(get_option('congress_themes') && get_option('congress_themes') == 'modern'): ?>
    <link href='<?php echo LEGISLATORS_PATH; ?>light.css' rel='stylesheet' type='text/css' /> <?php endif; ?>
	
  <?php if(get_option('congress_themes') && get_option('congress_themes') == 'custom'): ?>
		<?php if(get_option('congress_themes_css')): ?>
      <!--
			<style type="text/css">
				<?php echo get_option('congress_themes_css'); ?>
			</style>
      -->
		<?php endif; ?>
	<?php endif; ?>

	<script type="text/javascript">
		var ajaxurl = <?php echo json_encode( admin_url( "admin-ajax.php" ) ); ?>;      
		var security = <?php echo json_encode( wp_create_nonce( "my-special-string" ) ); ?>;
	</script>

<?php
}

function legislators_start($atts) {	
	$defaults_array = array(
		'id' => false, 
		'show' => 'all', 
		'congress_show' => 'all', 
		'congress_title' => 'default', 
		'congress_title_custom' => '', 
		'congress_form' => 'yes', 
		'congress_summary' => 'no', 
		'congress_map_center' => congress_get_default_map_center(), 
		'congress_map_center_placeholder' => congress_get_default_map_center(),
		'congress_map_zoom' => congress_get_default_map_zoom(),
		'congress_map_size' => congress_get_default_map_size(), 
		'congress_class' => false, 
		'congress_style' => false, 
		);
		
	//combines attributes & their default value
	$atts = shortcode_atts( $defaults_array, $atts, 'CongressLookup' );

	//read saved shortcode info
	$shortcode_id = 0;
	if( isset($atts['id']) && !empty($atts['id']) ){
		$shortcode_id = $atts['id'];
		$atts = congress_get_shortcodes($atts['id']);
	}
	
	//compatibility with previous version
	if( isset($atts['show']) && !empty($atts['show']) ){
		$atts['congress_show'] = $atts['show'];
	}	
	$show = $atts['congress_show'];
	
	/* $congress_key = get_option('congress_key'); */
	$congress_key = 'temporarily_api_key_from_propublica';

	$html = '';
	
	if($congress_key):
	
		//generate html id for multiple map instances in a page
		$id = date('gis') . rand(1,100);
    
		$schoice = $show;
		$htext = congress_get_title($atts);
				
		$html .= '<form action="#" class="legislators" onsubmit="return getCongressFromAddress(this, ' . $shortcode_id . ', ' . $id . ');">';
		// Dont show title if empty
		if( $htext ) $html .= '<p class="le_head">' . $htext . '</p>';
		if( $atts['congress_form'] == 'yes' ){
			$html .= '  <fieldset id="user-details">	
							<label for="congress_address">Use your zip code, or complete address for best results:</label>
							<input type="hidden" name="showon" value="'.$show.'" id="congress_showon' . $id . '"/>
							<input type="text" name="congress_address" id="congress_address' . $id . '" placeholder="ex: ' . esc_attr($atts['congress_map_center_placeholder']) . '" />
							<input type="submit" value="Find" name="submit" class="submit" />
							<img src="'.LEGISLATORS_PATH.'loader.gif" id="jloader' . $id . '" alt="loading" title="Loading" style="display:none;" />
							<p class="congress_example"><i>ex: 89106 OR 500 S Grand Central Parkway, Las Vegas, NV 89106 </i></p>
						</fieldset>';
		}

		// set div wrapper width & height if not set by html class or inline-style
		if( isset($atts['congress_map_size']) && strpos($atts['congress_map_size'], 'x') !== false ){
			list($width, $height) = explode('x', strtolower($atts['congress_map_size']));
		}else{
			$width = $height = '';
		}
		
		// remove unwanted spaces
		$width = trim($width); $height = trim($height);
		
		// fix width issue
		if($width){
			if( strpos($width, '%') === false && strpos($width, 'px') === false ){
				$width .= 'px';
			}
		}else{
			$width = '80%';
		}
		
		// fix height issue
		if($height){
			if( strpos($height, '%') === false && strpos($height, 'px') === false ){
				$height .= 'px';
			}
		}else{
			$height = '190px';
		}
		
		if( strpos($atts['congress_style'], 'width:') === false ){
			$atts['congress_style'] = 'width:' . $width . ';' . $atts['congress_style'];
		}
		
		if( strpos($atts['congress_style'], 'height:') === false ){
			$atts['congress_style'] = 'height:' . $height . ';' . $atts['congress_style'];
		}  

		if( strpos($atts['congress_style'], 'margin:') === false ){
			$atts['congress_style'] = 'margin:0 auto;' . $atts['congress_style'];
		}  
		
		if( strpos($atts['congress_style'], 'border:') === false ){
			$atts['congress_style'] = 'border:1px solid #EDEDED;' . $atts['congress_style'];
		}  

		
		//div wrapper for the map
		$html .= '<div id="map_canvas' . $id . '"' . ( $atts['congress_class'] ? ' class="' . $atts['congress_class'] . '" ' : '') . ( $atts['congress_style'] ? ' style="' . $atts['congress_style'] . '" ' : '') . '></div>';
    $html .= '<div class="notice">*Note: Many representatives do not have a public email address. We recommend that you visit their website, and copy and paste your letter to them into their contact form. Additionally, you may print your letter and send it in the mail to their office address.</div>';
		$html .= '<div id="congress_holder' . $id . '"></div>';
		$html .= '</form>';

//____________________________________________________________________________________________________________________________________________
		//preparing to call javascript google map api
		$js  = '<script type="text/javascript">';
		$js .= '
			var geocoder' . $id . ', map' . $id . ', marker' . $id . ', congressInitialized' . $id . ' = false;
			function congressInitialize' . $id . '(){
				var latlng = new google.maps.LatLng(36.166081, -115.153088);
				var options = {
					zoom: ' . $atts['congress_map_zoom'] . ',
					center: latlng,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
					
				map' . $id . ' = new google.maps.Map(document.getElementById("map_canvas' . $id . '"), options);
					
				// GEOCODER
				geocoder' . $id . ' = new google.maps.Geocoder();
				// Marker	
				marker' . $id . ' = new google.maps.Marker({
					map: map' . $id . ',
					draggable: true
				});
				
				marker' . $id . '.setPosition(latlng);';

		// Use geocoder to locate the lat-lng of street address
		if( $atts['congress_map_center'] ){
			$js .= '
				//Change to other address
				var address_center = "' . $atts['congress_map_center'] . '";
				geocoder' . $id . '.geocode({"address": address_center}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
							marker' . $id . '.setPosition( results[0].geometry.location );
							map' . $id . '.panTo( results[0].geometry.location );
						';
			
			if( $atts['congress_form'] == 'no' ){
			$js .= '
							// List congress members
							jQuery("#congress_address' . $id . '").val(results[0].formatted_address);
							getCongressData(results[0].geometry.location.lat(),  results[0].geometry.location.lng(), ' . $shortcode_id . ', ' . $id . ');
						';
			}
			
			$js .= '			
						}
					}
				});
			';
		}	
		
		$js .= '		
				//Add listener to marker for reverse geocoding
				google.maps.event.addListener(marker' . $id . ', "dragend", function (event) {
					jQuery("#jloader' . $id . '").show();
					geocoder' . $id . '.geocode({"latLng": marker' . $id . '.getPosition()}, function(results, status) {
						jQuery("#jloader' . $id . '").hide();
						if (status == google.maps.GeocoderStatus.OK) {
							if (results[0]) {
								jQuery("#congress_address' . $id . '").val(results[0].formatted_address);
								getCongressData(results[0].formatted_address, ' . $shortcode_id . ', ' . $id . ');
							}
						}
					});
				});			

				jQuery("congress_address' . $id . '").keyup(function(){
					jQuery("congress_address' . $id . '").removeClass();
				});
			}
			';

		if( !defined('CONGRESS_INITIAL_CALL') ) {
			$js .= 'function getCongressFromAddress(f, atts_id, id){
				
						var address = jQuery("#congress_address" + id).val();
						var showon = jQuery("#congress_showon" + id).val();
						
						if(!address || address == "" || address.length == 0)
						{
							jQuery("#congress_address" + id).addClass( "error" );	
							jQuery("#congress_holder" + id).html( "Missing Address!" );
							return false;
						}
						
						jQuery("#jloader" + id).show();	 
						
						window["geocoder" + id].geocode( { "address": address}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK)
							{
								window["marker" + id].setPosition(results[0].geometry.location);
								window["map" + id].setCenter(results[0].geometry.location);
								
								getCongressData(address, atts_id, id);
								jQuery("#jloader" + id).hide();	
							}
							else
							{
								jQuery("#congress_holder" + id).html( "Couldn\'t find the address" );
								jQuery("#jloader" + id).hide();	
							}
						});
						
						return false;
					}
					';
		}

		if( !defined('CONGRESS_INITIAL_CALL') ) {
			$js .= 'function getCongressData(address, atts_id, id){
						jQuery("#congress_holder" + id).html( jQuery("<img>",{id:"jloader_"+id,src: jQuery("#jloader" + id).attr("src") }) );
						
						var showon = jQuery("#congress_showon" + id).val();
						var data = {
							action: "congress_get_api_data",
							security : security,
							atts_id: atts_id,
							showon: showon,
							address: address
						};
						jQuery.post(ajaxurl, data, function(response) {
							if(response != "Error1" && response != "Error2"){
								jQuery("#congress_holder" + id).html( response );
							}else{
								jQuery("#congress_holder" + id).html( "Address or zip code not found. Try using your complete street address for best results. This information is provided by the <a href=\"https://developers.google.com/civic-information/\" rel=\"noopener nofollow\" target=\"_blank\">Google Civic Information Database</a>." );
							}
						});						
					}
					';
		}
		
		if( !defined('CONGRESS_INITIAL_CALL') ) define('CONGRESS_INITIAL_CALL', true);
		
		$js .= 'jQuery(document).ready(function(){congressInitialize' . $id . '();});';			

    if(isset($atts['congress_js']) && $atts['congress_js']){$js .= stripslashes($atts['congress_js']);}
    
    /* Autocomplete */

    $js .= "\r\n";
    $js .= "var placeSearch, autocomplete;\r\n";
    $js .= "function initAutocomplete() {
              var input = document.getElementById('congress_address".$id."');
              autocomplete = new google.maps.places.Autocomplete(input, {types: ['address']});
              autocomplete.setFields(['formatted_address']);
              autocomplete.addListener('place_changed', fillInAddress);
            }\r\n";
    
    $js .= "function fillInAddress() {
              // Get the place details from the autocomplete object.
              var place = autocomplete.getPlace();
            }\r\n";
    $js .= '</script>';
		$html .= $js;
//____________________________________________________________________________________________________________________________________________

			
	else:
		 $html .='<form action="#" class="legislators"> 
			<p class="le_head">API Key missing, please update it</p>
			</form>';
	endif; 
	
	return $html;
}

function qkCongressCheckOptions($t, $opt = false)
{
	if( ! is_array($opt) ){
		// Use default options if not supplied
		$opt = get_option('congress_options');
	}

	if(is_array($opt))
	{
		foreach($opt AS $key=>$value){
			if($value == $t) return 'checked="checked"';
		}
	}
	
	return('');
}

function GetRemoteLastModified( $uri )
{
    // default
    $unixtime = 0;
    
    $fp = @fopen( $uri, "r" );
    if( !$fp ) {return;}
    
    $MetaData = stream_get_meta_data( $fp );
        
    foreach( $MetaData['wrapper_data'] as $response )
    {
        // case: redirection
        if( substr( strtolower($response), 0, 10 ) == 'location: ' )
        {
            $newUri = substr( $response, 10 );
            fclose( $fp );
            return GetRemoteLastModified( $newUri );
        }
        // case: last-modified
        elseif( substr( strtolower($response), 0, 15 ) == 'last-modified: ' )
        {
            $unixtime = strtotime( substr($response, 15) );
            break;
        }
    }
    fclose( $fp );
    return $unixtime;
}

function qkCongressLookup_registerSettings() { // whitelist options
	register_setting( 'qkCongressLookup-group', 'google_civic_key' );
	register_setting( 'qkCongressLookup-group', 'congress_google_map_api_key' );
	register_setting( 'qkCongressLookup-group', 'congress_cache' );
	register_setting( 'qkCongressLookup-group', 'congress_cache_time' );
	register_setting( 'qkCongressLookup-group', 'congress_options' );
	register_setting( 'qkCongressLookup-group', 'congress_themes' );
	register_setting( 'qkCongressLookup-group', 'congress_themes_css' );
	//register_setting( 'qkCongressLookup-group', 'congress_select_choice' );
}

function qkCongressLookupSettings() {
	//get shortcodes
	$list = congress_get_shortcodes();
	$total_list = (is_array($list)) ? sizeof($list) : 0;
	$total_list = ($total_list) ? '(' . number_format($total_list) . ')' : '';
		
	//available tabs
	$array_tabs = array(
		'config' 		=> 'Configuration', 
		'shortcode' 	=> sprintf('Custom Shortcode', $total_list), 
	);

	//set default active tab
	if( !isset($_GET['tab']) ){
		$active_tab = 'config';
	}else{
		$active_tab = ( in_array($_GET['tab'], array_keys($array_tabs) ) ) ? wp_kses($_GET['tab'], '') : 'config'; 
	}	

?>
	<div class="wrap">
		<h2><?php echo __('CongressLookup', 'congresslookup');?> <a href="<?php echo admin_url('options-general.php?page=mt-cglu&tab=shortcode&id=0');?>" class="page-title-action">Add Shortcode</a></h2>

		<?php if (isset($_REQUEST['updated']) && $_REQUEST['updated'] == 'true') { ?>
		<div id="message" class="updated fade"><p><strong>Settings Updated</strong></p></div>
		<?php  } ?>
	
		<h2 class="nav-tab-wrapper">
			<?php
			foreach($array_tabs as $key => $value){
				echo '<a href="' . admin_url('options-general.php?page=mt-cglu&tab=' . $key) . '" class="nav-tab ' . ($active_tab == $key ? 'nav-tab-active' : '') . '">' . $value . '</a>';
			}
			?>
		</h2>
		
		<div class="content-tab-wrapper" id="congress-content">
		<?php
		if( $active_tab == 'config' ){
			congress_admin_config();
		}elseif( $active_tab == 'shortcode' ){
			congress_admin_shortcode();
		}else{
			congress_admin_config();
		}
		?>
		</div>
	</div>
<style>
.content-tab-wrapper{border: solid 1px #ccc; border-top: none; padding: 10px; max-width: 1125px;}
#congress-content .congress_row{}
#congress-content .congress_row:hover{background-color: #F3F3F3;}
#congress-content .row_odd{background-color: #EEE;}
#congress-content fieldset{border: dotted 1px #AAA; padding: 10px; margin: 5px 5px 20px 5px;}
#congress-content fieldset legend{font-weight: bold; font-size: initial;}
#congress-content #default_title_id{font-style: italic; font-weight: bold;}
#congress-content h1{margin: 0 0 25px 10px; color: blue;}
</style>
<?php
}

function congress_admin_config(){
	if( get_option('congress_themes') == 'custom' && !get_option('congress_themes_css'))
	{
		update_option('congress_themes_css', @file_get_contents(LEGISLATORS_PATH_BASE.'custom.css'));
	}
?>
		<form name="addnew" method="post" action="options.php">
			<?php settings_fields('qkCongressLookup-group');?>

			<table class="form-table">
				<tbody>

                <tr valign="top">
                    <th scope="row"><label for="google_civic_key">Google Civic
                            Key:</label></th>
                    <td colspan="2">
                        <input name="google_civic_key" type="text" size="45"
                               value="<?php echo get_option( 'google_civic_key' ); ?>">
                        <p>Get your API Key at the <a
                                    href="https://console.developers.google.com/apis/credentials"
                                    target="_blank">Google APIs</a></p>
                    </td>
                </tr>
			
				<tr valign="top">
					<th scope="row"><label for="congress_google_map_api_key">Google Map API Key:</label></th>
					<td colspan="2">
						<input type="text" class="regular-text" name="congress_google_map_api_key" value="<?php echo esc_attr( get_option('congress_google_map_api_key') ); ?>" />
						<p>Get Google Map API Key <a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&keyType=CLIENT_SIDE&reusekey=true" target="_blank">here</a></p>
					</td>
				</tr>
				<tr valign="top"><td colspan="3"><hr></td></tr>
				
				<tr valign="top">
					<th scope="row"><label>What to display?:</label></th>
					<td colspan="2">
						<table>
							<tbody>
								<tr>								 
									<td>
										<input name="congress_options[]" type="checkbox" id="title" value="title" <?php echo qkCongressCheckOptions("title"); ?>>&nbsp;<label for="title">Title</label> <br />
										<input name="congress_options[]" type="checkbox" id="picture" value="picture" <?php echo qkCongressCheckOptions("picture"); ?>>&nbsp;<label for="picture">Picture</label> <br />
										<input name="congress_options[]" type="checkbox" id="office" value="office" <?php echo qkCongressCheckOptions("office"); ?>>&nbsp;<label for="last_name">Office</label> <br />
										<input name="congress_options[]" type="checkbox" id="phone" value="phone" <?php echo qkCongressCheckOptions("phone"); ?>>&nbsp;<label for="phone">Phone</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="zip_code" value="zip_code" <?php echo qkCongressCheckOptions("zip_code"); ?>>&nbsp;<label for="zip_code">Zip code</label> <br />
										<input name="congress_options[]" type="checkbox" id="party" value="party" <?php echo qkCongressCheckOptions("party"); ?>>&nbsp;<label for="party">Party</label> <br />
										<input name="congress_options[]" type="checkbox" id="state_name" value="state_name" <?php echo qkCongressCheckOptions("state_name"); ?>>&nbsp;<label for="state_name">State&nbsp;Name</label>

                                    </td>
									<td>&nbsp;&nbsp;</td>
									<td>
                                        <input name="congress_options[]" type="checkbox" id="contact_form" value="contact_form" <?php echo qkCongressCheckOptions("contact_form"); ?>>&nbsp;<label for="contact_form">Contact&nbsp;Form</label> <br />
										<input name="congress_options[]" type="checkbox" id="email" value="email" <?php echo qkCongressCheckOptions("email"); ?>>&nbsp;<label for="email">Email</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="website" value="website" <?php echo qkCongressCheckOptions("website"); ?>>&nbsp;<label for="website">Website</label> <br />
										<input name="congress_options[]" type="checkbox" id="facebook_id" value="facebook_id" <?php echo qkCongressCheckOptions("facebook_id"); ?>>&nbsp;<label for="facebook_id">Facebook&nbsp;ID</label> <br />
                                        <input name="congress_options[]" type="checkbox" id="youtube_id" value="youtube_id" <?php echo qkCongressCheckOptions("youtube_id"); ?>>&nbsp;<label for="youtube_id">Youtube&nbsp;ID</label><br />
                                        <input name="congress_options[]" type="checkbox" id="twitter_id" value="twitter_id" <?php echo qkCongressCheckOptions("twitter_id"); ?>>&nbsp;<label for="twitter_id">Twitter&nbsp;ID</label><br />
                                        <input name="congress_options[]" type="checkbox" id="google_plus_id" value="google_plus_id" <?php echo qkCongressCheckOptions("google_plus_id"); ?>>&nbsp;<label for="google_plus_id">Google Plus ID</label><br />
                                    </td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr valign="top"><td colspan="3"><hr></td></tr>
				<tr valign="top">
					<th scope="row"><label>Cache:</label></th>
					<td>
						<p>Enable this to cache the data returned by the API, to reduce the number of requests, and for fast loading. Select for how many minutes you would like the data to be cached &amp; saved</p>
						<input name="congress_cache" id="congress_cache" type="checkbox" value="1" <?php echo checked(get_option('congress_cache'),1); ?>>&nbsp;<label for="congress_cache">Enable&nbsp;cache?</label>
						<p>
							<label for="congress_cache_time">Cache time:</label>
							<input name="congress_cache_time" id="congress_cache_time" type="text" size="5" style="width:40px" value="<?php echo get_option('congress_cache_time'); ?>"> <small><i>minutes</i></small>
						</p>
					</td>
                    <td valign="top" rowspan="2">
						<div style="background-color: #FFFFE0;border: 1px solid #E8E7AE;padding: 10px;position: relative;text-align: center;top: 10px;width: 200px;">
							<p>CongressLookup is free to use.  Please consider donating to help support the continued development of this plugin.  Thanks!</p>
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                            <input type="hidden" name="cmd" value="_s-xclick">
                            <input type="hidden" name="hosted_button_id" value="RDW8YBV83S882">
                            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                            </form>

						</div>
                    </td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="congress_themes">Theme:</label></th>
					<td>
						<select name="congress_themes" id="congress_themes">
							<option value="" <?php echo selected(get_option('congress_themes'),''); ?>>No Theme</option>
							<option value="modern" <?php echo selected(get_option('congress_themes'),'modern'); ?>>Modern</option>
							<option value="custom" <?php echo selected(get_option('congress_themes'),'custom'); ?>>Custom</option>
						</select>

						<div <?php if(get_option('congress_themes') != 'custom'): echo ' style="display:none" '; endif; ?>id="custom_css_div">
						<p><label for="congress_themes_css">Custom Css Code:</label></p>
						<p>
							<textarea name="congress_themes_css" id="congress_themes_css" rows="1" cols="1" style="width:80%;height:150px;">
<?php if(get_option('congress_themes_css')):  echo get_option('congress_themes_css'); else: echo @file_get_contents(LEGISLATORS_PATH_BASE.'custom.css'); endif; ?>
							</textarea>
						</p>
						<p><b>Demo :</b></p>
						<p>
							<iframe src="<?php echo LEGISLATORS_PATH; ?>iframe.html?<?php echo time(); ?>" id="form_demo" style="width:85%;height:200px;border:0"></iframe>
						</p>
						</div>
					</td>
				</tr>

				<tr valign="top"><td colspan="3"><hr></td></tr>
				<tr valign="top">
					<th scope="row"><label>Short Codes:</label></th>
					<td colspan="2">
					  <table>
						 <tr> <th> For Searching All: </th> <td><input type="text" name="tmp1" value="[CongressLookup]" style="border:none; background:transparent; box-shadow:none; width: 300px;"></td> </tr>
						 <tr> <th> For Searching Senators only: </th> <td><input type="text" name="tmp1" value="[CongressLookup show='senator']" style="border:none; background:transparent; box-shadow:none; width: 300px;"></td> </tr>
						 <tr> <th> For Searching Representatives only: </th> <td><input type="text" name="tmp1" value="[CongressLookup show='representative']" style="border:none; background:transparent; box-shadow:none; width: 300px;"></td> </tr>
					  </table>
					</td>
				</tr>
				<tr valign="top"><td colspan="3"><hr></td></tr>
				<tr valign="top"><td>&nbsp;</td><td colspan="2"><?php submit_button(); ?></td></tr>
				</tbody>
			</table>
		
		</form>			
<script type="text/javascript">
function iframeLoaded() {
    var $frame = jQuery("#form_demo");
	var	contents = $frame.contents(),
	styleTag = jQuery('<style></style>').appendTo(contents.find('head'));
	
	styleTag.text(jQuery('#congress_themes_css').val());
	
	jQuery('#congress_themes_css').keyup(function() {
		styleTag.text(jQuery(this).val());
	});
}
jQuery(document).ready(function($){
	if($('#congress_themes_css').length > 0)
	{
		$("#congress_themes").change(function(){
			if($(this).val() == "custom")
				$('#custom_css_div').show();
			else
				$('#custom_css_div').hide();
		});
	}
});
</script>	
	
<?php	
}

function congress_admin_shortcode(){
	if( isset($_GET['id']) ){
		congress_admin_shortcode_wizard( $_GET['id'] );
	}else{
		congress_admin_shortcode_list();
	}
}

function congress_admin_shortcode_submit(){
	if($_POST && isset($_POST['form_id'])){
		$form_id = ( isset($_POST['form_id']) ) ? $_POST['form_id'] : false;
		$congress_show = ( isset($_POST['congress_show']) ) ? wp_kses($_POST['congress_show'], '') : 'all'; //all, senator, representative
		$congress_title = ( isset($_POST['congress_title']) ) ? wp_kses($_POST['congress_title'], '') : 'default'; //default, empty, custom
		$congress_title_custom = ( isset($_POST['congress_title_custom']) ) ? wp_kses($_POST['congress_title_custom'], '') : '';
		$congress_form = ( isset($_POST['congress_form']) ) ? wp_kses($_POST['congress_form'], '') : 'yes'; //yes, no
		$congress_options = ( isset($_POST['congress_options']) ) ? $_POST['congress_options'] : false; //yes, no
		
		$congress_map_center = ( isset($_POST['congress_map_center']) ) ? $_POST['congress_map_center'] : '';
		$congress_map_center_placeholder = ( isset($_POST['congress_map_center_placeholder']) ) ? $_POST['congress_map_center_placeholder'] : '';
		$congress_map_zoom = ( isset($_POST['congress_map_zoom']) ) ? (int)$_POST['congress_map_zoom'] : 15;
		//Fix zero zoom factor
		if( !$congress_map_zoom ) $congress_map_zoom = congress_get_default_map_zoom();
		
		$congress_map_size = ( isset($_POST['congress_map_size']) ) ? $_POST['congress_map_size'] : false;
		$congress_class = ( isset($_POST['congress_class']) ) ? $_POST['congress_class'] : false;
		$congress_style = ( isset($_POST['congress_style']) ) ? $_POST['congress_style'] : false;
		
		//Add custom javascript
		$congress_js = ( isset($_POST['congress_js']) ) ? $_POST['congress_js'] : false;
		
		//update congress shortcode
		$list = congress_get_shortcodes();
		if(!is_array($list)) $list = array();
		
		if(!$form_id) $form_id = date('YmdHis');
		
		$list[$form_id] = array(
			'congress_show' => $congress_show, 
			'congress_title' => $congress_title, 
			'congress_title_custom' => $congress_title_custom, 
			'congress_form' => $congress_form, 
			'congress_options' => $congress_options, 
			'congress_map_center' => $congress_map_center, 
			'congress_map_center_placeholder' => $congress_map_center_placeholder,
			'congress_map_zoom' => $congress_map_zoom,
			'congress_map_size' => $congress_map_size, 
			'congress_class' => $congress_class, 
			'congress_style' => $congress_style, 
			'congress_js' => $congress_js, 
			);
			
		congress_update_shortcodes($list);
		
		wp_redirect(admin_url('options-general.php?page=mt-cglu&tab=shortcode&message=update_shortcode')); exit;			
	}
	
	wp_redirect(admin_url('options-general.php?page=mt-cglu&tab=shortcode')); exit;	
}

function congress_admin_shortcode_delete(){
	if($_GET && isset($_GET['id']) && $_GET['id'] > 0){
		$list = congress_get_shortcodes();
		if( is_array($list) && isset($list[$_GET['id']]) ){
			unset($list[$_GET['id']]);
			congress_update_shortcodes($list);
			wp_redirect(admin_url('options-general.php?page=mt-cglu&tab=shortcode&message=delete_shortcode')); exit;
		}
	}
	wp_redirect(admin_url('options-general.php?page=mt-cglu&tab=shortcode')); exit;		
}

function congress_admin_shortcode_wizard($id){
	$data = false;
	if($id){
		$data = congress_get_shortcodes($id);
	}
	
	if( !is_array($data) ) $data = array();
	
	$default_title = '';
	if( (isset($data['congress_title']) && $data['congress_title'] == 'default') || !isset($data['congress_title']) ){
		$default_title = congress_get_default_title($data['congress_show']);
	}

	// custom "what to display" per shortcode
	if( ! isset($data['congress_options']) ){
		$data['congress_options'] = false;
	}
?>
		<h1>
		<?php if($id): ?>
		Edit Shortcode ID: <?php echo wp_kses($id, '');?>
		<?php else: ?>
		Add Shortcode
		<?php endif;?>
		</h1>
		<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
		<input type="hidden" name="action" value="congress_admin_shortcode_submit">
		<input type="hidden" name="form_id" value="<?php echo $id;?>">
		
		<fieldset>
			<legend>Please Select What to Search / Show?</legend>
			<label><input name="congress_show" type="radio" value="senator" <?php echo ( isset($data['congress_show']) && $data['congress_show'] == 'senator') ? 'checked' : '';?> /> <?php echo __('Searching Senators only', 'congresslookup');?></label><br>
			<label><input name="congress_show" type="radio" value="representative" <?php echo ( isset($data['congress_show']) && $data['congress_show'] == 'representative') ? 'checked' : '';?> /> <?php echo __('Searching Representatives  only', 'congresslookup');?></label><br>
			<label><input name="congress_show" type="radio" value="all" <?php echo ( (isset($data['congress_show']) && $data['congress_show'] == 'all') || !isset($data['congress_show']) ) ? 'checked' : '';?> /> <?php echo __('Searching both Senators & Representatives (default)', 'congresslookup');?></label><br>
		</fieldset>

		<fieldset>
			<legend>Title:</legend>
			<label><input name="congress_title" type="radio" value="default" <?php echo ( (isset($data['congress_title']) && $data['congress_title'] == 'default') || !isset($data['congress_title']) ) ? 'checked' : '';?> /> Use default title: <span id="default_title_id"><?php echo $default_title;?></span></label><br>
			<label><input name="congress_title" type="radio" value="empty" <?php echo ( isset($data['congress_title']) && $data['congress_title'] == 'empty') ? 'checked' : '';?> /> <?php echo __('No title', 'congresslookup');?></label><br>
			<label><input name="congress_title" type="radio" value="custom" <?php echo ( isset($data['congress_title']) && $data['congress_title'] == 'custom' ) ? 'checked' : '';?> /> <?php echo __('Use custom title:', 'congresslookup');?> <input type="text" class="regular-text" name="congress_title_custom" value="<?php echo esc_attr($data['congress_title_custom']);?>"></label><br>
		
		</fieldset>

		<fieldset>
			<legend>Display Search Address Form?</legend>
			<label><input name="congress_form" type="radio" value="yes" <?php echo ( (isset($data['congress_form']) && $data['congress_form'] == 'yes') || !isset($data['congress_form']) ) ? 'checked' : '';?> /> <?php echo __('Yes please (default)', 'congresslookup');?></label><br>
			<label><input name="congress_form" type="radio" value="no" <?php echo ( isset($data['congress_form']) && $data['congress_form'] == 'no') ? 'checked' : '';?> /> <?php echo __('No thanks', 'congresslookup');?></label><br>
		</fieldset>

		<fieldset>
			<legend>What to display?</legend>
			<table>
				<tbody>
					<tr>								 
						<td>
							<input name="congress_options[]" type="checkbox" id="title" value="title" <?php echo qkCongressCheckOptions("title", $data['congress_options']); ?>>&nbsp;<label for="title">Title</label> <br />
                            <input name="congress_options[]" type="checkbox" id="party" value="party" <?php echo qkCongressCheckOptions("party", $data['congress_options']); ?>>&nbsp;<label for="party">Party</label> <br />
							<input name="congress_options[]" type="checkbox" id="picture" value="picture" <?php echo qkCongressCheckOptions("picture", $data['congress_options']); ?>>&nbsp;<label for="picture">Picture</label> <br />
                            <input name="congress_options[]" type="checkbox" id="phone" value="phone" <?php echo qkCongressCheckOptions("phone", $data['congress_options']); ?>>&nbsp;<label for="phone">Phone</label> <br />
                            <input name="congress_options[]" type="checkbox" id="office" value="office" <?php echo qkCongressCheckOptions("office", $data['congress_options']); ?>>&nbsp;<label for="office">Office</label> <br />
                            <input name="congress_options[]" type="checkbox" id="zip_code" value="zip_code" <?php echo qkCongressCheckOptions("zip_code", $data['congress_options']); ?>>&nbsp;<label for="zip_code">Zip code</label> <br />
                            <input name="congress_options[]" type="checkbox" id="state_name" value="state_name" <?php echo qkCongressCheckOptions("state_name", $data['congress_options']); ?>>&nbsp;<label for="state_name">State&nbsp;Name</label>

                        </td>
						<td>&nbsp;&nbsp;</td>
						<td>
                            <input name="congress_options[]" type="checkbox" id="contact_form" value="contact_form" <?php echo qkCongressCheckOptions("contact_form", $data['congress_options']); ?>>&nbsp;<label for="contact_form">Contact&nbsp;Form</label> <br />
                            <input name="congress_options[]" type="checkbox" id="email" value="email" <?php echo qkCongressCheckOptions("email", $data['congress_options']); ?>>&nbsp;<label for="picture">Email</label> <br />
                            <input name="congress_options[]" type="checkbox" id="website" value="website" <?php echo qkCongressCheckOptions("website", $data['congress_options']); ?>>&nbsp;<label for="website">Website</label> <br />
                            <input name="congress_options[]" type="checkbox" id="facebook_id" value="facebook_id" <?php echo qkCongressCheckOptions("facebook_id", $data['congress_options']); ?>>&nbsp;<label for="facebook_id">Facebook&nbsp;ID</label> <br />
                            <input name="congress_options[]" type="checkbox" id="youtube_id" value="youtube_id" <?php echo qkCongressCheckOptions("youtube_id", $data['congress_options']); ?>>&nbsp;<label for="youtube_id">Youtube&nbsp;ID</label><br />
                            <input name="congress_options[]" type="checkbox" id="twitter_id" value="twitter_id" <?php echo qkCongressCheckOptions("twitter_id", $data['congress_options']); ?>>&nbsp;<label for="twitter_id">Twitter&nbsp;ID</label> <br />
                            <input name="congress_options[]" type="checkbox" id="google_plus_id" value="google_plus_id" <?php echo qkCongressCheckOptions("google_plus_id", $data['congress_options']); ?>>&nbsp;<label for="google_plus_id">Google Plus ID</label> <br />

                        </td>
						<td>&nbsp;&nbsp;</td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<fieldset>
			<legend>Google Map Settings</legend>
			<p><strong>Default Center Address:</strong><br>
			<input type="text" class="regular-text" name="congress_map_center" value="<?php echo esc_attr($data['congress_map_center']);?>"><br>
			<em>Default: <?php echo congress_get_default_map_center();?></em></p>

            <p><strong>Address Placeholder:</strong><br>
                <?php $placeholder = !empty($data['congress_map_center_placeholder']) ? $data['congress_map_center_placeholder'] : ''; ?>
                <input type="text" class="regular-text" name="congress_map_center_placeholder" value="<?php echo esc_attr( $placeholder );?>"><br>
                <em>Example: <?php echo congress_get_default_map_center();?></em></p>
			
			<p><strong>Map Size:</strong><br>
			<input type="text" class="regular-text" name="congress_map_size" value="<?php echo esc_attr($data['congress_map_size']);?>" style="width:100px;"><br>
			<em>Set map size here. You may use % for percentage.<br>Default value is <?php echo congress_get_default_map_size();?> (80% for width and 190 pixel for height).</em>
			
			<p><strong>Zoom Level:</strong><br>
			<input type="text" class="regular-text" name="congress_map_zoom" value="<?php echo esc_attr($data['congress_map_zoom']);?>" style="width:100px;"><br>
			<em>Value from 0 (world) - 20 (buildings), default: <?php echo congress_get_default_map_zoom();?></em></p>
		</fieldset>

		<fieldset>
			<legend>Advance Settings</legend>
			<p><strong>JavaScript:</strong><br>
			<textarea name="congress_js" style="width:100%; height:300px;"><?php echo (isset($data['congress_js'])) ? stripslashes($data['congress_js']) : '';?></textarea>
		</fieldset>
		
		<p>
		<?php if($id): ?>
		<input type="submit" class="button button-primary" value="Update Shortcode">
		<?php else: ?>
		<input type="submit" class="button button-primary" value="Create New Shortcode">
		<?php endif;?>
		</p>
		
		</form>
			
<script>
jQuery(document).ready(function($){
	$('input[name="congress_show"]').click(function(){
		if( $(this).val() == 'senator' ){
			$('#default_title_id').html('<?php echo congress_get_default_title('senator');?>');
		}else if( $(this).val() == 'representative' ){
			$('#default_title_id').html('<?php echo congress_get_default_title('representative');?>');
		}else{
			$('#default_title_id').html('<?php echo congress_get_default_title('all');?>');
		}
	});
	$('input[name="congress_title_custom"]').focus(function(){
		$("input[name=congress_title][value='custom']").prop("checked",true);
	});
});
</script>	

<?php
}

function congress_admin_shortcode_list(){
?>
    	<table class="widefat" cellspacing="0">
    		<thead>
			<tr>
				<th scope="col" class="manage-column" style="width:10%;"><?php echo __('ID', 'congresslookup');?></th>
				<th scope="col" class="manage-column" style="width:30%;"><?php echo __('Shortcode', 'congresslookup');?></th>
				<th scope="col" class="manage-column" style=""><?php echo __('Show', 'congresslookup');?></th>
				<th scope="col" class="manage-column" style=""><?php echo __('Title', 'congresslookup');?></th>
				<th scope="col" class="manage-column" style="text-align:center"><?php echo __('Actions', 'congresslookup');?></th>
			</tr>
    		</thead>
    		<tbody>
<?php
	$query = congress_get_shortcodes();
	//echo '<pre>'; print_r($query); echo '</pre>';
    if($query){
        $no = 0;
        foreach ($query as $id => $line) {
            $no++;
            $tr_class = 'congress_row';
			$tr_class .= ($no % 2) ? ' row_odd' : '';
			
			$actions = '<a onclick="return confirm(\'Are you sure want to delete shortcode # ' . esc_attr($id) . '?\');" href="' . admin_url('admin-post.php?action=congress_admin_shortcode_delete&id=' . esc_attr($id) ) . '">' . __('Delete', 'congresslookup') . '</a>';
			$actions .= ' | <a href="' . admin_url('options-general.php?page=mt-cglu&tab=shortcode&id=' . esc_attr($id) ) . '">' . __('Edit', 'congresslookup') . '</a>';

			$congress_title = congress_get_title($line);
?>        
        		<tr class="<?php echo $tr_class;?>">
        			<td><?php echo $id;?></td>
        			<td><?php echo '[CongressLookup id="' . $id . '"]'; ?></td>
        			<td><?php echo $line['congress_show']; ?></td>
        			<td><?php echo $congress_title; ?></td>
        			<td align="center"><?php echo $actions;?></td>
        		</tr>
<?php        
        }
    }
?>
            </tbody>
        </table>
<?php		
} 
?>