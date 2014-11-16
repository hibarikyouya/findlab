function initialize() {
	var latlng = new google.maps.LatLng(48.9, 2.4);
	var myOptions = {
		zoom: 11,
		center: latlng,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map(
			document.getElementById("map_canvas"), myOptions);
}

//$(document).ready(function() { $("#map").gmap3(); } );
//
//js/gmap3.js
//$("#map").gmap3({ map:{options:{center:[45,0],zoom:12}}});
//
//navigator.geolocation.getCurrentPosition(onSuccess, onError, {maximumAge:10000, timeout:3000000, enableHighAccuracy: true});
//
//function onSuccess(position) {
//var latitude = position.coords.latitude;
//var longitude = position.coords.longitude;
//$("#map").gmap3({
//map:{options:{center:[latitude,longitude],zoom:12}}});
//
//$('#map').gmap3({marker:{values:[{latLng:[latitude, longitude],
//data: "Center de la carte"},{latLng:[latitude+.02,longitude+.01],data:"Un pe décalé, options={icon: "http://image.png"}}}

/*
function getCoord() {
	var query = "bussy+saint+georges";
	var url = "http://maps.googleapis.com/maps/api/geocode/json?";
	var params = { address : query };
	var esearch = url+jQuery.param(params);
	$("#entrez").text(esearch);
	$.getJSON(url, params, function(data) {
		$("#latlong").text(JSON.stringify(data, undefined, 4));
	}
	);
}
//*/
