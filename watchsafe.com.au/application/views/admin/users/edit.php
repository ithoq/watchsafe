 <?php 
 //var_dump($user);
 //if (isset($errors)) var_dump($errors)?>
      <div class="hero-unit" style="height: 500px;">
        <h2>Edit <?=$user[0]['username']?></h2>
            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/admin/users">
            	<table>
            	<?php foreach ($user[0] as $key => $value) { 
            		if (!empty($value) and ($key!='password') and ($key!='logins') and ($key!='last_login') and ($key!='date_mode_status') and ($key!='id'))
            		{
            			?>
<tr>
	<td>
     <?=ucfirst($key)?>
    </td>
    <td>
	 <input class="span3" type="text"  value="<?=(isset($_POST['<?=$key?>'])) ? $_POST['<?=$key?>'] : $value?>"  name="<?=$key?>"> <?=(isset($errors[$key]) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors[$key].'</span>' : '')?>
    </td>
</tr>          			
            			<?php 
         		    }
            	 } 
                 if ($user_role=='user')
                    {
                        ?>
                        <tr>
                             <td>
                                Device
                             </td>
                             <td>                          
                                  <select class="span3" id='device_select_box' name='device'>
                                  </select>
                                  <?=(isset($errors['device']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['device'].'</span>' : '')?><br>
                             </td>
                        </tr>
                        <?php

                    }  
                 ?>
            	</table>	
            <button type="submit" name='edit' class="btn btn btn-primary">Edit</button>
            </form>     
      </div>
      <a href="/admin/users" class="btn-link" >back</a>
<?php  if ($user_role=='user') { 
    //var_dump($user_device);
    //exit();
    ?>
   <script src="/bootstrap/js/jquery.js"></script>
   <script type="text/javascript">
    $(document).ready(function() {
         var device_list = $("#device_select_box");
             device_list.load('/ajax/getDevices',{"current_device_name":"<?=$user_device['name']?>","current_device_imei":"<?=$user_device['imei']?>"});
    }
    );
    </script>
<?php } ?>    
