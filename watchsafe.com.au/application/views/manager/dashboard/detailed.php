   <script type="text/javascript">
   function initialize(){

   }
   </script>
   <table class='table table-bordered'>
   	 <tr>
   	 	<td>
   	         <a href="#" class="btn btn-danger btn-small"><?=$HeaderResult['name']?></a>
   	    </td>
   	    <td>
             <?=$HeaderResult['points'].' Points'?>
   	    </td>
   	    <td>
             <?=$HeaderResult['trips'].' Trips'?>
   	    </td>
   	    <td>
             <?=($HeaderResult['mileage']/1000).' KM'?>
   	    </td>
   	</tr>
   </table>

<form action="/manager/dashboard" method="get">
<input type='hidden' name='imei' value='<?=$HeaderResult['imei']?>'></input>
<input type='hidden' name='name' value='<?=$HeaderResult['name']?>'></input>
<input type='hidden' name='points' value='<?=$HeaderResult['points']?>'></input>
<input type='hidden' name='trips' value='<?=$HeaderResult['trips']?>'></input>
<input type='hidden' name='mileage' value='<?=$HeaderResult['mileage']?>'></input>    
   <table style="margin: 20px 0;">
    <tr>
      <td >
          <div id="datetimepicker1" class="input-append date">
            <input type="text" name='start' value="<?=isset($HeaderResult['start']) ? $HeaderResult['start'] : ''?>"></input>
            <span class="add-on">
              <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
            </span>
          </div>
     </td>
     <td>
          <div id="datetimepicker2" class="input-append date">
            <input type="text" name='end' value="<?=isset($HeaderResult['end']) ? $HeaderResult['end'] : ''?>" ></input>
            <span class="add-on">
              <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
            </span>
          </div>
    </td>
    <td style="padding: 0 20px;">
         <button type='submit' class="btn btn-info btn-small">Show</button>
    </td>
    <td style="padding: 0 20px;">
        <a href="/manager/dashboard?imei=<?=$HeaderResult['imei']?>&name=<?=$HeaderResult['name']?>&points=<?=$HeaderResult['points']?>&trips=<?=$HeaderResult['trips']?>&mileage=<?=$HeaderResult['mileage']?>&show_all=true" class="btn btn-info btn-small">Show all datas</a>
    </td>
    <td style="padding: 0 20px;">
       <a href="/manager/dashboard?imei=<?=$HeaderResult['imei']?>&name=<?=$HeaderResult['name']?>&points=<?=$HeaderResult['points']?>&trips=<?=$HeaderResult['trips']?>&mileage=<?=$HeaderResult['mileage']?>" class="btn btn-info btn-small">Clear all filters</a>
    </td>
    <td >
       <button type='submit' class="btn btn-info btn-small" name='csv'>Download CSV</button>
    </td>
  </tr>
  </table> 
</form>
 <h3>Journey Records  <small>Summary of vehicle trips</small></H3>
<?php if (count($ViewResult)>0) {?>
   <table class='table table-bordered'>
   <tr>
   	   <td>Trip date a time</td>
   	   <td>Duration</td>
       <td>Driver Score</td>
       <td>Alerts</td>
   	   <td>Distance Travelled</td>
   	   <td>Odometer</td>
   	   <td>Fuel Used</td>
   	   <td>Map trip</td>
   </tr>       
   <?php foreach($ViewResult as $row)
   { 
   ?>
	           
            <tr>			      
				     <td><?=isset($row['trip_start']) ? $row['trip_start'] : 'Driving'?></td>
			   	   <td><?=isset($row['trip_duration']) ? convert_duration($row['trip_duration']): 'Driving'?></td>
             <td><?=isset($row['points']) ? $row['points']: 'Driving'?></td>
             <td><?=isset($row['alerts']) ? $row['alerts']: 'Driving'?></td>
			   	   <td><?=isset($row['trip_mileage']) ? round(($row['trip_mileage']/1000),2).' KM' : 'Driving'?></td>
			   	   <td><?=isset($row['OBD_Odometer']) ? round($row['OBD_Odometer'],2).' KM' : 'Driving'?></td>
			   	   <td><?=isset($row['Total_fuel_used']) ? $row['Total_fuel_used'].' L' : 'Driving'?></td>
			   	   <!--<td><a href="#mapModal" class="btn btn-info">Maps</a></td>-->
             <td><a href="#mapModal<?=$row['id']?>" role="button" class="btn btn-info" data-toggle="modal" style="margin:0 10 0 10;">Maps</a></td>
			</tr>			
	
   <?php }?>  
  </table>
<?php } else {?>
No trips  
<?php }?>
<a href="/manager/dashboard" class="btn btn-info btn-small">Back</a>

 <h3> Last 4 Weekly scores <small>Summary of vehicle trips</small></H3>
<?php if (count($weeklyScoresResult)>0) {?>
   <table class='table table-bordered'>
   <tr>
       <td>Week</td>
       <td>Driver Score</td>
       <td>Awards</td>
       <td>Graph</td>
   </tr>       
   <?php foreach($weeklyScoresResult as $row)
   { 
   ?>
             
            <tr>            
             <td><?=($row['week_start'])?></td>
             <td><a href="#myWeeklyModal<?=$row['id']?>" role="button" class="btn btn-info" data-toggle="modal" style="margin:0 10 0 10;"><?=($row['score'])?></a></td>
             <td>Awards</td>
             <td><a href="#" class="btn btn-info">Graph</a></td>
      </tr>     
  
   <?php }?>  
  </table>
<?php } else {?>
No scores  
<?php }?>
<a href="/manager/dashboard" class="btn btn-info btn-small">Back</a>
<h3>Alerts <small>Summary of driving and vehicle incidents</small></H3>
<?php if (count($AlertResult)>0) {?>
   <table class='table table-bordered'>
   <tr>
       <td>Alert Type</td>
       <td>Date and time</td>
       <td>Location</td>
       <td>More Information</td>
   </tr>       
   <?php foreach($AlertResult as $row)
   { 
   ?>
             
            <tr>
             <td><?=isset($row['Type']) ? $row['Type']: ''?></td>            
             <td><?=isset($row['Date']) ? $row['Date'] : ''?></td>
             <td><a href="#" class="btn btn-danger">Map</a></td>
             <td><?=isset($row['Data']) ? $row['Data'] : ''?></td>   
      </tr>     
  
   <?php }?>  
  </table>
<?php } else {?>
No Alerts 
<?php }?>

<a href="/manager/dashboard" class="btn btn-info btn-small">Back</a>
<?php
function convert_server_date($date)
{
  $date = explode(' ',$date);
  $month = explode('-',$date[0]);
  $days = explode(':',$date[1]);
  
  $timestamp = mktime ($days[0]+9,$days[1],$days[2],$month[1],$month[2],$month[0]);
  return date('jS  F Y h:i:s A',$timestamp);
  //echo $timestamp;
}
function convert_duration($time)
{
  if ($time!=0)
  {
  $hours = floor($time/3600);
  $min = floor(($time%3600)/60);
  $sec = $time%60;

  ($hours==0) ? $hours='' : $hours.=' h';
  ($min==0) ? $min='' : $min.=' min';
  ($sec==0) ? $sec='' : $sec.=' sec';
  return $hours.' '.$min.' '.$sec;
  }
  else
  {
    return '0 sec';
  }  
}
?>

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

    <!-- Weekly Modal -->
<?php foreach ($WeeklyModalData as $row) {?>    
<div id="myWeeklyModal<?=$row['id']?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3 id="myWeeklyModal">Detailed Info</h3>
</div>
<div class="modal-body">
<p><?="Score - ".$row['score']?></p>
<p><?="Trips - ".$row['trips']?></p>
<p><?="Distance - ".$row['mileage']?></p>
</div>
<div class="modal-footer">
<button data-dismiss="modal" class="btn btn-primary">Close</button>
</div>
</div>
<?php }?>

    <!-- Modal -->
<?php foreach ($ModalData as $row) {?>    
<div id="myModal<?=$row['ID']?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3 id="myModalLabel"><?=$row['Info']?></h3>
</div>
<div class="modal-body">
<p><?=$row['Info'].' - '.$row['Data']?></p>
</div>
<div class="modal-footer">
<button data-dismiss="modal" class="btn btn-primary">Close</button>
</div>
</div>
<?php }?>

<!-- Maps modal-->
<?php foreach ($MapModalDatas as $index => $value) {?>  
<div id="mapModal<?=$index?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width:1000px; margin: 0 -500px;">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3 id="myModalLabel">Map</h3>
</div>
<div class="modal-body">
<script type="text/javascript">

   $('#mapModal<?=$index?>').on('shown', function () {
            var mapOptions = {
                zoom: 8,
                center: new google.maps.LatLng(-34.397, 150.644),
                mapTypeId: google.maps.MapTypeId.ROADMAP
              };
            var map = new google.maps.Map(document.getElementById("map_canvas<?=$index?>"), mapOptions);  

            var latlngbounds = new google.maps.LatLngBounds();
            var path = [], service = new google.maps.DirectionsService();
            var lastLatLon;
            /*
             <?php
            $counter = 0;
            foreach ($value['coordinates'] as $key) { 
                $temp = explode(',',$key); ?>
                var latLng = new google.maps.LatLng(<?=$temp[0]?>,<?=$temp[1]?>); 
                   latlngbounds.extend(latLng);   
                var request = {
                  origin:latLng, 
                  destination:latLng,
                  travelMode: google.maps.DirectionsTravelMode.DRIVING
                };

                service.route(request, function(response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    var marker = new google.maps.Marker({
                       position: response.routes[0].legs[0].start_location, 
                       map: map,
                       title:"Hello World!"
                    });
                } else {
                    var marker = new google.maps.Marker({
                       position: latLng,
                       map: map,
                       title:"Hello World!"
                    });
                }
              });

                <?php }?>
                map.fitBounds(latlngbounds);
                */
            
            <?php
            $counter = 0;
            foreach ($value['coordinates'] as $key) {
                //var_dump($key);
                //exit(); 
                $temp = explode(',',$key);?>
            var latLng = new google.maps.LatLng(<?=$temp[0]?>,<?=$temp[1]?>); 
            latlngbounds.extend(latLng);
            path.push(latLng);   
            
            <?php if (isset($temp[2]) and ($temp[2]!='null')){ ?>
                   var marker = new google.maps.Marker({
                        position: latLng,
                        icon: "http://www.google.com/mapfiles/markerA.png",
                        title: "<?=$temp[2]?> <?=(isset($temp[3])) ? '\nInfo:\n'.$temp[3] : ''?>",
                        map: map
                      });    
            <?php } else {?>

               <?php if ($counter==0) { ?>
                     var marker = new google.maps.Marker({
                        position: latLng,
                        icon: "http://www.google.com/mapfiles/dd-start.png",
                        title: "Start of Trip",
                        map: map
                      });
               <?php } else {?>
                      var marker = new google.maps.Marker({
                        position: latLng,
                        icon: "http://labs.google.com/ridefinder/images/mm_20_black.png",
                        title: "Date and time : <?=$temp[4]?>\nSpeed : <?=$temp[5]?>\nThrottle : <?=$temp[6]?>\nRPM : <?=$temp[7]?>",
                        map: map
                      });
                <?php } }?>
               lastLatLon = latLng;      

            <?php $counter++; } ?>

            var marker = new google.maps.Marker({
                        position: lastLatLon,
                        icon: "http://www.google.com/mapfiles/dd-end.png",
                        title: "End of trip",
                        map: map
                      });
              map.fitBounds(latlngbounds);

            var flightPath = new google.maps.Polyline({
              path: path,
              strokeColor: '#FF0000',
              strokeOpacity: 1.0,
              strokeWeight: 2
            });

            flightPath.setMap(map);

              

        });

</script>
<div id = "map_canvas<?=$index?>" style="width:100%;height:400px;"></div>
</div>
<?php 
$temp = explode(',',$value['detailed']);
//var_dump($temp);
//exit();
?>
<table class='table table-bordered'>
  <tr>
     <td>Trip date and time</td>
     <td>Duration</td>
     <td>Driver score</td>
     <td>Alerts</td>
     <td>Distance traveled</td>
     <td>Odometer</td>
     <td>Fuel used</td>
  </tr>
  <tr>            
     <td><?=$temp[0]?></td>
     <td><?=convert_duration($temp[1])?></td>
     <td><?=$temp[2]?></td>
     <td><?=$temp[3]?></td>
     <td><?=round($temp[4]/1000,2)?> KM</td>
     <td><?=$temp[5]?> KM</td>
     <td><?=$temp[6]?> L</td>      
  </tr>
</table>             
<div class="modal-footer">
<button data-dismiss="modal" class="btn btn-primary">Close</button>
</div>
</div>
<?php 
} 
?>






 
