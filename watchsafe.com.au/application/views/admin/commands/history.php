<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#items").change(function() {	
		var item = $("#items option:selected").val();
	    	$("#items_form").submit(); 
	});
}
);
</script>
<h3 align=center>Commands history</h3>

<?php echo 'Results '.$pagination->current_first_item.'-'.$pagination->current_last_item.' of '.$pagination->total_items;?>
<?=$pagination?>
<br>
<form action='/admin/commandsHisory' id='items_form' method='post'>
Items per page - 
<select name='commands_items' id='items' class="input-small">
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

    <?php if (count($ViewResult)>0)
	{
	?>
       <table class='table table-bordered' style="font-size:12px;">
	   <tr>
		   <td>Imei</td>
		   <td>Ip</td>
		   <td>Command</td>
		   <td>Response</td>
		   <td>Date</td>
	   </tr>
       
   <?php foreach($ViewResult as $row)
   { 
   ?>
	           
            <tr>			      
				  <td><?=$row->imei?></td>
				  <td><?=$row->ip?></td>
				  <td><?=hex2str(str_replace(' ','',$row->command))?></td>
				  <td><?=$row->response?></td>
				  <td><?=$row->date?></td>
			</tr>			
	
   <?php }?>  
	   </table>
	
    <?php
	}
	else{
	  echo "<p>No commands...</p>";
	  //echo "<a href='/admin/l?clear_all_filters=true'>Show all</a>";
	}
	function hex2str($hex) {
    $str='';
    for($i=0;$i<strlen($hex);$i+=2)
    $str .= chr(hexdec(substr($hex,$i,2)));

    return $str;
    }
    ?>
     <a href="/admin/commands" class="btn-link" >back</a>