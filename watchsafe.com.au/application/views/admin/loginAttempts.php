<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#items").change(function() {	
		var item = $("#items option:selected").val();
	    	$("#items_form").submit(); 
	});
	$("#usertype").change(function() {	
		var item = $("#usertype option:selected").val();   
	    	$("#userType_form").submit(); 
	});
	$("#ip").change(function() {	
		var item = $("#ip option:selected").val();
	    	$("#ip_form").submit(); 
	});	
}
);
</script>
<h3 align=center>Login Attemps</h3>
<br>
<?php echo 'Results '.$pagination->current_first_item.'-'.$pagination->current_last_item.' of '.$pagination->total_items;?>
<?=$pagination?>
<br>
<form action='/admin/login_attempts' id='items_form' method='post'>
Items per page - 
<select name='usertype_items' id='items' class="input-small">
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
       <table class='table table-bordered'>
	   <tr>
	   	   <td>ID</td>
		   <td>User ID</td>
		   <td>User Type
		   	              <form action='/admin/login_attempts' method='post' id='userType_form'>
						  <select id='usertype' name='usertype_filter'>
						  <option value='all' <?=($_SESSION['usertype_filter']=='all') ? 'selected':''?>>All</option>
						  <?php foreach($UserTypeList as $value) {?>
						  <option value='<?=$value->user_login?>' <?=($_SESSION['usertype_filter']==$value->user_login) ? 'selected':''?>><?=$value->user_login?></option>
						  <?php }?>
						  </select>
						  </form>
		   </td>
		   <td>IP
                          <form action='/admin/login_attempts' method='post' id='ip_form'>
						  <select id='ip' name='ip_filter'>
						  <option value='all' <?=($_SESSION['ip_filter']=='all') ? 'selected':''?>>All</option>
						  <?php foreach($IPList as $value) {?>
						  <option value='<?=$value->ip?>' <?=($_SESSION['ip_filter']==$value->ip) ? 'selected':''?>><?=$value->ip?></option>
						  <?php }?>
						  </select>
						  </form>
		   </td>
		   <td>Status</td>
		   <td>Date</td>
	   </tr>
       
   <?php foreach($ViewResult as $row)
   { 
   ?>
	           
            <tr>	
                  <td><?=$row->id?></td>		      
				  <td><?=$row->user_id?></td>
				  <td><?=$row->user_login?></td>
				  <td><?=$row->ip?></td>
				  <td>
				  <?php
				  switch ($row->status) {
				   	case 0: echo "<span style='color:green;'>OK</span>"; break;
				   	case 1: echo "<span style='color:pink;'>OK/BLOCKED</span>"; break;
                    case 2: echo "<span style='color:red;'>INCORRECT PASSWORD</span>"; break;
				   	case 3: echo "<span style='color:blue;'>USER NOT EXIST</span>"; break;
				   } 
				  ?>
				  </td>
				  <td><?=$row->mod_date?></td>
			</tr>			
	
   <?php }?>  
	   </table>
	
    <?php
	}
	else{
	  echo "<p>No logs...</p>";
	  echo "<a href='/admin/login_attempts?clear_all_filters=true'>Show all</a>";
	}
    ?>	
