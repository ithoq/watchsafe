   <!-- Main hero unit for a primary marketing message or call to action -->
     <?php //if (isset($errors)) var_dump($errors)?>
      <div class="hero-unit" style="height: 400px;">
        <h3>Please specify email where you want to send reset password for user <?=$user->username?></h3>
            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/manager/users">
              <input class="span3" type="email" name="email" value="<?=$user->email?>"></input> 
              <button type="submit" name="reset_password" class="btn btn-primary">Send</button>
            </form>
      </div>
      <a href="/manager/users" class="btn-link" >back</a>