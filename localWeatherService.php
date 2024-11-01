<?php
error_reporting(0); // Turn off all error reporting
require_once('../../../wp-config.php');
$cachefile = 'cache/' . $_GET["zip"];
$cachetime = 1800;

$wspideroptions =  get_option( 'wspider_options', $default );

// Serve from the cache if it is younger than $cachetime
if($wspideroptions['docache'] != 'no')
{
	if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
		include($cachefile);
		exit;
	}
}
ob_start(); // Start the output buffer

// Call to weather service to get weather

//$WeatherServiceURI = "http://i.wxbug.net/REST/Direct/GetForecast.ashx?nf=6&ih=1&ht=t&ht=i&ht=cp&ht=wd&ht=ws&l=en&c=US&api_key=".$wspideroptions['apikey']."&zip=" . $_GET["zip"];
$WeatherServiceURI = "http://i.wxbug.net/REST/Direct/GetData.ashx?dt=l&dt=o&ic=1&dt=f&nf=7&dt=a&api_key=".$wspideroptions['apikey']."&zip=" . $_GET["zip"];
$output = file_get_contents($WeatherServiceURI);
echo $output;

// Cache the output to a file
if($wspideroptions['docache'] != 'no')
{
	$fp = fopen($cachefile, 'w');
	fwrite($fp, ob_get_contents());
	fclose($fp);
	ob_end_flush(); // Send the output to the browser
}
?>