     <!-- Main hero unit for a primary marketing message or call to action -->
     <?php //if (isset($errors)) var_dump($errors)?>
      <div class="hero-unit" style="height: 500px;">
        <h2>Registration</h2>
            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/admin/register">
              <select class="span3" name="role" id='user_type'>
                  <option value="fleet manager" <?=(isset($_POST['role']) and ($_POST['role']=='fleet_manager')) ? 'selected' : ''?>>Manager</option>
                  <option value="user" <?=(isset($_POST['role']) and ($_POST['role']=='user')) ? 'selected' : ''?>>User</option>
              </select><br />
              <input class="span3" type="text"  value="<?=(isset($_POST['username'])) ? $_POST['username'] : ''?>" placeholder="username" name="username"><?=(isset($errors['username']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['username'].'</span>' : '')?><br />
              <input class="span3" type="email" value="<?=(isset($_POST['email'])) ? $_POST['email'] : ''?>" placeholder="email" name="email"><?=(isset($errors['email']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['email'].'</span>' : '')?><br />
              <div id='manager_data'>
                  
                  <input class="span3" type="text"  value="<?=(isset($_POST['unit_number'])) ? $_POST['unit_number'] : ''?>" placeholder="Unit Number/Street number" name="unit_number"><?=(isset($errors['unit_number']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['unit_number'].'</span>' : '')?><br />
                  <input class="span3" type="text"  value="<?=(isset($_POST['street'])) ? $_POST['street'] : ''?>" placeholder="Street Name" name="street"><?=(isset($errors['street']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['street'].'</span>' : '')?><br />
                  <input class="span3" type="text"  value="<?=(isset($_POST['suburd'])) ? $_POST['suburd'] : ''?>" placeholder="Suburb" name="suburd"><?=(isset($errors['suburd']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['suburd'].'</span>' : '')?><br />
                  <input class="span3" type="text"  value="<?=(isset($_POST['post_code'])) ? $_POST['post_code'] : ''?>" placeholder="Post Code" name="post_code"><?=(isset($errors['post_code']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['post_code'].'</span>' : '')?><br />
                   <select class="span3" name='state'>
                    <option value='' >---Select State---</option>
                     <option value="QLD" <?=(isset($_POST['state']) and ($_POST['state']=='QLD')) ? 'selected' : ''?>>QLD</option>
                     <option value="NSW" <?=(isset($_POST['state']) and ($_POST['state']=='NSW')) ? 'selected' : ''?>>NSW</option>
                     <option value="VIC" <?=(isset($_POST['state']) and ($_POST['state']=='VIC')) ? 'selected' : ''?>>VIC</option>
                     <option value="ACT" <?=(isset($_POST['state']) and ($_POST['state']=='ACT')) ? 'selected' : ''?>>ACT</option>
                     <option value="TAS" <?=(isset($_POST['state']) and ($_POST['state']=='TAS')) ? 'selected' : ''?>>TAS</option>
                     <option value="SA" <?=(isset($_POST['state']) and ($_POST['state']=='SA')) ? 'selected' : ''?>>SA</option>
                     <option value="WA" <?=(isset($_POST['state']) and ($_POST['state']=='WA')) ? 'selected' : ''?>>WA</option>
                     <option value="NT" <?=(isset($_POST['state']) and ($_POST['state']=='NT')) ? 'selected' : ''?>>NT</option>
                   </select>
                   <?=(isset($errors['state']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['state'].'</span>' : '')?>
                   <br>
                  <input class="span3" type="text" value="<?=(isset($_POST['DOB'])) ? $_POST['DOB'] : ''?>" placeholder="date of birthday (DD/MM/YYYY) " name="DOB"><?=(isset($errors['DOB']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['DOB'].'</span>' : '')?><br />
                  <input class="span3" type="text"  value="<?=(isset($_POST['fullname'])) ? $_POST['fullname'] : ''?>" placeholder="full name" name="fullname"><?=(isset($errors['fullname']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['fullname'].'</span>' : '')?><br />
                  <input class="span3" type="text" value="<?=(isset($_POST['phone'])) ? $_POST['phone'] : ''?>" placeholder="mobile phone" name="phone"><?=(isset($errors['phone']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['phone'].'</span>' : '')?><br />
              </div> 
              <div id='manager_list' style='display:none;'>
              <select class="span3" id='manager_select_box' name='manager'>
              </select>
              <?=(isset($errors['manager']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['manager'].'</span>' : '')?><br>
              </div>
              <div id='device_list' style='display:none;'>
              <select class="span3" id='device_select_box' name='device'>
              </select>
              <?=(isset($errors['device']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['device'].'</span>' : '')?><br>
              </div>      
              <input class="span3" type="password" placeholder="password" name="password"><?=(isset($errors['password']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['password'].'</span>' : '')?><br />
              <input class="span3" type="password" placeholder="password confirm" name="password_confirm"><?=(isset($errors['password_confirm']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['password_confirm'].'</span>' : '')?><br />
              <button type="submit" class="btn btn-primary">Sign in</button>
            </form>
      </div>