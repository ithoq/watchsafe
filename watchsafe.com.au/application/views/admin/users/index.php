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
<h3 align=center>System users</h3>
<br>
<form action='/admin/register' method='post'>
<input type='submit' value='Add user' class='btn btn-primary btn-large'></input>
</form>
<br>
<?php echo 'Results '.$pagination->current_first_item.'-'.$pagination->current_last_item.' of '.$pagination->total_items;?>
<?=$pagination?>
<br>
<form action='/admin/users' id='items_form' method='post'>
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

<form action='/admin/users' method='post'>
<input type='text' name='search' value=<?=(isset($_SESSION['users_search'])) ? $_SESSION['users_search'] : ''?>></input>
<input type='submit' value='Search' class='btn btn-primary btn-small'/>
<input type='submit' name='show_all' value='Show All' class='btn btn-primary btn-small'/>
</form>
<br>

    <?php if (count($ViewResult)>0)
	{
	?>
       <table class='table table-bordered' style="font-size:12px;">
	   <tr>
		   <td><a href='/admin/users?users_order_val=id'>ID</a></td>
		   <td>UserRole
		                  <form action='/admin/users' method='post' id='roles_form'>
						  <select id='roles' name='roles_filter' class="span1">
						  <option value='all' <?=($_SESSION['users_roles_filter']=='all') ? 'selected':''?>>All</option>
						  <option value='admin' <?=$_SESSION['users_roles_filter']=='admin' ? 'selected':''?>>Admin</option>
						  <option value='fleet manager' <?=$_SESSION['users_roles_filter']=='manager' ? 'selected':''?>>Manager</option>
						  <option value='user' <?=$_SESSION['users_roles_filter']=='user' ? 'selected':''?>>User</option>
						  </select>
						  </form>
		   </td>
		   <td><a href='/admin/users?users_order_val=username'>Username</a></td>
		   <td><a href='/admin/users?users_order_val=email'>Email</a></td>
		   <td>Devices</td>
		   <td>Fullname</td>
		   <td>Phone</td>
		   <td><a href='/admin/users?users_order_val=DOB'>DOB</a></td>
		   <td>Address</td>
		   <td><a href='/admin/users?users_order_val=logins'>Logins</a></td>
		   <td>Last login</td>
		   <td>Actions</td>
	    </tr>
       
   <?php foreach($ViewResult as $row)
   { 
   ?>
	           
            <tr <?php if ($row['Suspend']==1) { ?>style="background-color:#880000 !important;" <?php } ?>>   
				  <td><?=$row['ID']?></td>
				  <td><?=$row['UserRoles']?>
				  </td>				  
				  <td>
					  <?php
					  if (isset($_SESSION['users_search']))
					  {
					  echo str_ireplace($_SESSION['users_search'],'<span style="color:red;">'.$_SESSION['users_search'].'</span>',$row['Username']);
					  }
					  else
					  { 
					     echo $row['Username'];
					  }
					  ?>
				  </td>
				  <td>
					  <?php
					  if (isset($_SESSION['users_search']))
					  {
					  echo str_ireplace($_SESSION['users_search'],'<span style="color:red;">'.$_SESSION['users_search'].'</span>',$row['Email']);
					  }
					  else
					  { 
					     echo $row['Email'];
					  }
					  ?>
				  </td>
				  <td><?=$row['Devices']?></td>
				  <td>
					  <?php
					  if (isset($_SESSION['users_search']))
					  {
					  echo str_ireplace($_SESSION['users_search'],'<span style="color:red;">'.$_SESSION['users_search'].'</span>',$row['Fullname']);
					  }
					  else
					  { 
					     echo $row['Fullname'];
					  }
					  ?>
				  </td>
				  <td><?=$row['Phone']?></td>
				  <td><?=$row['DOB']?></td>
				  <td>
					  <?php
					  if (isset($_SESSION['users_search']))
					  {
					  echo str_ireplace($_SESSION['users_search'],'<span style="color:red;">'.$_SESSION['users_search'].'</span>',$row['Address']);
					  }
					  else
					  { 
					     echo $row['Address'];
					  }
					  ?>
				  </td>				  
				  <td><?=$row['Logins']?></td>
				  <td><?=$row['LastLogin']?></td>
                  <td>
				     <form action='/admin/users' method='post'> 
					 <input type='hidden' name='id' value='<?=$row['ID']?>'></input>
					 <input type='submit' name='reset_password_show' value='Reset password' class='btn btn-primary btn-small'></input><br>
					 <?php if($row['Suspend']==0) {?>
					 <input type='submit' name='suspend' value='Suspend' class='btn btn-primary btn-small'></input><br>
					 <?php } else {?>
					 <input type='submit' name='unsuspend' value='Unsuspend' class='btn btn-primary btn-small'></input><br>
					 <?php } ?>
					 <input type='submit' name='edit_show' value='Edit' class='btn btn-primary btn-small'></input><br>
					 <input type='submit' name='del_show' value='Delete' class='btn btn-primary btn-small'></input>
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


