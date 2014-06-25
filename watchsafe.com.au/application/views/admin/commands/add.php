<div class="hero-unit" style="height: 400px;">
        <h2>Add command</h2>
            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/admin/commands">
              <select class="span3" name="imei">
                  <?php if (count($DevicesList)>0){ 
                  foreach ($DevicesList as $row) {?>
                  <option value='<?=$row->imei?>'><?=$row->imei?></option>
                  <?php }
                  } else {
                      $unshow = true;
                     ?>
                     <option>No free devices!</option> 
                     <?php  
                  }
                  ?>
              </select>
              <input class="span3" type="text" placeholder="Command" value="<?=(isset($_POST['command']) ? $_POST['command'] : '' )?>" name="command"><?=(isset($errors['command']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['command'].'</span>' : '')?>
             <button type="submit" name='add' class="btn btn-primary" <?=isset($unshow) ? "disabled=true" : ""?>>Send</button>
            </form>     
      </div>
      <a href="/admin/commands" class="btn-link" >back</a>