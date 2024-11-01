$(document).ready(function() {
     alert('!');
});

$('.wspider').weatherspider({weatherCacheScript : 'localWeatherService.php?zip=' + $(this).attr('title')});