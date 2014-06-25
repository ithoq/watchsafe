<div class="hero-unit" style="height: 400px;">
        <h2>Edit device</h2>
            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/admin/devices">
              <input type='hidden' value='<?=$DeviceResult->id?>' name='id' />
              <input class="span3" type="text" value="<?=$DeviceResult->name?>" name="name"><?=(isset($errors['name']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['name'].'</span>' : '')?><br />
              <input class="span3" type="text" value="<?=$DeviceResult->imei?>" name="imei"><?=(isset($errors['imei']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['imei'].'</span>' : '')?><br />
              <button type="submit" name='edit' class="btn btn-primary">Edit</button>
            </form>     
      </div>
      <a href="/admin/devices" class="btn-link" >back</a>
