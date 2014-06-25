<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#items").change(function() {	
		var item = $("#items option:selected").val();
	    	$("#items_form").submit(); 
	});
	
	$("#device").change(function() {	
		var item = $("#device option:selected").val();
	    	$("#device_form").submit(); 
	});

	$("#message_type").change(function() {	
		var item = $("#message_type option:selected").val();
	    	$("#message_type_form").submit(); 
	});
	
}
);
</script>
<h3 align=center>Devices reports</h3>
<?php echo 'Results '.$pagination->current_first_item.'-'.$pagination->current_last_item.' of '.$pagination->total_items;?>
<?=$pagination?>
<form action='/admin/deviceTest' id='items_form' method='post'>
Items per page - 
<select name='items' id='items'>
<option value=10 <?=($pagination->items_per_page==10) ? 'selected': ''?>>10</option>
<option value=50 <?=($pagination->items_per_page==50) ? 'selected': ''?>>50</option>
<option value=100 <?=($pagination->items_per_page==100) ? 'selected': ''?>>100</option>
<option value=200 <?=($pagination->items_per_page==200) ? 'selected': ''?>>200</option>
<option value=500 <?=($pagination->items_per_page==500) ? 'selected': ''?>>500</option>
</select>
</form>

<form action='/admin/deviceTest' method='post'>
  <input type='submit' name='show_all' value='Clear all filters' class='btn btn-primary btn-small' ></input>
  <input type='submit' name='clear' value='Clear all data' class='btn btn-primary btn-small' ></input>
</form>
<!--
<fieldset>
	<legend>Send commands</legend>
</fieldset>
-->
<div align=center>
 <div>Number of records:<?=$Count?>, Size - <?=$Size?> kb</div>
    <?php if (count($ViewResult)>0)
	{
		
	?>
       <table class='table table-bordered' style="font-size:10px;">
	   <tr >
		   <td>Modem_ID
                          <form action='/admin/deviceTest' method='post' id='device_form'>
						  <select id='device' name='device_filter'>
						  <option value='all' <?=($_SESSION['matt1_device_filter']=='all') ? 'selected':''?>>All</option>
						  <?php foreach($DevicesList as $value) {?>
						  <option value='<?=$value->Modem_ID?>' <?=($_SESSION['matt1_device_filter']==$value->Modem_ID) ? 'selected':''?>><?=$value->Modem_ID?></option>
						  <?php }?>
						  </select>
						  </form>
		   </td>
		   <td>GPS_DataTime</td>
		   <td>Longitude</td>
		   <td>Latitude</td>
		   <td>Speed</td>
		   <td>Direction</td>
		   <td>Altitude</td>
		   <td>Satellites</td>
		   <td>Message_ID
		   	              <form action='/admin/deviceTest' method='post' id='message_type_form'>
						  <select id='message_type' name='message_type_filter'>
						  <option value='all' <?=($_SESSION['matt1_message_type_filter']=='all') ? 'selected':''?>>All</option>
						  <?php foreach($MessagesTypesList as $value) {?>
						  <option value='<?=$value->Message_ID?>' <?=($_SESSION['matt1_message_type_filter']==$value->Message_ID) ? 'selected':''?>><?=$value->Message_ID?></option>
						  <?php }?>
						  </select>
						  </form>
		   </td>
		   <td>Input_Status</td>
		   <td>Output_Status</td>
		   <td>Analog_Input1</td>
		   <td>Analog_Input2</td>
		   <td>RTC_DataTime</td>   
		   <td>Mileage</td>

		   <td>Main_Battery</td>
		   <td>Back_Up_Battery</td>
		   <td>Over_RPM</td>
		   <td>Text</td>
		   <td>Over_TEMP</td>
		   <td>Over_STEP</td>
		   <td>MAX_Speed</td>
		   <td>AVG_Speed</td>
		   <td>MAX_Speed_Duration</td>
		   <td>X_Axis</td>
		   <td>Y_Axis</td>
		   <td>Z_Axis</td>
		   <td>Geo Fence Index</td>         
		   
		   <td>(MIL)status</td>
		   <td>Number_of_DTC_codes</td>
		   <td>Engine load</td>
		   <td>Engine coolant</td>
		   <td>Fuel pressure</td>
		   <td>Intake manifold pressure</td>
		   <td>Engine RPM</td>
		   <td>ODB Speed</td>
		   <td>Intake temp</td>
		   <td>MAF air flow rate</td>
		   <td>Throttle position</td>
		   <td>Run time since engine start</td>
		   <td>Distance travelled with error</td>
		   <td>Fuel level</td>
		   <td>Barometric pressure</td>
		   <td>Control module voltage</td>
		   <td>Air temp</td>
		   <td>Accel pedal pos</td>
		   <td>Total fuel used</td>
		   <td>OBD Odometer</td>
		   <td>Server date</td>


	    </tr>
       
   <?php foreach($ViewResult as $row)
   { 
   ?>      
		<tr>
				  <td><?=$row->Modem_ID?></td>
				  <td><?=$row->GPS_DataTime?></td>
				  <td><?=$row->Longitude?></td>
				  <td><?=$row->Latitude?></td>
				  <td><?=$row->Speed?></td>
				  <td><?=$row->Direction?></td>
				  <td><?=$row->Altitude?></td>
				  <td><?=$row->Satellites?></td>
				  <td><?=$row->Message_ID?></td>
				  <td><?=$row->Input_Status?></td>
				  <td><?=$row->Output_Status?></td>
				  <td><?=$row->Analog_Input1?></td>
				  <td><?=$row->Analog_Input2?></td>
				  <td><?=$row->RTC_DataTime?></td>
				  <td><?=$row->Mileage?></td>

                  <td style="color:green"><?=$row->Main_Battery?></td>
                  <td style="color:green"><?=$row->Back_Up_Battery?></td>
                  <td style="color:green"><?=$row->Over_RPM?></td>
                  <td style="color:green"><?=$row->Text?></td>
                  <td style="color:green"><?=$row->Over_TEMP?></td>
                  <td style="color:green"><?=$row->Over_STEP?></td>
                  <td style="color:green"><?=$row->MAX_Speed?></td>
                  <td style="color:green"><?=$row->AVG_Speed?></td>
                  <td style="color:green"><?=$row->MAX_Speed_Duration?></td>
                  <td style="color:green"><?=$row->X_Axis?></td>
                  <td style="color:green"><?=$row->Y_Axis?></td>
                  <td style="color:green"><?=$row->Z_Axis?></td>
                  <td style="color:green"><?=$row->geo_fence_index?></td>

				  <td><?=$row->MIL_status?></td>
				  <td><?=$row->number_of_DTC_codes?></td>
				  <td><?=$row->Engine_load?></td>
				  <td><?=$row->Engine_coolant?></td>
				  <td><?=$row->Fuel_pressure?></td>
				  <td><?=$row->Intake_manifold_pressure?></td>
				  <td><?=$row->Engine_RPM?></td>
				  <td><?=$row->ODB_Speed?></td>
				  <td><?=$row->Intake_temp?></td>
				  <td><?=$row->MAF_air_flow_rate?></td>
				  <td><?=$row->Throttle_position?></td>
				  <td><?=$row->Run_time_since_engine_start?></td>
				  <td><?=$row->Distance_travelled_with_error?></td>
				  <td><?=$row->Fuel_level?></td>
				  <td><?=$row->Barometric_pressure?></td>
				  <td><?=$row->Control_module_voltage?></td>
				  <td><?=$row->Air_temp?></td>
				  <td><?=$row->Accel_pedal_pos?></td>
				  <td><?=$row->Total_fuel_used?></td>
				  <td><?=$row->OBD_Odometer?></td>
				  <td><?=$row->server_date?></td>
				

			</tr>			
	
   <?php }?>  
	   </table>
    <?php
	}
	else
	  echo "<h3 align=center>No data...</h3>";
    ?>	
</div>
<?php
function convert_server_date($date)
{
	$date = explode(' ',$date);
	$month = explode('-',$date[0]);
	$days = explode(':',$date[1]);
	
	$timestamp = mktime ($days[0]+9,$days[1],$days[2],$month[1],$month[2],$month[0]);
	return date('d/m/Y h:i:s A',$timestamp);
	//echo $timestamp;
}
function make_data($date)
{

      $str = $date;
      $arr = str_split($str);
      $res_arr = array();

      foreach ($arr as $key => $value) 
      {
        if (($key==4) or ($key==6) or ($key==8) or ($key==10) or ($key==12))
        {
          $res_arr[] = '-';
        } 
          $res_arr[] = $value;   
      }
      
      $str='';
      foreach ($res_arr as $key => $value) 
      {
        $str.=$value;
      }

      $res_arr = explode('-',$str);
      //var_dump($res_arr);
      //exit();

      $temp_arr['day'] = $res_arr[2]; 
      $temp_arr['month'] = $res_arr[1];
      $temp_arr['year'] = $res_arr[0];

      return $res_arr[2].'/'.$res_arr[1].'/'.$res_arr[0].' '.$res_arr[3].':'.$res_arr[4].':'.$res_arr[5];
}
?>


