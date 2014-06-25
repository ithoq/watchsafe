<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100% }
    </style>
    <script type="text/javascript"
       src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDwf6jM1SkCL_tQLN1qzeqaJX4Wor5380A&sensor=true">
    </script>
    <script type="text/javascript">
      function initialize() {
        var myOptions = {
          center: new google.maps.LatLng(-34.397, 150.644),
          zoom: 8,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById("map_canvas"),
            myOptions);


var directionsService = new google.maps.DirectionsService();

      google.maps.event.addListener(map, 'click', function(event) {
      var request = {
        origin:event.latLng, 
        destination:event.latLng,
        travelMode: google.maps.DirectionsTravelMode.DRIVING
      };

      directionsService.route(request, function(response, status) {
      if (status == google.maps.DirectionsStatus.OK) {
          var marker = new google.maps.Marker({
             position: response.routes[0].legs[0].start_location, 
             map: map,
             title:"Hello World!"
          });
      }
    });
});


}



    </script>
  </head>
  <body onload="initialize()">
    <div id="map_canvas" style="width:100%; height:100%"></div>
  </body>
</html>