/*!
 * jQuery WeatherSpider plugin
 * version 1.00 (2012-02-07)
 * Requires jQuery v1.3.2 or later
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * Authors: Leon Amarant (http://www.leonamarant.com)
 */
 
(function( $ ){
	//C = (F - 32) * 5/9
    $.fn.weatherspider = function( options ) {  
		
        // Create some defaults, extending them with any options that were provided
        var defaults = {
            mode                    : 'cache',          // widget mode. either hit the api live or hit a server-side script [cache/live]
            size                    : 'lg',             // size of widget [sm/med/lg] : sm = 3 day forecast | med = 4 day forecast | lg = 5 day forecast
            showCurrent             : true,             // display current weather? [true/false]
            showForecast            : true,             // display forecast data? [true/false]
            zip                     : '',               // 5 digit zip code to display (only relevant if [mode=live]
            apiKey                  : '',               // your API Key. sign up at http://developer.weatherbug.com to get one.
            weatherCacheScript      : ''               	// relative path to the script on YOUR server that calls and caches the feed (eg: weatherservice.aspx?Zip=02842)
        };
        
        var settings = $.extend(defaults, options);

        return this.each(function() {        
            var obj = $(this);
            
			//Override plugin setting from element's title attribute
			var settingsArray = obj.attr('title').split('|');
			var strSettings = '{';
			for (var i = 0; i < settingsArray.length; i++) {
				itemArray = settingsArray[i].split('=');
				strSettings = strSettings + '"' + itemArray[0] + '":"' + itemArray[1] + '",';
			}
			strSettings = strSettings.substring(0, strSettings.length - 1); 
			var strSettings = strSettings+  '}';
			var settingsObj= $.parseJSON(strSettings);
			for(var keyToSet in settingsObj) {
				valToSet = settingsObj[keyToSet];
				if(valToSet === "true"){valToSet = true}
				if(valToSet === "false"){valToSet = false}
				for(var keyToCompare in settings) {					
					if(keyToCompare===keyToSet){
						settings[keyToCompare] = valToSet;						
					}
				}
			}
			
			//place all settings in scoped variables
			var sMode = settings.mode;                 
            var sSize = settings.size;                    
            var sShowCurrent = settings.showCurrent;             
            var sShowForecast = settings.showForecast;           
            var sZip = settings.zip;                     
			var sApiKey = settings.apiKey;
            var sWeatherCacheScript = settings.weatherCacheScript;
			
			obj.removeAttr('title');
			
			if(sMode == 'live'){
                //Get data from live weatherbug feed
                feedURI = "http://i.wxbug.net/REST/Direct/GetData.ashx?dt=l&dt=o&ic=1&dt=f&nf=7&dt=a&f=myweather&api_key=" + sApiKey + "&zip=" + sZip;
				console.log(feedURI);
            }
            if(sMode == 'cache')
            {
                //Get data from local script that should cache the feed
                feedURI = sWeatherCacheScript + '?zip=' + sZip;
            }
			
            $.ajax({
                url: feedURI,
                dataType: 'json',
                cache: false,
				async: false,
				statusCode: {
				  404:function() { obj.addClass('SLweatherspider').addClass('wb'+sSize).append('<div class="SLweatherspiderByLine">Error Retrieving Weather</div>'); },
				  200:function() {  },
				  201:function() {  },
				  202:function() {  }
				},
                success: function(fc){
					
                    //determine sizes to use for display
                    numForecastDays = 3;                    
                    switch(sSize)
                    {
                        case 'sm':
                            numForecastDays = 3;
							curIconSize= '80x67'; 
                            break;
                        
                        case 'med':
                            numForecastDays = 4;
							curIconSize = '100x84'; 
                            break;
                            
                        case 'lg':
                            numForecastDays = 5;
							curIconSize = '120x101'; 
                            break;                    
                    }
                    widgetWidth = ((numForecastDays+1) * 49) + numForecastDays + 1;                                       
                    obj.addClass('SLweatherspider');
                    obj.append('<div class="SLweatherspiderLoc">' + fc.weather.LocationData.location.city + ', ' + fc.weather.LocationData.location.state + '</div>');
					obj.append('<div id="SLWeatherData">');
					obj.find('#SLWeatherData').addClass('wb'+sSize).attr('style','width:' + widgetWidth + 'px;')
                    if(sShowCurrent){
						//Get Current Observations
						curIcon = fc.weather.ObsData.icon;
                        curTemp = Math.round(fc.weather.ObsData.temperature) + '<sup>' + fc.weather.ObsData.temperatureUnits + '</sup>';
                        curDesc = fc.weather.ObsData.desc;
                        curWindDir = fc.weather.ObsData.windDirection;
                        curWindSpeed = fc.weather.ObsData.windSpeed;
                        
                        obj.find('#SLWeatherData').append('<dl>');
                        obj.find('dl').append(
                            '<dt><img src="http://img.weather.weatherbug.com/forecast/icons/localized/' + curIconSize + '/en/trans/' + curIcon + '.png" /></dt>' +
                            '<dd><p><span class="curTemp">' + curTemp + '</span></p>' +
                            '<p><span>' + curDesc + '</span></p>' + 
                            '<p><span>Wind: ' + curWindDir + ' ' + curWindSpeed + 'mph</span></p>'
                        );
                        obj.find('#SLWeatherData').append('<div style="clear:both;"></div>');                        
                    }
                    if(sShowForecast)
                    {
                        obj.find('#SLWeatherData').append('<ul>');
                        $.each(fc.weather.ForecastData.forecastList, function (index, key) {
                            if(index <= numForecastDays)
                            {
                                //Determine if we're showing night
                                img = key.dayIcon;
                                hi = key.high;
                                lo = key.low;
                                pred = key.dayPred;
                                if(key.hasDay == false)
                                {
                                    img = key.nightIcon;
                                    pred = key.nightPred
                                }
                                strHi = hi?'<span>H</span>' + hi : '&nbsp;';
                                strLo = lo?'<span>L</span>' + lo : '&nbsp;';
                                
                                obj.find('ul').append(
                                    '<li><div class="wbDay">' + key.dayTitle.substring(0,3).toUpperCase() + '</div>' +
                                    '<div class="wbImg"><img src="http://img.weather.weatherbug.com/forecast/icons/localized/30x25/en/trans/' + img + '.png" title="' +pred+ '"></div>' +
                                    '<div class="wbTemp">' + strHi + '</div>' +
                                    '<div class="wbTemp">' + strLo + '</div>'
                                );
                            }
                        });
                        obj.find('#SLWeatherData').append('<div style="clear:both;"></div>');
                        obj.find('li:last').addClass('noborder');
                    }
                    obj.append('<div class="SLweatherspiderByLine" style="width:' + widgetWidth + 'px;"><a href="http://weather.weatherbug.com/?zip=' + sZip +'" target="_blank">weatherbug.com</a></div>');
                }
            });

        });

    };
    
})( jQuery );
