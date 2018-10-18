<?php

	if(isset($_GET['lat']) && isset($_GET['lon']))
	{
		require_once('../../../wp-load.php');
		$url = plugin_dir_url(__FILE__);
		
		$api_call = "https://congress.api.sunlightfoundation.com/legislators/locate?apikey=". wp_kses(get_option('congress_key'), '') . "&latitude=" . wp_kses($_GET['lat'], '') . "&longitude=". wp_kses($_GET['lon'], '');

		if(get_option("congress_cache"))
		{
			require 'API_cache.php';
			$cache_file = 'cache/'.md5($api_call).'.json';
			$cache_for = get_option("congress_cache_time"); // cache time in minutes

			$api_cache = new API_cache ($api_call, $cache_for, $cache_file);
			$congress = $api_cache->get_api_cache();
		}
		else
			$congress = @file_get_contents($api_call);
		
		if($congress )
		{
			$congress = json_decode($congress);
			
			if(get_option("congress_options"))
				$a = get_option("congress_options");
			else
				$a = array();
						
			echo '<div class="legislators_list">';			
			$myselection = $_GET['show'];
			if($myselection == 'representative')
				$discard = 'senate';
			elseif($myselection == 'senator')
				$discard = 'house';
			else
				$discard = 'nothing';

			foreach ($congress->results as $c) {              
				if($c->chamber == $discard){
					continue;
				}
				$pic_name = "pics/".$c->bioguide_id.".jpg";

				if(is_array($a) &&count($a) > 0)
				{
					if(in_array('middle_name', $a))
					 $middle_name = $c->middle_name;
					else
					 $middle_name = "";
					if(isset($c->state_rank) && $c->state_rank != "")
					echo "<h3 class='legislator'>" . esc_html($c->first_name . " " . $middle_name . " " . $c->last_name) . " <small>(". " ". ucwords(esc_html($c->state_rank)) . ", " . esc_html($c->state_name) . " " . esc_html($c->district) . " " .")</small></h3>";
				    else
				    echo "<h3 class='legislator'>" . esc_html($c->first_name . " " . $middle_name . " " . $c->last_name) . " <small>(". " ". ucwords(esc_html($c->state_rank)) . " " . esc_html($c->state_name) . " " . esc_html($c->district) . " " . ")</small></h3>"; 	
				
					if(in_array("picture", $a))
					{
						if(file_exists($pic_name)) echo "<img class='legislator-pic' src='".$url.$pic_name."' width='40' height='50' alt='' />";
						else echo "<img class='legislator-pic' src='".$url."pics/unknown.jpg' width='40' height='50' alt='' />";
					}
					
					echo "<ul class='legislator-contact'>";
         
					foreach($c AS $key=>$value)
					{				
						if(in_array($key, $a))
						{
							$tempval = ucwords(str_replace('_', " ", $key));
							if(empty($value)) $value = "Not Available";
							if(strpos($value, "http:") !== false || strpos($value, "https:") !== false) $value = '<a href="'.$value.'" target="_blank">'.$value.'</a>';
							if($key == 'facebook_id') $value = '<a href="https://www.facebook.com/'.$value.'" target="_blank"> https://www.facebook.com/'.$value.'</a>';
							if($key == 'twitter_id') $value = '<a href="https://twitter.com/'.$value.'" target="_blank"> https://twitter.com/'.$value.'</a>';
							if($key == 'votesmart_id') $value = '<a href="http://votesmart.org/candidate/'.$value.'" target="_blank"> http://votesmart.org/candidate/'.$value.'</a>';
							if($key == 'youtube_id') $value = '<a href="https://www.youtube.com/'.$value.'" target="_blank"> http://www.youtube.com/user/'.$value.'</a>';
							echo "<li>$tempval :  $value</li>";
						}
					}

					echo "</ul>";
				}
				else
				{
					echo "<h3 class='legislator'>" . esc_html($c->first_name . " " . $c->last_name) . " <small>(". ucwords(esc_html($c->state_rank)) .", " . esc_html($c->state_name) .")</small></h3>";
				}
				
			} 
			
			echo '<div style="clear:both"></div></div>';
			
		}
		else echo "Error1";
	}
	else echo "Error2";
?>