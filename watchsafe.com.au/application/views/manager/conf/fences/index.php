<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
<script src="/jquery/development-bundle/ui/jquery.alerts.js" type="text/javascript"></script>
<link href="/jquery/css/jquery.alerts-1.1/jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript">
	$(document).ready(function() {
	  <?php foreach($FencesList as $row){ ?>
	   $('#del<?=$row->id?>').click(function(){
      jConfirm("Are you sure want to delete this Watch Zone?", 'Warning', function(r) {
        if (r) {
          $( "#form<?=$row->id?>" ).submit();
        }
    });  
	   });
	   <?php } ?>					   
	});
  function initialize(){

  };
</script>

<?php
if (isset($nousers)) {echo "No devices"; exit();}
  if (count($ManagersDevices)>0)
  {
  	 ?>
  	     <a href="/manager/fences?imei=all"><button  <?=('all'==$CurrentDeviceImei) ? "class='btn btn-success'" : "class='btn btn-danger'"?>>All</button></a>
  	 <?php
     foreach ($ManagersDevices as $row) 
     {
     	?>
     	  <a href="/manager/fences?imei=<?=$row->imei?>"><button  <?=($row->imei==$CurrentDeviceImei) ? "class='btn btn-success'" : "class='btn btn-danger'"?>><?=$row->name?></button></a>
     	<?php
     }
     ?>
     <br>
     <h3>Watch Zones setup</h3>
     <br>
     <?php
       // Show fences list
        if (count($FencesList) > 0) {
        	?>
            <table class='table table-bordered' style="font-size:15px;">
		     <tr>
		         <td>Watch Zone name</td>
		         <td>Watch Zones type</td>
		         <td>Usage type</td>
		         <td>Settings</td>
		     </tr>
        	<?php
        foreach($FencesList as $row){
        	?>
        	<form action='/manager/fences' method='post' id='form<?=$row->id?>'>
        	<tr>
		         <td><?=$row->name?></td>
		         <td><?=$row->type?></td>
		         <td>
		             <?php
		             if ($row->type=='overspeed')
		             {
                        echo $row->overspeed." KM/h";
		             } else {
                        if (($row->time_start=='0000-00-00 00:00:00') and ($row->time_end=='0000-00-00 00:00:00')) {
                        	echo "All Hours";
                        } else {
                        	echo "From ".$row->time_start." to ".$row->time_end;
                        }
		             }
		             ?>
		         </td>
		         <td>
                 <input type="hidden" name="id" value=<?=$row->id?> />
                 <input type="hidden" name="imei" value=<?=$CurrentDeviceImei?> />
		             <input type="submit" value="View" name="edit_show" class='btn btn-info' />
		         </form>    
		             <button name="del_show" id="del<?=$row->id?>" class='btn btn-danger'>Delete</button>
		         </td>
        	</tr>
        	
        	<?php
        }
        } else {
        	echo "No fences";
        }
     ?>
     </table>  
     <br>
  <?php
  }
  else
  {
  	echo "No devices";
  }
?>
<form action='/manager/fences' method='post'>
   <input type="hidden" name="imei" value="<?=$CurrentDeviceImei?>" />
   <input type="submit" value="Add" name="add_show" class="btn btn-info" />
</form>
<a href="/manager/conf" class="btn-link" >back</a>