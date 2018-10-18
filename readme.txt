=== CongressLookup ===
Contributors: ConstructiveGrowth, Quick2ouch, gsnarawat, sugiartha, trishahdee
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RDW8YBV83S882
Tags: congress, lookup, senator, representative, US congress, find senator, find representative
Requires at least: 4.0
Tested up to: 4.9.5
Stable tag: 3.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Give your visitors the ability to lookup US Congress members for specific zip codes and addresses.

== Description ==

<p>CongressLookup is a free WordPress plugin giving your site visitor an easy way to find United States US senators and representatives.  CongressLookup makes it easier to launch a grassroots campaign for your favorite cause and will keep your visitors on your site instead of sending them elsewhere to find their Congressional legislators.</p>
<p><strong>Where The Data Comes From</strong><br />
CongressLookup uses data provided by Google Maps GeoCoding API and Google Civics API.</p>
<p><em>Google Maps GeoCoding API</em><br />
By using this plugin you are bound by the <a title="Google Maps Terms of Use" href="http://www.google.com/intl/en-US_US/help/terms_maps.html" target="_blank">Google Maps terms of use</a>.  Google GeoCoding API limits use to 2,500 requests per IP address per day.  We have added a cache feature (default setting is 30 minutes) you can set to reduce the number of requests made to the API.   To obtain a Google Maps API key there is a link in: WP Admin > Settings > CongressLookup > Configuration, or do the following:<br>

1. Go to the <a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&keyType=CLIENT_SIDE&reusekey=true">Google API Console</a>.<br>
2. Create or select a project.<br>
3. Click Continue to enable the API and any related services.<br>
4. On the Credentials page, get an API key.<br>

</p>
<p><em>Google Civic Information API</em><br />
To display the contact information for each Congress person we now use the Google Civic Information API which requires a separate API key.  To receive one:<br>
1. Using your Google account, login to the <a href="https://console.developers.google.com/projectselector/apis/credentials?pli=1">Google API Console</a>.  Here you create a project if you don't already have one.<br>
2. Click "Create credentials".<br>
3. Choose "API Keys" (do not restrict). Copy and save this key to use in the CongressLookup settings.<br>
Now you have to enable the Civic API:<br>
4. Click the "Library" side menu tab.<br>
5. In the search field enter "Civic".<br>
6. Click on the "Google Civic Information API" link.<br>
7. Then click the word "Enable".<br>
8. On your website, go to:  Settings > CongressLookup > Configuration.  Paste the Civic API key in the Google Civic API Key field.<br>
9. Save CongressLookup settings.
</p>
<p>PLEASE NOTE: YOU CAN NOT USE THE SAME API KEY FOR GOOGLE MAPS AND GOOGLE CIVICS.</p>
<p>The following information can be displayed for each legislator.  You can turn any of these on/off in the Admin settings:   Title, Picture, Office, Phone, Zip code, Party, State Name, Contact Form, Email, Website, Facebook, YouTube, Twitter, and Google Plus.  NOTE: If one or more of these are not available for an individual Congress person, the information will not show in the results on the page, even if you have the box checked.</p>
<p><strong>Using CongressLookup Plugin</strong><br />
The minimum information needed to get results is (1) a State name and (2) a 5-digit zip code.  However, some zip codes cover more than one congressional district so the more of the address is entered the more accurate the results will be.</p>
<p>CongressLookup is implemented on your WordPress site with use of a shortcode.  See the <a href="http://wordpress.org/plugins/congresslookup/installation/">Installation</a> and <a href="http://wordpress.org/plugins/congresslookup/faq/">FAQs</a> for more information.</p>
<p><strong>Customizing The Look of the Plugin</strong><br />
There are three theme options available: No Theme, Modern and Custom Theme.</p>
<ul>
<li><strong>No Theme</strong>: Uses the core styling from your theme without adding any of it&#8217;s own.</li>
<li><strong>Custom Theme</strong>: Allow you to create your own look using CSS and comes with a demo area to preview your changes.</li>
<li><strong>Modern Theme</strong>: <img class="alignnone size-medium wp-image-81 modern_theme" title="theme_modern" src="http://constructivegrowth.net/site/wp-content/uploads/2012/07/theme_modern-300x136.jpg" alt="" width="300" height="136" /></li>
</ul>
<p><strong>Support</strong><br />
If you have any problems, please see our <a href="http://wordpress.org/plugins/congresslookup/#troubleshooting" target="_blank">Trouble Shooting Guide</a> before putting in a <a href="http://wordpress.org/support/plugin/congresslookup">support request</a>.
<br /><br />
Please use the CongressLookup plugin <a href="http://wordpress.org/support/plugin/congresslookup">support tab</a> here on the WordPress.org website. Keeping support questions and answers public helps everyone. However, feel free to <a href="http://congresslookup.com/contact-us/">contact us here</a> for any other help you may need.</p>
<p><strong>Official Website</strong><br />
http://congresslookup.com</p>

== Installation ==

1. Unzip `CongressLookup.zip` and Upload folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Or in your Plugin page "Add Plugin" search: enter "CongressLookup" then install and activate.
4. Obtain a Google Maps API<br>
    * Go to the <a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&keyType=CLIENT_SIDE&reusekey=true">Google API Console</a>.<br>
    * Create or select a project.<br>
    * Click Continue to enable the API and any related services.<br>
    * On the Credentials page, get an API key.<br>
5. Enter Google Maps API key and configure in:  WP Admin > Settings > CongressLookup
6. To display the contact information for each Congress person we now use the Google Civic Information API which requires a separate API key.  To receive one:<br>
    * Using your Google account, login to the <a href="https://console.developers.google.com/projectselector/apis/credentials?pli=1">Google API Console</a>.  Here you create a project if you don't already have one.<br>
    * Click "Create credentials".<br>
    * Choose "API Keys" (do not restrict). Copy and save this key to use in the CongressLookup settings.<br>
Now you have to enable the Civic API:<br>
    * Click the "Library" side menu tab.<br>
    * In the search field enter "Civic".<br>
    * Click on the "Google Civic Information API" link.<br>
    * Then click the word "Enable".<br>
    * On your website, go to:  Settings > CongressLookup > Configuration.  Paste the Civic API key in the Google Civic API Key field.<br>
    * Save CongressLookup settings.
</p>
<p>PLEASE NOTE: YOU CAN NOT USE THE SAME API KEY FOR GOOGLE MAPS AND GOOGLE CIVICS.</p>
7. Use the following shortcodes in a page, post, widget, or/and sidebars: <br />
Search for Senators and Representatives: `[CongressLookup]`<br />
Search for Senators only: `[CongressLookup show="senator"]`<br />
Search for Representatives only: `[CongressLookup show="representative"]`
8. You can use the following codes in your template, placed outside the loop:<br />
    `<?php echo do_shortcode('[CongressLookup]'); ?>`<br />
    `<?php echo do_shortcode('[CongressLookup show="senator"]'); ?>`<br />
    `<?php echo do_shortcode('[CongressLookup show="representative"]'); ?>`
9. You can create shortcodes with different configurations by going to: WP Admin > Settings > CongressLookup > Custom Shortcode.
10. If you have any problems, please see our <a href="http://wordpress.org/plugins/congresslookup/#troubleshooting" target="_blank">Trouble Shooting Guide</a> before putting in a <a href="http://wordpress.org/support/plugin/congresslookup">support request</a>.

== Frequently Asked Questions ==

= Where  does the legislator information comes from =

CongressLookup uses data provided by both <a href="https://developers.google.com/maps/documentation/geocoding/" target="_blank">Google GeoCoding API</a> and the <a href="https://developers.google.com/civic-information/" target="_blank">Google Civics API</a>.</p>

= What are Google Maps API Terms of Use? =

By using this plugin you are bound by both the <a title="Google Maps Terms of Use" href="http://www.google.com/intl/en-US_US/help/terms_maps.html" target="_blank">Google Maps terms of use</a> and the <a href="https://developers.google.com/maps/documentation/geocoding/" target="_blank">Google GeoCoding</a> API terms of use. The information presented here and on <a title="CongressLookup Homepage" href="http://constructivegrowth.net/wordpress-plugins/congresslookup/">our website</a> is for informational purposes only and is not intended to be legal advise.</p>

= Why use Google Maps GeoCoding API? =

<a href="https://developers.google.com/maps/documentation/geocoding/" target="_blank">Google GeoCoding API</a> is used to obtain the longitude and latitude of a particular address.  This is the most accurate way to determine within which legislative districts an address is located.</p>

= How accurate is Google GeoCoding data? =

GeoCoding is not 100% accurate.  Sometimes an entered address will not return the correct location.  For this reason, we have included a Google Map the visitor can use to verify the location is correct.</p>

= What if the map location is different from the address entered? =

The plugin&#8217;s Google map has a movable pointer you can click and drag to more accurately target the desired location. The address in the input window (and the corresponding legislators) will automatically recalculate each time the red marker is repositioned.</p>

= How do I tell Google their information is wrong? =

You can tell Google of any inaccuracies by clicking the link in the lower right corner of the map: &#8220;Report a map error&#8221;.</p>

= Are there limits on how many addresses can be looked up with CongressLookup plugin? =

We, the developers, created CongressLookup plugin to be free and do not limit how much you can use it, however, both <a href="https://developers.google.com/maps/documentation/geocoding/" target="_blank">Google GeoCoding</a> and <a href="https://developers.google.com/civic-information/" target="_blank">Google Civics</a> have restrictions and/or limits imposed when using the APIs.  Please consult their websites for more information on the legal use of their APIs.</p>

= How does the <strong>CongressLookup</strong> Admin cache setting help with the Google GeoCoding limit? =

We have added a cache feature you can set to reduce the number of requests made to the API.  The default setting will clear the cache every 30 minutes.  If you anticipate 2,500 requests per day will not be enough for your site, even with the caching, please <a title="Contact Us" href="http://constructivegrowth.net/contact-us/">contact us</a> about a custom solution.</p>

= What is the <a href="https://developers.google.com/civic-information/" target="_blank">Google Civic Information API</a> used for? =

CongressLookup uses free information provided by Google Civic API. For any U.S. residential address, you can look up who represents that address at each elected level of government. We make use of their legislator information and legislator photos databases in CongressLookup plugin.

= Where is the CongressLookup settings page located? =

WP Admin &gt; Settings &gt; CongressLookup

= What legislator information can be displayed? =

The following information can be displayed for each legislator.  You can turn any of these on/off in the Admin checkbox settings.  Note: Even if you turn them on, if one of more of this information is not available for a particular member of Congress, the information will not display in their results:</p>
<ul style="margin: -10px 0 20px 100px;">
 <li>Title</li>
 <li>Picture</li>
 <li>Office</li>
 <li>Phone</li>
 <li>Zip code</li>
 <li>Party</li>
 <li>State Name</li>
 <li>Contact Form</li>
 <li>Email</li>
 <li>Website</li>
 <li>Facebook</li>
 <li>Youtube</li>
 <li>Twitter</li>
 <li>Google Plus</li>
</ul>

= How much information must be entered in the address field to get results? =

Minimum information needed is (1) the state and (2) a 5-digit zip code.  However, some zip codes cover more than one congressional district so the more of the address is entered, the more accurate the results will be.</p>

= Can I change the look of the plugin? =

There are three themes to choose from:</p>
<ul>
<li><strong>Modern Theme</strong>: By default.</li>
<li><strong>No Theme</strong>: Uses the core styling from your theme without adding any of it&#8217;s own.</li>
<li><strong>Custom Theme</strong>: Allows you to create your own look using CSS and comes with a demo area to preview your changes.</li>
</ul>

= How do I use CongressLookup on my site? =

Use the following shortcode in a page, post, widget, or/and sidebar:</p>
&#91;CongressLookup&#93;<br>
&#91;CongressLookup show='senator'&#93;<brf>
&#91;CongressLookup show='representative'&#93;
<p>Use the following code in your template, outside of the loop:</p>
&lt;?php echo do_shortcode("&#91;CongressLookup&#93;"); ?&gt;<br>
&lt;?php echo do_shortcode("&#91;CongressLookup show='senator'&#93;"); ?&gt;<br>
&lt;?php echo do_shortcode("&#91;CongressLookup show='representative'&#93;"); ?&gt;<br>
<p>You can custom configure shortcodes by going to: WP Admin > Settings > CongressLookup > Custom Shortcode.</p>

= Do you have "hooks" to modify the output?
Yes.  As of version 2.1.8 we have added the following hooks:
<ul>
<li>"congress_results" filter to modify array returned by api</li>
<li>"congress_field" filter to modify field title</li>
<li>"congress_value" filter to modify field value</li>
<li>"congress_output" filter to modify output per field</li>
</ul>

CONGRESSLOOKUP HOOK FILTERS EXAMPLES

`add_filter( 'congress_results', 'congress_results_callback', 10 );
function congress_results_callback( $results ){
  //$out = '<pre>' . print_r($results, 1) . '</pre>';

  $out .= '<p>add_filter( "congress_results" ) example:</p>';
  $out .= '<table>';
  $out .= '<tr>
        <td>Name</td>
        <td>Picture</td>
        <td>Facebook</td>
      </tr>';
  foreach($results as $i => $arr){
    $out .= '<tr>
          <td>' . esc_html($arr['full_name']) . '</td>
          <td>' . '<img src="' . $arr['pic'] . '">' . '</td>
          <td>' . '<a href="https://www.facebook.com/' . $arr['facebook_id'] . '">' . $arr['facebook_id'] . '</a>' . '</td>
        </tr>';
    }
  $out .= '</table>';

  return $out;
}

//add_filter( 'congress_field', 'congress_field_callback', 10, 2 );
function congress_field_callback($field_title, $key){
  if( $key == 'facebook_id'){
    return 'FB';
  }else{
    return $field_title;
  }
}

//add_filter( 'congress_value', 'congress_value_callback', 10, 3 );
function congress_value_callback($field_value, $key, $value){
  if( $key == 'office'){
    return $field_value . ' <a href="#">(MAP)</a>';
  }else{
    return $field_value;
  }
}

//add_filter( 'congress_output', 'congress_output_callback', 10, 6 );
function congress_output_callback($li, $key, $value, $field_title, $field_value, $result){
  return "<li>{$field_title} => {$field_value}</li>";
}`

= How do I contact support? =

Please use the CongressLookup plugin <a href="http://wordpress.org/support/plugin/congresslookup">Support Tab</a> above.  Keeping support questions and answers public helps everyone.  But feel free to <a href="http://congresslookup.com/contact-us/">contact us here</a> for any other help you may need.</p>

<p><strong>Official Website</strong><br />
http://congresslookup.com</p>

<a id="troubleshooting"></a>
== Troubleshooting ==

<b>If CongressLookup is not working properly, please try the following steps:</b>
<br /><br />
1. Make sure you are using the most recent version of the plugin.
<br /><br />
2. Make sure your server is running at least PHP 5.4.
<br /><br />
3. When creating your Google Maps API, set Key Restrictions to "None".
<br /><br />
4. PLUGIN CONFLICT: Please try deactivating all plugins except CongressLookup. If CongressLookup does then work, turn on one plugin at a time and test it again until you find the plugin it conflicts with. Please let us know what plugin this is.
<br /><br />
5. THEME CONFLICT: If there is still an issue after testing for a plugin conflict, and all other plugins are turned off, please try temporarily switching to another theme.  We recommend trying one of the default WordPress themes.
<br /><br />
6. SERVER SETTINGS: If none of the above has helped, please tell your webmaster to enable allow-url-fopen in php.ini
http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen<br />
When doing this, disable the cache option for testing, then when allow-url-fopen is enabled and CongressLookup works only then enable the cache again.
<br /><br />
7. If you are still having any issues, please put in a <a href="http://wordpress.org/support/plugin/congresslookup">support request</a>.

== Screenshots ==

1. CongressLookup frontend with Modern theme screenshot-1.jpg
2. WP Admin > Settings > CongressLookup > Configuration screenshot-2.jpg
3. WP Admin > Settings > CongressLookup > Custom Shortcode screenshot-3.jpg
4. WP Admin > Settings > CongressLookup > Custom Shortcode > Add Shortcode screenshot-4.jpg

== Changelog ==

= 3.0.2 =
* Updated 28 April 2018
* Fix title not shown
* Fix PayPal donation link

= 3.0.1 =
* Updated 25 January 2018
* Fix non-SSL photo for WP with SSL enabled
* Add folder /photo inside plugin
* Fix missing unknown.jpg
* Fix javascript function "jsCongress" detection

= 3.0 =
* Updated 30 September 2017
* Removed Sunlight Labs Congress 3.0 API
* Added Google Civic Information API
* Added placeholder setting for frontend address input field
* Fixed bug in default address for frontend address input field

= 2.1.8 =
* Added "congress_results" filter to modify array returned by api
* Added "congress_field" filter to modify field title
* Added "congress_value" filter to modify field value
* Added "congress_output" filter to modify output per field

= 2.1.7 =
* Add CSS to wrap long URLs
* Add custom javascript call (if the function exist) everytime api called

= 2.1.6 =
* Fix undefined variables
* Fix shortcode bug when displaying senator or representative only 

= 2.1.5 =
* Add shortcode creation area 
* Modify admin interface
* Update get photos function
* Update internal cache API call using wp transient api
* Update API call to get congress members 
* Fix bugs 404 when calling API
* Add "What to display" options at shortcode level

= 2.1.4 =
* Enable multiple map call
* Merge geocode.js into main plugin file
* Add option to input Google Map API Key
* Update picture files
* Update do_shortcode usage at readme.txt file

= 2.1.3 =
* Update 28 November 2016
Sunlight Labs database and API is now run by Propublica.org  They are not requiring an API key but CongressLookup still expects one so enter any character string in the API Key field to make it work.

= 2.1.2 =
* Update 20 April 2016
Fixed "window.onload" function conflict that occurred with another plugin.

= 2.1.1 =
* Update 21 February 2015
1.  Fixed an "Undefined property" error.

= 2.1 =
* Update 01 June 2014
1. Added two new shortcodes for displaying (1) US Senators only, and (2) US Representatives only.

= 2.0 =
* Update 15 May 2014
1. Replaced the use of deprecated Sunlight Labs <b>Congress API</b> with more comprehensive <b>Sunlight Congress API</b>.
1. Added more congressperson stats, now available from new API, with checkboxes in admin.
1. Added ability for admin to choose to display only Senators, only Representatives, or both (default) from a dropdown menu in admin.
1. Minor CSS changes

= 1.0 =
* Original version, 23 August 2012

== Upgrade Notice ==

= 3.0.2 =
Updated 28 April 2018 to fix Senator and Representative title that was not showing on front when checked.  Also fixed PayPal donation link that was not working.

= 3.0.1 =
Updated 25 January 2018 to fix non-SSL photo for WP with SSL enabled, added folder /photo inside plugin, fixed missing unknown.jpg, and fixed javascript function "jsCongress" detection.

= 3.0 = <b>Major Update</b>. Because Sunlight Labs Congress 3.0 API was deprecated on 30 September 2017, CongressLookup 2.1.8 plugin will stop working on that date.  To continue using CongressLookup please update to version 3.0 and obtain the additional Google Civic Information API key.

= 2.1.4 =
No longer a conflict with other plugins who also use Google Maps API. Merged geocode.js into main plugin file.  Added option to input Google Map API Key. Updated picture files URL.  Updated do_shortcode usage at readme.txt file.

= 2.1.3 =
Sunlight Labs database and API is now run by Propublica.org  They are not requiring an API key but CongressLookup still expects one so enter any character string in the API Key field to make it work.

= 2.1.2 =
Fixed "window.onload" function conflict that occurred with another plugin.

= 2.1.1 =
Fixed an "Undefined property" error.

= 2.1 =
Added two new shortcodes for searching US Senators only and US Representatives only.

= 2.0 =
Upgrade is necessary before the 2014 Congress inauguration (January 2015), which is when the deprecated Sunlight Foundation Congress API will stop working.

