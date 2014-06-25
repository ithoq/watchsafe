
<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
  initialize();         
  });
</script>
<script type="text/javascript">

      function initialize(){
     var mapOptions = {
        zoom: 5,
        center: new google.maps.LatLng(-33.87138,151.065246),
        mapTypeId: google.maps.MapTypeId.ROADMAP
      };

      temp = document.getElementById("map_canvas");
      //$('#map_canvas').css({"width":"100%","height":"600px"});
      var map = new google.maps.Map(temp,mapOptions);

      var counter = 0;
      var center;
      var map_objects = [];
      var marker;
      var old_circle = false;
      var circle = null;
      var geocoder;

      geocoder = new google.maps.Geocoder();

      google.maps.event.addListener(map, 'click', function(event) {
            if (counter == 0) {
          marker = new google.maps.Marker({
                                            position: event.latLng,
                                            map: map,
                             });
             counter++;
             center = event.latLng;

             console.log(center);
                   
                   $('#lat').val(center.lat());
                   $('#lon').val(center.lng());
             
             map_objects.push(marker);
          } else {
            console.log(event.latLng);
            //marker.setMap(null);
            if (old_circle) old_circle.setMap(null);
            marker.setMap(null);
            var distance = google.maps.geometry.spherical.computeDistanceBetween(center, event.latLng);
            
            var fillColor = '#05AB5E';
            var strokeColor = '#05AB5E';
            if ($('#type').val()==='overspeed') {var fillColor = '#E80000'; var strokeColor = '#E80000';}

            var populationOptions = {
              strokeColor: strokeColor,
              strokeOpacity: 0.8,
              strokeWeight: 2,
              fillColor: fillColor,
              fillOpacity: 0.35,
              map: map,
              center: center,
              radius: distance,
              editable : true
            };

            circle = new google.maps.Circle(populationOptions);
            map_objects.push(circle);
            old_circle = circle;

            $('#radius').val(distance);

             google.maps.event.addListener(circle, 'radius_changed', function () {
                      $('#radius').val(circle.getRadius());
                 });

                 google.maps.event.addListener(circle, 'center_changed', function () {
                      $('#lat').val(circle.getCenter().lat());
                      $('#lon').val(circle.getCenter().lng());
                 });

            
          }

        });

 function showAddress(address) {
        if (geocoder) {

            geocoder.geocode( { 'address': address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    map.setCenter(results[0].geometry.location);
                    map.setZoom(13);       
                } 

                else {
                //alert("Mistake: " + status);
                }

            });
        }
    }

          $("#find").click(function(){
            var adress = $("#adress").val();
            showAddress(adress);
          });

      $("#clear").click(function(){
           counter = 0;
           $('#lat').val("");
               $('#lon').val("");
               $('#radius').val("");
           for (var i = 0; i < map_objects.length; i++) {
                    map_objects[i].setMap(null);
                }
         });
          
          $("#type").change(function(){
             if ($("#type").val()=='overspeed') {
              if (circle!=null) {
              var populationOptions = {
                strokeColor: '#E80000',
                fillColor: '#E80000'
              };
              circle.setOptions(populationOptions);
              } 

              $("#overspeed").css({"display":"inline"});
              $("#time").css({"display":"none"});
             } else {
              if (circle!=null) {
              var populationOptions = {
                strokeColor: '#05AB5E',
                fillColor: '#05AB5E'
              };
              circle.setOptions(populationOptions);
              } 
              $("#overspeed").css({"display":"none"});
              $("#time").css({"display":"inline"});
             }
          });

          $("#save").click(function(){
             if (($("#name").val()=='') || ($("#lat").val()=='')  || ($("#lon").val()=='')  || ($("#radius").val()=='')) {
                console.log($("#name").val());
                console.log($("#lat").val());
                console.log($("#lon").val());
                console.log($("#radius").val());
                alert('You must set fence name and generate fence!');
             } else {
                $("#size").val(map.getZoom());
                $( "#form" ).submit();
             }
          });



      }


</script>

<?php if ($imei=='all') {?>
<h3>Add new fence to all devices </h3>
<?php } else {?>
<h3>Add new fence to  device with imei = <?=$imei?></h3>
<?php }?>
<form action="/manager/fences" method="post" id="form">
<table class='table table-bordered' style="font-size:15px;">
      <tr>
           <td>Watch Zones Options</td>
           <td>Watch Zones</td>
      </tr>
      <tr>
        <td style="width:25%; padding:10px;">
      <input type="text" name="name" id="name" placeholder="Geo fence name" />
      <select name='type' id='type'>
               <option value='entry&exit' selected>entry&exit</option>
               <option value='overspeed'>overspeed</option>
            </select>
            Usage type: <br><br>
            <div id="overspeed" style="display:none;">
              <input type="text" name="overspeed" value="60" placeholder="Overspeed limit" />
              <p style="color:red;"> You must set overspeed limit in KM/h
            </div>
            <div id="time">
              
              <div id="datetimepicker1" class="input-append date">
              <input type="text" name='start' placeholder='Date from'></input>
              <span class="add-on">
                <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
              </span>
            </div>

             <div id="datetimepicker2" class="input-append date">
              <input type="text" name='end' placeholder='Date to'></input>
              <span class="add-on">
                <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
              </span>
            </div>
            <p style="color:red;"> You must set time period when current geofence will works. If both fields is empty - geofence will works all time.
            </div>

            
            
      <input type="hidden" name="radius" id="radius" />
      <input type="hidden" name="lat" id="lat" />
      <input type="hidden" name="lon" id="lon" />
      <input type="hidden" name="size" id="size" />
            <div align='center'>
            <a href="/manager/fences?imei=<?=$imei?>" class=" btn btn-info btn-large"  >BACK</a>
            <a href="#" class=" btn btn-info btn-large" id="save" >SAVE</a>
      <!--<input type='submit' name='add' value='SAVE' class="btn btn-success btn-large"/>-->
      </div>
      
        </td>
   
          <td style="width:75%">
            <input type="text" name="address" id="adress" value="Sydney" style="width:400px; height:40px;" />
          <a class="btn btn-info" id="find">Find</a>
          <a class="btn btn-info" id="clear">Clear fence</a>
            <div id="map_canvas" style="width:100%; height:600px"></div>
          </td>
      </tr>

</table>
<input type='hidden' name='add' />
</form>


<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/css/bootstrap-combined.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" media="screen"
     href="http://tarruda.github.com/bootstrap-datetimepicker/assets/css/bootstrap-datetimepicker.min.css">  
    <script type="text/javascript"
     src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js">
    </script> 
    <script type="text/javascript"
     src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/js/bootstrap.min.js">
    </script>
    <script type="text/javascript"
     src="http://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.min.js">
    </script>
 
    <script type="text/javascript">
      $('#datetimepicker1').datetimepicker({
        format: 'yyyy-MM-dd hh:mm:ss',
        pick12HourFormat: true,
        language: 'en-EN'
      });
      $('#datetimepicker2').datetimepicker({
        format: 'yyyy-MM-dd hh:mm:ss',
        pick12HourFormat: true,
        language: 'en-EN'
      });
    </script>

