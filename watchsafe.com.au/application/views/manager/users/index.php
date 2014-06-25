<?php
if (isset($nousers)) {echo "No users found..."; exit();}
?>
<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#items").change(function() {	
		var item = $("#items option:selected").val();
	    	$("#items_form").submit(); 
	});
	
	$("#roles").change(function() {	
		var item = $("#roles option:selected").val();
	    	$("#roles_form").submit(); 
	});
	
}
);
</script>
<h3 align=center>Manager <?=$managerInfo->username?> users</h3>
<br>

<?php echo 'Results '.$pagination->current_first_item.'-'.$pagination->current_last_item.' of '.$pagination->total_items;?>
<?=$pagination?>
<br>
<form action='/manager/users' id='items_form' method='post'>
Items per page - 
<select name='items' id='items'>
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

    <?php if (count($ViewResult)>0)
	{
	?>
       <table class='table table-bordered' style="font-size:12px;">
	   <tr>
		   <td>ID</td>
		   <td>Username</td>
		   <td>Email</td>
		   <td>Device IMEI</td>
		   <td>Logins</td>
		   <td>Last login</td>
		   <td>Actions</td>
	    </tr>
       
   <?php foreach($ViewResult as $row)
   { 
   ?>
	           
            <tr>   
				  <td><?=$row['ID']?></td>
				  <td><?=$row['Username']?></td>
				  <td><?=$row['Email']?></td>
				  <td><?=$row['Devices']?></td>		  
				  <td><?=$row['Logins']?></td>
				  <td><?=$row['LastLogin']?></td>
                  <td>
				     <form action='/manager/users' method='post'> 
					 <input type='hidden' name='id' value='<?=$row['ID']?>'></input>
					 <input type='submit' name='reset_password_show' value='Reset password' class='btn btn-primary btn-small'></input>
					 <!--<input type='submit' name='edit_show' value='Edit' class='btn-small'></input>-->
					 </form>
				  </td>
			</tr>			
	
   <?php }?>  
	   </table>
    <?php
	}
	else
	  echo "No users found...";
    ?>
    <a href="/manager/conf" class="btn-link" >back</a>	


