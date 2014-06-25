<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
               		<script type="text/javascript">
               		$(document).ready(function() {
                           initialize()
                           getAction();						   
                    });
                    function getAction()
                           {						  
						    var dashboard = $("#dashboard");
							dashboard.load('/ajax/getDashboard',{user_id:<?=$user_id?>,user:true});
							setTimeout('getAction()','15000');
						   }
</script>

<script type="text/javascript">

      function initialize(){

     var markers = [];
     var latlngbounds = new google.maps.LatLngBounds();
     var mapOptions = {
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          maxZoom: 17
      };
      temp = document.getElementById("map_canvas");
      $('#map_canvas').css({"width":"100%","height":"600px"});
      var map = new google.maps.Map(temp,mapOptions);
      
         $.ajax({
                              url: '/ajax/getPointsForDashboard?user_id='+<?=$user_id?>+'&user=true',
                              success: function(JsonData) {
                                 JsonData = JSON.parse(JsonData);
                                 console.log(JsonData);
                                                     
                                 jQuery.each(JsonData, function(i, val) {

                                     if (val.MIL_status=='OFF'){val.MIL_status='No Faults'} else {val.MIL_status='Fault detected'}
                        
                                     var contentString = '<H4>'+val.driver_name+'</H4><H5>'+val.manufacter+'</H5>Status: '+val.trip_status+'<br>Speed: '+val.speed+' km/h<br>Alerts: '+val.alerts+'<br>Driver score: '+val.points+'<br>Engine Health: '+val.MIL_status+'<br>Last update: '+val.lastUpdate;
                                     var infowindow = new google.maps.InfoWindow({
                                          content: contentString
                                     });


                                     var myPos = new google.maps.LatLng(val.latitude,val.longitude);
                                     latlngbounds.extend(myPos);
                                    
                                     var marker = new google.maps.Marker({
                                            position: myPos,
                                            map: map,
                                            icon: "http://www.google.com/mapfiles/marker"+val.imei[0]+".png"
                                     });
                                     google.maps.event.addListener(marker, 'click', function() {
                                         infowindow.open(map,marker);
                                     });
                                    console.log(document.getElementById("test"));

                                     markers.push(marker);
                                 });       
                 map.fitBounds(latlngbounds);

                              }
                            });

          setInterval(function(){
                            $.ajax({
                              url: '/ajax/getPointsForDashboard?user_id='+<?=$user_id?>+'&user=true',
                              success: function(JsonData) {
                                 JsonData = JSON.parse(JsonData);
                                 // Clear all markers
                                   if (markers.length>0){
                                        for (var i = 0; i < markers.length; i++) {
                                    markers[i].setMap(null);
                                  }
                                   }
                                                     
                                 jQuery.each(JsonData, function(i, val) {
                                    if (val.MIL_status=='OFF'){val.MIL_status='No Faults'} else {val.MIL_status='Fault detected'}
                        
                                     var contentString = '<H4>'+val.driver_name+'</H4><H5>'+val.manufacter+'</H5>Status: '+val.trip_status+'<br>Speed: '+val.speed+' km/h<br>Alerts: '+val.alerts+'<br>Driver score: '+val.points+'<br>Engine Health: '+val.MIL_status+'<br>Last update: '+val.lastUpdate;
                                     var infowindow = new google.maps.InfoWindow({
                                          content: contentString
                                     });


                                     var myPos = new google.maps.LatLng(val.latitude,val.longitude);
                                     latlngbounds.extend(myPos);
                                    
                                     var marker = new google.maps.Marker({
                                            position: myPos,
                                            map: map,
                                            icon: "http://www.google.com/mapfiles/marker"+val.imei[0]+".png"
                                     });
                                     google.maps.event.addListener(marker, 'click', function() {
                                         infowindow.open(map,marker);
                                     });
                                  
                                     markers.push(marker);
                                 });       
                         //map.fitBounds(latlngbounds);

                              }
                            });
                       }, '15000');
      }


</script>

<div id="map_canvas"></div>
<br>
<br>
<div id='dashboard'></div>
