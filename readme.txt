=== Plugin Name ===
Contributors: lamarant
Donate link: 
Tags: weather, forecast, ajax, weatherbug.com
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: trunk

Place clean, nice-looking weather forecasts from weatherbug.com within your blog and sidebar.

== Description ==

Use this plugin to place current weather and/or weather forecast (up to 5 days) from weatherbug.com within your blog and sidebar. Options to show or hide both the current weather and weather forecast. If displaying forecast, choose to display 3, 4, or 5 day forecasts. Comes with a handy widget for your sidebars and an easy-to-use short code for your Pages and Posts.

This plugin requires an API Key which can be obtained for free from http://developer.weatherbug.com/. This plugin will cache the weather data for 15 minutes and uses Ajax to build the weather forecast using semantic markup.

http://www.leonamarant.com/2012/02/07/weather-spider-wordpress-plugin/


== Installation ==

= Install the plugin =
1. Upload the "weather-spider-display-weather-forecast-on-your-blog" folder to the /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Next, Get your API Key from weatherbug.com: =

1. Go to: http://developer.weatherbug.com/
2. Register for an account
3. Apply for an API key (be sure to get the REST key - not the GEO key)

= Finally, go to Settings > Weather Spider and enter the API Key =

Once installed, you can:

- Add weather forecasts to any sidebar using the WeatherSpider widget
- Add weather forecasts to a Blog or Page with the shortcode [weatherspider]

== Frequently Asked Questions ==

= How do I get an API key from weatherbug.com? =

1. Go to: http://developer.weatherbug.com/
2. Register for an account
3. Apply for an API key (be sure to get the REST key - not the GEO key)

= How do I use the short code? =

To display a weather forecast in a page or a post, you can use the [weatherspider] shortcode. The shortcode has four options, listed below.

* zip (optional, default=90210): The zip code of the location you want to show the forecast for.
* size (optional, default=sm): The size of the forecast display. Options: sm (200px wide/3 day forecast), med (250px wide/4 day forecast), lg (300px wide/5 day forecast)
* showCurrent (optional, default=true): Show the current temperature in the display. Options: true, false
* showForecast (optional, default=true): Show the current temperature in the display. Options: true, false

EXAMPLE: 

[weatherspider zip="02842" size="lg" showCurrent="false" showForecast="true"]


== Screenshots ==

1. 5 Day (lg) Forecast Display with Current Weather
2. Weather Spider Widget

== Changelog ==

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.0 =
* Initial release of the plugin.
