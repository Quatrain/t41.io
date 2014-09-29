function getLocation(button) {
	obj = button;
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(showPosition);
	} else {
		obj.innerHTML = "Geolocation is not supported by this browser.";
	}
}
		
function showPosition(position) {
	obj.innerHTML="Latitude: " + position.coords.latitude +
	"<br>Longitude: " + position.coords.longitude;
}
