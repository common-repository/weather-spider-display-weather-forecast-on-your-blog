<?php   
  
/* 
Plugin Name: Weather Spider
Plugin URI: http://leonamarant.com/
Description: Place current weather and weather forecast from weatherbug.com within your blog and sidebar. Requires API Key which can be obtained for free from http://developer.weatherbuig.com/.
Version: 1.0 
Author: Leon Amarant
Author URI: http://leonamarant.com/ 
*/ 

/* TODO:
	- Create Dark Skin
	- Add support for locations using Locations Lookup API
	- Add support for languages
	- add support for culture	
*/


// *********** ADD JS AND CSS FILES TO HEADER ***********
function my_scripts_method() {
   wp_register_script('wbScript',
       plugins_url( '/jquery.weatherspider.js', __FILE__ ),
       array('jquery'),
       '1.0');
   wp_enqueue_script('wbScript');
   
   wp_enqueue_style( 'wspider-style', plugins_url( '/jquery.weatherspider.css', __FILE__ ), false, '1.0', 'all' ); // Inside a plugin
   
}

add_action('wp_enqueue_scripts', 'my_scripts_method');


// *********** ADMIN PAGE **************
// add the admin options page
add_action('admin_menu', 'wspider_admin_add_page');
function wspider_admin_add_page() {
	add_options_page('Weather Spider Page', 'Weather Spider', 'manage_options', 'plugin', 'wspider_options_page');
}

// display the admin options page
function wspider_options_page() { ?>
    <script type="text/javascript">
	function clearWSCache(){
		jQuery('#WSCacheStatus').html('clearing cache...');
		jQuery.ajax({
			url: '<?php echo plugins_url( '/clearcache.php', __FILE__ ) ?>',
			dataType: 'json',
            cache: false,
			success: function(d){
				jQuery('#WSCacheStatus').html(d.msg);
			}
		})
	}
	</script>
    <div>
        <h1>Weather Spider Settings</h1>
        <form action="options.php" method="post">
        <?php settings_fields('wspider_options'); ?>
        <?php do_settings_sections('plugin'); ?>
        <br />
        <input name="Submit" type="submit" value="<?php esc_attr_e('Save Settings'); ?>" />
        </form>
        
        <div style="margin: 20px 0; background: #e3e3e3; padding: 10px 20px 20px 20px;">
            <h1>Instructions</h1>
            <hr />
            <h3>WeatherBug.com API Key</h3>
            <p>In order to use this plugin, you must first obtain an API key from WeatherBug.com.</p>
            <ol>
                <li>Go to: <a href="http://developer.weatherbug.com/" target="_blank" title="WeatherBug Developer Community">http://developer.weatherbug.com/</a></li>
                <li>Register for an account</li>
                <li>Apply for an API key</li>
                <li>Enter your API key in the field above and press the "Save Settings" button</li>
            </ol>
            
            <br /><hr />
            <h3>Caching Weather Data</h3>
            <p>By default, the weather feed information for each zip code will be cached for 15 minutes. This prevents the widget from making uneccesary calls to WeatherBug.com for weather. You can turn caching off in the settings panel (though it is not recommended). You can also clear the cache manually by clicking the "Clear Cache" button.</p>
            
            <br /><hr />
            <h3>Displaying in Pages and Posts</h3>
            <p>To display a weather forecast in a page or a post, you can use the [weatherspider] shortcode. The shortcode has four options, listed below.</p>
            <ul style="margin: 20px;">
                <li><strong>zip:</strong> The zip code of the location you want to show the forecasft for (default=90210).</li>
                <li><strong>size:</strong> The size of the forecast display. Options are:
                    <ul style="margin: 20px 10px;">
                        <li><strong>sm</strong>: 3 Day forecast, 200px wide (default)</li>
                        <li><strong>med</strong>: 4 Day forecast, 250px wide</li>
                        <li><strong>lg</strong>: 5 Day forecast, 300px wide</li>
                    </ul>
                </li>
                <li><strong>showCurrent:</strong> Show the current temperature in the display. Options are: <em>true, false</em> (default=true)</li>
                <li><strong>showForecast:</strong> Show the forecast in the display. Options are: <em>true, false</em> (default=true)</li>
            </ul>
            <p>Example:</p>
            <blockquote style="background: #fff; border: solid 1px #ccc;padding: 10px; width: 80%; margin:auto;">
            [weatherspider zip="02842" size="lg" showCurrent="false" showForecast="true"]
            </blockquote>
            
            <br /><hr />
            <h3>The Widget</h3>
            <p>If your theme is widget-enabled, you can add the WeatherSpider widget to any sidebar. Configuration is self-explanatory. Note that you can add multiple instances of the widget in your sidebar.</p>
        </div>
    </div>
<?php }

// add the admin settings and such
add_action('admin_init', 'wspider_admin_init');
function wspider_admin_init(){
	register_setting( 'wspider_options', 'wspider_options', 'wspider_options_validate' );
	add_settings_section('wspider_main', '', 'wspider_section_text', 'plugin');
	add_settings_field('wspider_apikey', 'Weatherbug.com API Key', 'wspider_setting_apikey', 'plugin', 'wspider_main');
	add_settings_field('wspider_docache', 'Cache Weather Data?', 'wspider_setting_docache', 'plugin', 'wspider_main');
}
function wspider_section_text() {
	//echo '<p>Main description of this section here.</p>';
}
function wspider_setting_apikey() {
	$options = get_option('wspider_options');
	echo "<input id='wspider_apikey' name='wspider_options[apikey]' size='40' type='text' value='{$options['apikey']}' />";
}
function wspider_setting_docache() {
	$options = get_option('wspider_options');
	$selYes = '';
	$selNo = '';
	if($options['docache']=='yes'){$selYes=' selected';}
	if($options['docache']=='no'){$selNo=' selected';}
	echo "<select id='wspider_docache' name='wspider_options[docache]'><option value='yes'".$selYes.">Yes</option><option value='no'".$selNo.">No</option></select> <input type='button' value='Clear Cache' onclick='clearWSCache()' /> <span id='WSCacheStatus'></span>";
}

// validate our options
function wspider_options_validate($input) {
	$newinput['apikey'] = trim($input['apikey']);
	$newinput['docache'] = $input['docache'];
	return $newinput;
}

// *********** CREATE SIDEBAR WIDGET ***********
/* Add our function to the widgets_init hook. */
add_action( 'widgets_init', 'weatherspider_load_widgets' );

/* Function that registers our widget. */
function weatherspider_load_widgets() {
	register_widget( 'weatherspider_widget' );
}

class weatherspider_widget extends WP_Widget {
	function weatherspider_widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'weatherspider', 'description' => 'Displays weather for a specified zip code in your sidebar.' );

		/* Widget control settings. */
		$control_ops = array('id_base' => 'weatherspider-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'weatherspider-widget', 'Weather Spider', $widget_ops, $control_ops );
	}	
	
	function widget( $args, $instance ) {
		extract( $args );

		/* User-selected settings. */
		$zip = $instance['zip'];
		$size = $instance['size'];
		$show_current = isset( $instance['show_current'] ) ? $instance['show_current'] : 'false';
		$show_forecast = isset( $instance['show_forecast'] ) ? $instance['show_forecast'] : 'false';

		/* Before widget (defined by themes). */
		echo $before_widget;

		echo '<div class="wspider" title="zip='.$zip.'|size='.$size.'|showCurrent='.$show_current.'|showForecast='.$show_forecast.'"></div>';

		/* After widget (defined by themes). */
		echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['zip'] = $new_instance['zip'];
		$instance['size'] = $new_instance['size'];
		$instance['show_current'] = $new_instance['show_current'];
		$instance['show_forecast'] = $new_instance['show_forecast'];

		return $instance;
	}
	
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'zip' => '90210', 'size' => 'sm', 'show_current' => 'true', 'show_forecast' => 'true' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
        

		<p>
			<label for="<?php echo $this->get_field_id( 'zip' ); ?>">Zip Code:</label>
			<input id="<?php echo $this->get_field_id( 'zip' ); ?>" name="<?php echo $this->get_field_name( 'zip' ); ?>" value="<?php echo $instance['zip']; ?>" style="width:95%;" />
		</p>
        
        <p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>">size:</label>
			<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" class="widefat" style="width:100%;">
				<option value="sm" <?php if ( 'sm' == $instance['size'] ) echo 'selected="selected"'; ?>>Small (3 Day Forecast-200px wide)</option>
				<option value="med" <?php if ( 'med' == $instance['size'] ) echo 'selected="selected"'; ?>>Medium (4 Day Forecase-250px wide)</option>
                <option value="lg" <?php if ( 'lg' == $instance['size'] ) echo 'selected="selected"'; ?>>Large (5 Day Forecast-300px wide)</option>
			</select>
		</p>
        
        <p>
			<input class="checkbox" type="checkbox" <?php if( $instance['show_current']== true ){ echo 'checked'; }; ?> value="true" id="<?php echo $this->get_field_id( 'show_current' ); ?>" name="<?php echo $this->get_field_name( 'show_current' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_current' ); ?>">Display Current Weather?</label>
		</p>
        
        <p>
			<input class="checkbox" type="checkbox" <?php if( $instance['show_forecast'] == true ){echo 'checked';}; ?> value="true" id="<?php echo $this->get_field_id( 'show_forecast' ); ?>" name="<?php echo $this->get_field_name( 'show_forecast' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_forecast' ); ?>">Display Forecast?</label>
		</p>
        
        <?php
	}

}

// *********** CREATE SHORT CODE **************
// [bartag foo="foo-value"]
function WS_shortcode( $atts ) {
	extract( shortcode_atts( array(
		'zip' => '90210',
		'size' => 'sm',
		'current' => 'true',
		'forecast' => 'true',
	), $atts ) );

	return '<div class="wspider" title="zip='.$zip.'|size='.$size.'|showCurrent='.$current.'|showForecast='.$forecast.'"></div>';
}
add_shortcode( 'weatherspider', 'WS_shortcode' );

// *********** INVOKE VIA JQUERY CALL. PLACE IN GLOBAL FOOTER ************
function your_function() {
    echo '<script type="text/javascript">
			jQuery(".wspider").weatherspider({            
				"weatherCacheScript"      : "'. plugins_url( '/localWeatherService.php', __FILE__ ) . '"
		  	});
		  </script>';
}
add_action('wp_footer', 'your_function');
?>