<div class="hero-unit" style="height: 400px;">
        <h2>Add device</h2>
            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/admin/devices">
              <input class="span3" type="text" placeholder="name" value="<?=(isset($_POST['name']) ? $_POST['name'] : '' )?>" name="name"><?=(isset($errors['name']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['name'].'</span>' : '')?><br />
              <input class="span3" type="text" placeholder="imei"value="<?=(isset($_POST['imei']) ? $_POST['imei'] : '' )?>" name="imei"><?=(isset($errors['imei']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['imei'].'</span>' : '')?><br />
              <button type="submit" name='add' class="btn btn-primary btn-small">Add</button>
            </form>     
      </div>
      <a href="/admin/devices" class="btn-link" >back</a>