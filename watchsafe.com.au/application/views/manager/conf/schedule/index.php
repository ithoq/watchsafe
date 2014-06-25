<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
<script src="/jquery/development-bundle/ui/jquery.alerts.js" type="text/javascript"></script>
<link href="/jquery/css/jquery.alerts-1.1/jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript">
	$(document).ready(function() {
	  <?php foreach($ViewResult as $row){ ?>
	   $('#del<?=$row->id?>').click(function(){
      jConfirm("Are you sure want to delete this Schedule?", 'Warning', function(r) {
        if (r) {
          $( "#form<?=$row->id?>" ).submit();
        }
    });  
	   });
	   <?php } ?>					   
	});
</script>

<h3><?= $device->name?> schedules</h3>
<br>
<form action='/manager/schedule' method='post'>
<input type='submit' name='add_show' value='Add' class='btn btn-primary btn-large'></input>
</form>

    <?php if (count($ViewResult)>0)
	{
	?>
       <table class='table table-bordered' style="font-size:12px;">
	   <tr>
		   <td>ID</td>
		   <td>Name</td>
		   <td>Date Service Due</td>
		   <td>Kilomenter Service Due (km)</td>
		   <td>Is_alerted</td>
		   <td>Actions</td>
	    </tr>
       
   <?php foreach($ViewResult as $row)
   { 
   ?>
	           
            <tr>   

				  <td><?=$row->id?></td>
				  <td><?=$row->name?></td>
				  <td><?=$row->date_due?></td>
				  <td><?=$row->mil_due?></td>
				  <td><?=$row->is_alert?></td>
                  <td>
				     <form action='/manager/schedule' method='post' id="form<?=$row->id?>"> 
					 <input type='hidden' name='id' value='<?=$row->id?>'></input>
					 <input type='submit' name='edit_show' value='Edit' class='btn btn-primary btn-small'></input><br>
					 </form>
				
					 <button id="del<?=$row->id?>" name="del" class='btn btn-primary btn-small'>Delete</button>
					 
				  </td>
			</tr>			
	
   <?php }?>  
	   </table>
    <?php
	}
	else
	  echo "No schedules found...";
    ?>
    <br>
    <a href="/manager/vehicle?imei=<?=$_SESSION['vehicle_imei']?>" class="btn-link" >back</a>	


