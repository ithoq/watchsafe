   <!-- Main hero unit for a primary marketing message or call to action -->
     <?php //if (isset($errors)) var_dump($errors)?>
      <div class="hero-unit" style="height: 400px;">
        <h3>Change password</h3>
            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/manager/conf">
              <input class="span3" type="password" placeholder="new password" name="password"><?=(isset($errors['password']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['password'].'</span>' : '')?><br />
              <input class="span3" type="password" placeholder="new password confirm" name="password_confirm"><?=(isset($errors['password_confirm']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['password_confirm'].'</span>' : '')?><br />          
              <button type="submit" name="change_password" class="btn btn-primary">Change</button>
            </form>
      </div>
      <a href="/manager/conf" class="btn-link" >back</a>