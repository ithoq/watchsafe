<?php
if (isset($nousers)) {echo "No devices"; exit();}
?>
<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#schedule").change(function() {	
		var type = $("#schedule option:selected").val();
		if (type==2)
		{
           $("#schedule_button").css("display","inline");
		}
		else
		{
           $("#schedule_button").css("display","none");
		}
	});
}
);
</script>
<?php
//var_dump($CurrentDevice);
//exit();
?>
<h3>Vehicle setup</h3>
<?php
  if (count($ManagersDevices)>0)
  {
     foreach ($ManagersDevices as $row) 
     {
     	?>
     	  <a href="/manager/vehicle?imei=<?=$row->imei?>"><button  <?=($row->imei==$CurrentDevice->imei) ? "class='btn btn-success'" : "class='btn btn-danger'"?>><?=$row->name?></button></a>
     	<?php
     }
     ?> 
     <br>
     <br>
     <form action='/manager/vehicle' method="post"> 
     <input class="span4" type="text"   name="number_plate" <?=($CurrentDevice->number_plate!==NULL) ? "value='".$CurrentDevice->number_plate."'" : "placeholder='Number Plate'" ?> > </input><br>
     <input class="span4" type="text"   name="odometer" <?=($CurrentDevice->odometer!=0) ? "value='".$CurrentDevice->odometer."'" : "placeholder='Current Vehicle Odometer(0 by default)'" ?>> </input> <?=(isset($errors['odometer']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['odometer'].'</span>' : '')?> <br>
     <input class="span4" type="text"   name="drivers_name" <?=($CurrentDevice->drivers_name!==NULL) ? "value='".$CurrentDevice->drivers_name."'" : "placeholder='Drivers Name'" ?>> </input><br>
     <input class="span4" type="email"  name="drivers_email" <?=($CurrentDevice->drivers_email!==NULL) ? "value='".$CurrentDevice->drivers_email."'" : "placeholder='Drivers Email Address'" ?> > </input> <?=(isset($errors['drivers_email']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['drivers_email'].'</span>' : '')?> <br>
	     <select class="span4" name='manufacter'>
	        <option selected value='' >Manufacter</option>
	        <option value='Alfa Romeo' <?=($CurrentDevice->manufacter=='Alfa Romeo') ? 'selected' : ''?>>Alfa Romeo</option>
	        <option value='Audi' <?=($CurrentDevice->manufacter=='Audi') ? 'selected' : ''?>>Audi</option>
	        <option value='AMG' <?=($CurrentDevice->manufacter=='AMG') ? 'selected' : ''?>>AMG</option>
	        <option value='Aston Martin' <?=($CurrentDevice->manufacter=='Aston Martin') ? 'selected' : ''?>>Aston Martin</option>
			<option value='BMW' <?=($CurrentDevice->manufacter=='BMW') ? 'selected' : ''?>>BMW</option>
			<option value='Chrysler' <?=($CurrentDevice->manufacter=='Chrysler') ? 'selected' : ''?>>Chrysler</option>
			<option value='Daihatsu' <?=($CurrentDevice->manufacter=='Daihatsu') ? 'selected' : ''?>>Daihatsu</option>
			<option value='Dodge' <?=($CurrentDevice->manufacter=='Dodge') ? 'selected' : ''?>>Dodge</option>
			<option value='Ferrari' <?=($CurrentDevice->manufacter=='Ferrari') ? 'selected' : ''?>>Ferrari</option>
			<option value='Fiat' <?=($CurrentDevice->manufacter=='Fiat') ? 'selected' : ''?>>Fiat</option>
			<option value='Ford' <?=($CurrentDevice->manufacter=='Ford') ? 'selected' : ''?>>Ford</option>
			<option value='Ford Performance Vehicles' <?=($CurrentDevice->manufacter=='Ford Performance Vehicles') ? 'selected' : ''?>>Ford Performance Vehicles</option>
			<option value='GM' <?=($CurrentDevice->manufacter=='GM') ? 'selected' : ''?>>GM</option>
			<option value='Great Wall' <?=($CurrentDevice->manufacter=='Great Wall') ? 'selected' : ''?>>Great Wall</option>
			<option value='Honda' <?=($CurrentDevice->manufacter=='Honda') ? 'selected' : ''?>>Honda</option>
			<option value='Holden' <?=($CurrentDevice->manufacter=='Holden') ? 'selected' : ''?>>Holden</option>
			<option value='Hyundai' <?=($CurrentDevice->manufacter=='Hyundai') ? 'selected' : ''?>>Hyundai</option>
			<option value='Jaguar' <?=($CurrentDevice->manufacter=='Jaguar') ? 'selected' : ''?>>Jaguar</option>
			<option value='Jeep' <?=($CurrentDevice->manufacter=='Jeep') ? 'selected' : ''?>>Jeep</option>
			<option value='Lancia' <?=($CurrentDevice->manufacter=='Lancia') ? 'selected' : ''?>>Lancia</option>
			<option value='Land Rover' <?=($CurrentDevice->manufacter=='Land Rover') ? 'selected' : ''?>>Land Rover</option>
			<option value='Lexus' <?=($CurrentDevice->manufacter=='Lexus') ? 'selected' : ''?>>Lexus</option>
			<option value='Mazda' <?=($CurrentDevice->manufacter=='Mazda') ? 'selected' : ''?>>Mazda</option>
			<option value='Maserati' <?=($CurrentDevice->manufacter=='Maserati') ? 'selected' : ''?>>Maserati</option>
			<option value='Mercedes-Benz' <?=($CurrentDevice->manufacter=='Mercedes-Benz') ? 'selected' : ''?>>Mercedes-Benz</option>
			<option value='Mini' <?=($CurrentDevice->manufacter=='Mini') ? 'selected' : ''?>>Mini</option>
			<option value='Mitsubishi' <?=($CurrentDevice->manufacter=='Mitsubishi') ? 'selected' : ''?>>Mitsubishi</option>
			<option value='Nissan' <?=($CurrentDevice->manufacter=='Nissan') ? 'selected' : ''?>>Nissan</option>
			<option value='Peugeot' <?=($CurrentDevice->manufacter=='Peugeot') ? 'selected' : ''?>>Peugeot</option>
			<option value='Porsche' <?=($CurrentDevice->manufacter=='Porsche') ? 'selected' : ''?>>Porsche</option>
			<option value='Range Rover' <?=($CurrentDevice->manufacter=='Range Rover') ? 'selected' : ''?>>Range Rover</option>
			<option value='Renault' <?=($CurrentDevice->manufacter=='Renault') ? 'selected' : ''?>>Renault</option>
			<option value='Rolls Royce' <?=($CurrentDevice->manufacter=='Rolls Royce') ? 'selected' : ''?>>Rolls Royce</option>
			<option value='Rover' <?=($CurrentDevice->manufacter=='Rover') ? 'selected' : ''?>>Rover</option>
			<option value='Saab' <?=($CurrentDevice->manufacter=='Saab') ? 'selected' : ''?>>Saab</option>
			<option value='Seat' <?=($CurrentDevice->manufacter=='Seat') ? 'selected' : ''?>>Seat</option>
			<option value='Skoda' <?=($CurrentDevice->manufacter=='Skoda') ? 'selected' : ''?>>Skoda</option>
			<option value='Subaru' <?=($CurrentDevice->manufacter=='Subaru') ? 'selected' : ''?>>Subaru</option>
			<option value='SuzukiToyota' <?=($CurrentDevice->manufacter=='SuzukiToyota') ? 'selected' : ''?>>SuzukiToyota</option>
			<option value='Volkswagen' <?=($CurrentDevice->manufacter=='Volkswagen') ? 'selected' : ''?> >Volkswagen</option>
	     </select><br>
     <input class="span4" type="text" name="model" <?=($CurrentDevice->model!==NULL) ? "value='".$CurrentDevice->model."'" : "placeholder='Model'" ?> > </input><br>
     <input class="span4" type="email" name="service_email" <?=($CurrentDevice->service_email!==NULL) ? "value='".$CurrentDevice->service_email."'" : "placeholder='Service Center Email Address'" ?> > </input> <?=(isset($errors['service_email']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['service_email'].'</span>' : '')?> <br>
        <select class="span4" name='notification_type'>
	        <option value='0' selected >Notification Setup (disabled by default)</option>
	        <option value='3' <?=($CurrentDevice->notification_type=='3') ? 'selected' : ''?> >After Each Trip</option>
	        <option value='2' <?=($CurrentDevice->notification_type=='2') ? 'selected' : ''?> >Only If Alert</option>
	        <option value='1' <?=($CurrentDevice->notification_type=='1') ? 'selected' : ''?> >Disable</option>
	    </select><br>
	    <select class="span4" name='schedule_type' id='schedule'>
	        <option value='0' selected>Vehicle Service Schedule (disabled by default)</option>
	        <option value='2' <?=($CurrentDevice->schedule_type=='2') ? 'selected' : ''?> >Enable</option>
	        <option value='1' <?=($CurrentDevice->schedule_type=='1') ? 'selected' : ''?> >Disable</option>
	    </select>
	     <?php if ($CurrentDevice->schedule_type=='2') {?>
         <span id='schedule_button'> <a href="/manager/schedule?imei=<?=$CurrentDevice->imei?>">Show schedules</a></span>
         <?php } else { ?>
         <span id='schedule_button' style="display:none;"> <a href="/manager/schedule?imei=<?=$CurrentDevice->imei?>">Show schedules</a></span>
         <?php }?>
	    <br>

	    <button type="submit" class="btn btn-primary">Save</button> 
	    </form>    

  <?php
  }
  else
  {
  	echo "No devices";
  }
?>
<a href="/manager/conf" class="btn-link" >back</a>