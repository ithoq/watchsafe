<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
<script src="/jquery/development-bundle/ui/jquery.alerts.js" type="text/javascript"></script>
<link href="/jquery/css/jquery.alerts-1.1/jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript">
$(document).ready(function() {
	$("#items").change(function() {	
		var item = $("#items option:selected").val();
	    	$("#items_form").submit(); 
	});
	<?php foreach($ViewResult as $row){ ?>
	   $('#del<?=$row->id?>').click(function(){
      jConfirm("Are you sure want to delete this Device?", 'Warning', function(r) {
        if (r) {
          $( "#form<?=$row->id?>" ).submit();
        }
    });  
	   });
	   <?php } ?>		
}
);
</script>
<h3 align=center>System devices</h3>
<div align=center>
<form action='/admin/devices' method='post'>
<input type='submit' name='add_show' value='Add device' class='btn btn-primary btn-large'></input>
</form>
</div>
<?php echo 'Results '.$pagination->current_first_item.'-'.$pagination->current_last_item.' of '.$pagination->total_items;?>
<?=$pagination?>
<br>
<form action='/admin/devices' id='items_form' method='post'>
Items per page - 
<select name='device_items' id='items' class="input-small">
<option value=1 <?=($pagination->items_per_page==1) ? 'selected': ''?>>1</option>
<option value=2 <?=($pagination->items_per_page==2) ? 'selected': ''?>>2</option>
<option value=3 <?=($pagination->items_per_page==3) ? 'selected': ''?>>3</option>
<option value=4 <?=($pagination->items_per_page==4) ? 'selected': ''?>>4</option>
<option value=5 <?=($pagination->items_per_page==5) ? 'selected': ''?>>5</option>
<option value=6 <?=($pagination->items_per_page==6) ? 'selected': ''?>>6</option>
<option value=7 <?=($pagination->items_per_page==7) ? 'selected': ''?>>7</option>
<option value=8 <?=($pagination->items_per_page==8) ? 'selected': ''?>>8</option>
<option value=9 <?=($pagination->items_per_page==9) ? 'selected': ''?>>9</option>
<option value=10 <?=($pagination->items_per_page==10) ? 'selected': ''?>>10</option>
<option value=20 <?=($pagination->items_per_page==20) ? 'selected': ''?>>20</option>
<option value=50 <?=($pagination->items_per_page==50) ? 'selected': ''?>>50</option>
<option value=10000000 <?=($pagination->items_per_page==10000000) ? 'selected': ''?>>All</option>
</select>
</form>
<br>
<form action='/admin/devices' method='post'>
<input type='text' name='search' value=<?=(isset($_SESSION['devices_search'])) ? $_SESSION['devices_search'] : ''?>></input>
<input type='submit' value='Search' class='btn btn-primary btn-small'/>
<input type='submit' name='show_all' value='Show All' class='btn btn-primary btn-small'/>
</form>
<br>

    <?php if (count($ViewResult)>0)
	{
	?>
       <table class='table table-bordered'>
	   <tr>
		   <td>Name</td>
		   <td>Imei</td>
		   <td>ip</td>
		   <td>ip_date</td>
		   <td>Actions</td>
	   </tr>
       
   <?php foreach($ViewResult as $row)
   { 
   ?>
	           
            <tr>			      
				  <td>
                      <?php
					  if (isset($_SESSION['device_search']))
					  {
					  echo str_ireplace($_SESSION['device_search'],'<span style="color:red;">'.$_SESSION['device_search'].'</span>',$row->name);
					  }
					  else
					  { 
					     echo $row->name;
					  }
					  ?>
				  </td>
				  <td>
                      <?php
					  if (isset($_SESSION['device_search']))
					  {
					  echo str_ireplace($_SESSION['device_search'],'<span style="color:red;">'.$_SESSION['device_search'].'</span>',$row->imei);
					  }
					  else
					  { 
					     echo $row->imei;
					  }
					  ?>
				  </td>
				  <td>
				  	  <?=$row->last_ip?>
				  </td>
				  <td>
                      <?=$row->last_ip_date?>
				  </td>
				  <td>
				     <form action='/admin/devices' method='post' id="form<?=$row->id?>"> 
					 <input type='hidden' name='id' value='<?=$row->id?>'></input>
					 <input type='submit' name='edit_show' value='Edit' class='btn btn-primary btn-large'></input>
					 </form>
					 <button id="del<?=$row->id?>" class='btn btn-primary btn-large'>Delete</button>
				  </td>
			</tr>			
	
   <?php }?>  
	   </table>
	
    <?php
	}
	else{
	  echo "<p>No devices found...</p>";
	  //echo "<a href='/admin/l?clear_all_filters=true'>Show all</a>";
	}
    ?>	
