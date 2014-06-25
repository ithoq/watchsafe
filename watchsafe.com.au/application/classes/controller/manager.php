<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manager extends Controller_Template {

    public $template = 'manager/index';
    protected $user;
    
	  public function before()
	  {
	      parent::before();

	      $session = Session::instance();
	  
	      if (!Auth::instance()->logged_in('fleet manager'))
	          {
	           Request::instance()->redirect('/main');
	        }
	        else
	        {  
	           $this->user = Auth::instance()->get_user();
	        }
	  }

	public function action_index()
	{
		$this->template->content = 'Hello, Manager!';
    $this->action_dashboard();
	}
  // Dashboard
  public function action_dashboard()
  {
    if (isset($_GET['csv']))
    {
      if (($_GET['start']!=0) and ($_GET['end']!=0)){
      $sql = "SELECT * FROM `scores` WHERE `imei`='".$_GET['imei']."' AND (`trip_start` BETWEEN '".$_GET['start']."' AND '".$_GET['end']."')";
      }
      else { 
      $sql = "SELECT * FROM `scores` WHERE `imei`='".$_GET['imei']."'";
      }
      
      $Trips = DB::query(Database::SELECT, $sql)->as_object()->execute();
      if (count($Trips)!=0)
      {
         $text = '';
         foreach ($Trips as $row)
         {
            $text.=$row->trip_start.','.$row->imei.','.$row->start_latitude.','.$row->start_longitude.','.$row->end_latitude.','.$row->end_longitude.','.$row->trip_end.','.$row->mileage.','.$row->odometer.','.$row->alerts.','.$row->score;
            $text.="\r\n";
         }
      $f = fopen("/tmp/csv.txt", "w");
      fwrite($f, $text); 
      fclose($f);

      $file = ("/tmp/csv.txt");
      header ("Content-Type: application/octet-stream");
      header ("Accept-Ranges: bytes");
      header ("Content-Length: ".filesize($file));
      header ("Content-Disposition: attachment; filename=".$file);  
      readfile($file);
      
      
      $this->template->content = Message::alert("Warning!","File was saved...","/manager/dashboard");

      }
      else
      {
         $this->template->content = Message::alert("Warning!","Not datas for this period","/manager/dashboard");
      }
    }
    else{

    if (empty($_POST) and empty($_GET))
    {
        $user_id = $this->user->id;
        $str = "curl -i 'http://www.watchsafe.com.au/ajax/getDashboard?user_id=".$user_id."'";
        $result = exec($str);
        $result = json_decode($result,true);

        $str = "curl -i 'http://www.watchsafe.com.au/ajax/getData?user_id=".$user_id."'";
        $result1 = exec($str);
        $result1 = json_decode($result1,true);

        $str = "curl -i 'http://www.watchsafe.com.au/ajax/getAlerts?user_id=".$user_id."'";
        $result2 = exec($str);
        $result2 = json_decode($result2,true);
       
        $weeklyScores = array();
        // Get weekly datas
        foreach ($result as $key => $value) {
           $sql = "SELECT * FROM `weeklyScores` WHERE `imei` = '".$key."' ORDER BY `week_start` DESC LIMIT 1,10";
           $Trips = DB::query(Database::SELECT, $sql)->execute();
              foreach ($Trips as $key1 => $value1) {
                 $weeklyScores[$value['imei']][date(strtotime($value1['week_start']))] = $value1['score'];
              }
        }
        //var_dump($weeklyScores);
        //exit();

        $this->template->content = View::factory('/manager/dashboard/index')->bind('user_id',$user_id)
                                                                            ->bind('result', $result)
                                                                            ->bind('result1', $result1)
                                                                            ->bind('result2', $result2)
                                                                            ->bind('weeklyScores', $weeklyScores);
    }
   else
   {
    
      if(isset($_GET['imei']))
      {
         // Detailed  trips information
          $TripsResult = array();
          $HeaderResult = array();
          $HeaderResult['name'] = $_GET['name'];
          $HeaderResult['imei'] = $_GET['imei'];
          $HeaderResult['points'] = $_GET['points'];
          $HeaderResult['trips'] = $_GET['trips'];
          $HeaderResult['mileage'] = $_GET['mileage'];
          $is_cut = false;
          // Get all device trips
          if ((isset($_GET['start'])) and (isset($_GET['end'])))
          {
            $HeaderResult['start'] = $_GET['start'];
            $HeaderResult['end'] = $_GET['end'];
            //$sql = "SELECT * FROM `2locations` WHERE `Modem_ID`='".$_GET['imei']."' AND (`GPS_DataTime` BETWEEN '".$_GET['start']."' AND '".$_GET['end']."')  ORDER BY `GPS_DataTime`";
            $sql = "SELECT * FROM `scores` WHERE `imei`='".$_GET['imei']."' AND (`trip_start` BETWEEN '".$_GET['start']."' AND '".$_GET['end']."')";
          }
          else{
              $is_cut = true;
              if (isset($_GET['show_all'])) $is_cut = false;
              //$sql = "SELECT * FROM `2locations` WHERE `Modem_ID`='".$_GET['imei']."' ORDER BY `GPS_DataTime`";
              $sql = "SELECT * FROM `scores` WHERE `imei`='".$_GET['imei']."'";
           }
           $Trips = DB::query(Database::SELECT, $sql)->execute();
           $counter = 0;
           foreach ($Trips as $row) 
           {
             $TripsResult[$counter]['id'] = $row['id'];
             $TripsResult[$counter]['imei'] = $row['imei'];
             $TripsResult[$counter]['trip_start'] = $row['trip_start'];
             $TripsResult[$counter]['trip_end'] = $row['trip_end'];
             $TripsResult[$counter]['trip_duration'] = $this->make_timestamp($row['trip_end']) - $this->make_timestamp($row['trip_start']);
             $TripsResult[$counter]['trip_mileage'] = $row['mileage'];
             $TripsResult[$counter]['OBD_Odometer'] = $row['odometer'];
             if (($TripsResult[$counter]['trip_mileage']<1500) or ($TripsResult[$counter]['trip_duration']<60) or ($row['alerts']<4)) {
              $TripsResult[$counter]['points'] = 'N/A';
              } else{
                if ($row['score']<0) {
                  $points = 0;
                }else{
                  $points = $row['score'];
                }
                $TripsResult[$counter]['points'] = $points;
              }
              $TripsResult[$counter]['alerts'] = $row['alerts'];
              $TripsResult[$counter]['Total_fuel_used'] = ($row['fuel_used'] == 0) ? 'N/A' : $row['fuel_used'];
              $counter++;

           }
          // Calculate Alerts
          if ((isset($_GET['start'])) and (isset($_GET['end'])))
          {
               $sql = "SELECT * FROM `2locations` WHERE `Modem_ID`='".$_GET['imei']."' AND `Message_ID` IN (208,199,206,323,321,180,179,162,322,178,170,160,164,165,210,193) AND (`GPS_DataTime` BETWEEN '".$_GET['start']."' AND '".$_GET['end']."') ORDER BY `GPS_DataTime` DESC";    
          }
          else
            $sql = "SELECT * FROM `2locations` WHERE `Modem_ID`='".$_GET['imei']."' AND `Message_ID` IN (208,199,206,323,321,180,179,162,322,178,170,160,164,165,210,193)  ORDER BY `GPS_DataTime` DESC limit 20";
          
          $Alerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
          $AlertResult = array();
          $ModalData = array();
          foreach ($Alerts as $row) {
             $AlertResult[$row->id]['ID'] = $row->id;
             $AlertResult[$row->id]['Date'] = $row->GPS_DataTime;
             $AlertResult[$row->id]['Latitude'] = $row->Latitude;
             $AlertResult[$row->id]['Longitude'] = $row->Longitude;

             switch($row->Message_ID)
             {
              case '208' : $AlertResult[$row->id]['Type'] = 'Sudden Break'; break;
              case '199' : $AlertResult[$row->id]['Type'] = 'Emergency Break'; break;
              case '206' : $AlertResult[$row->id]['Type'] = 'Sudden Acceleration'; break;
              case '323' : $AlertResult[$row->id]['Type'] = 'High Throttle'; $AlertResult[$row->id]["Data"] = "High Throttle - ".$row->Over_STEP.'%';break;
              case '321' : $AlertResult[$row->id]['Type'] = 'High RPM'; $AlertResult[$row->id]["Data"] = "Over RPM - ".$row->Over_RPM.' RPM';break;
              case '180' : $AlertResult[$row->id]['Type'] = 'Impact Detected'; $AlertResult[$row->id]["Data"] = "Impact Detected<br>X_Axis = ".$row->X_Axis." G's<br>Y_Axis = ".$row->Y_Axis." G's<br>Z_Axis = ".$row->Z_Axis." G's";break;
              case '179' : $AlertResult[$row->id]['Type'] = 'Motion Detected'; break;
              case '162' : $AlertResult[$row->id]['Type'] = 'Speeding Alert'; break;
              case '322' : $AlertResult[$row->id]['Type'] = 'High Temp'; $AlertResult[$row->id]["Data"] = "Over Temperature - ".$row->Over_TEMP.' C'; break;
              case '178' : $AlertResult[$row->id]['Type'] = 'GPS Failure'; break;
              case '170' : $AlertResult[$row->id]['Type'] = 'Device Removal'; break;
              case '160' : $AlertResult[$row->id]['Type'] = 'Device installed'; break;
              case '164' : $AlertResult[$row->id]['Type'] = 'Geo Fence Entry'; break;
              case '165' : $AlertResult[$row->id]['Type'] = 'Geo Fence Exit'; break;
              case '210' : $AlertResult[$row->id]['Type'] = 'Geo Fence Speed'; break;
              case '193' : $AlertResult[$row->id]['Type'] = 'Low Power Mode'; break;
             }
          }
          // Calculate geofence alerts
          if ((isset($_GET['start'])) and (isset($_GET['end'])))
          {
               $sql = "SELECT * FROM `geoalerts` WHERE `imei`='".$_GET['imei']."' AND (`date` BETWEEN '".$_GET['start']."' AND '".$_GET['end']."') ORDER BY `date` DESC";    
          }
          else
            $sql = "SELECT * FROM `geoalerts` WHERE `imei`='".$_GET['imei']."' ORDER BY `date` DESC limit 20";
	        $Alerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
	        foreach ($Alerts as $row) {
	             array_push($AlertResult, array('ID' => $row->id,'imei' => $row->imei, 'Type' => ucfirst($row->action), 'Data' => $row->details, 'Date' => $row->date));
	         }

          // Calculate engine faults
            if ((isset($_GET['start'])) and (isset($_GET['end'])))
          {
               $sql = "SELECT * FROM `errCommands` WHERE `responseText`!='' AND `numberErrors`>0 AND `imei`='".$_GET['imei']."' AND (`date` BETWEEN '".$_GET['start']."' AND '".$_GET['end']."') ORDER BY `date` DESC";    
          }
          else
            $sql = "SELECT * FROM `errCommands` WHERE `responseText`!='' AND `numberErrors`>0 AND `imei`='".$_GET['imei']."' ORDER BY `date` DESC limit 20";
          $Alerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
          foreach ($Alerts as $row) {
               array_push($AlertResult, array('ID' => $row->id,'imei' => $row->imei, 'Type' => 'Engine fault', 'Data' => $row->responseText, 'Date' => $row->date));
           }

	         //var_dump($AlertResult);
	         //exit();
          

          // Weekly scores result
          $WeeklyModalData = array();
         $sql = "SELECT * FROM `weeklyScores` WHERE `imei`='".$_GET['imei']."' ORDER BY `week_start` DESC limit 4";
         $weeklyScoresResult = DB::query(Database::SELECT, $sql)->execute();
         foreach ($weeklyScoresResult as $row) {
            $WeeklyModalData[$row['id']]['id'] = $row['id'];
            $WeeklyModalData[$row['id']]['score'] = $row['score'];
            $WeeklyModalData[$row['id']]['trips'] = $row['trips'];
            $WeeklyModalData[$row['id']]['mileage'] = $row['mileage'];
         }
          //var_dump($TripsResult);
          //exit();

          if ($is_cut){
             $TripsResult = array_slice(array_reverse($TripsResult),0,10);
          }
          else{
              $TripsResult = array_reverse($TripsResult);
           }
          // var_dump($TripsResult);
          //exit();
           $MapModalDatas = array();

           foreach ($TripsResult as $row) {
              $TripStart = $row['trip_start'];
              $TripEnd = $row['trip_end'];
              $sql = "SELECT * FROM `2locations` WHERE `Modem_ID`='".$row['imei']."' AND (`GPS_DataTime` BETWEEN '".$TripStart."' AND '".$TripEnd."')";

              $TripArr = array();
              $Trip = DB::query(Database::SELECT, $sql)->execute();
              $PreviousLat = 0;
              $PreviousLon = 0;
                 foreach ($Trip as $row1) {
                  if (in_array($row1['Message_ID'], array(208,199,206,323,321,180,179,162,322,178,170,160,164,165,210,193))){
                    $Trip_str = $row1['Latitude'].','.$row1['Longitude'];
                      switch($row1['Message_ID'])
                       {
                        case '208' : $Trip_str.=',Sudden Break'; break;
                        case '199' : $Trip_str.=',Emergency Break'; break;
                        case '206' : $Trip_str.=',Sudden Acceleration'; break;
                        case '323' : $Trip_str.=',High Throttle,'; $Trip_str.= $row1['Over_STEP'].'%';break;
                        case '321' : $Trip_str.=',High RPM,'; $Trip_str.= $row1['Over_RPM'].' RPM';break;
                        case '180' : $Trip_str.=',Impact Detected,'; $Trip_str.="<br>X_Axis = ".$row1['X_Axis']." G's<br>Y_Axis = ".$row1['Y_Axis']." G's<br>Z_Axis = ".$row1['Z_Axis']." G's";break;
                        case '179' : $Trip_str.=',Motion Detected'; break;
                        case '162' : $Trip_str.=',Speeding Alert'; break;
                        case '322' : $Trip_str.=',High Temp,';$Trip_str.=$row1['Over_TEMP'].' C';break;
                        case '178' : $Trip_str.=',GPS Failure'; break;
                        case '170' : $Trip_str.=',Device Removal'; break;
                        case '160' : $Trip_str.=',Device installed'; break;
                        case '164' : $Trip_str.=',Geo Fence Entry'; break;
                        case '165' : $Trip_str.=',Geo Fence Exit'; break;
                        case '210' : $Trip_str.=',Geo Fence Speed'; break;
                        case '193' : $Trip_str.=',Low Power Mode'; break;
                       }
                       $TripArr[] = $Trip_str;
                       unset($Trip_str);
                  }
                  else
                    {
                     if (($row1['Latitude']!=$PreviousLat) and ($row1['Longitude']!=$PreviousLon)){
                      $TripArr[] = $row1['Latitude'].','.$row1['Longitude'].',null,null,'.$row1['GPS_DataTime'].','.$row1['Speed'].'km/h,'.$row1['Throttle_position'].'%,'.$row1['Engine_RPM'];
                      $PreviousLat = $row1['Latitude'];
                      $PreviousLon = $row1['Longitude'];
                     }
                    }
                 }
              $MapModalDatas[$row['id']]['coordinates'] = $TripArr;
              $MapModalDatas[$row['id']]['detailed'] = $row['trip_start'].','.$row['trip_duration'].','.$row['points'].','.$row['alerts'].','.$row['trip_mileage'].','.$row['OBD_Odometer'].','.$row['Total_fuel_used'];
           }

           //var_dump($MapModalDatas);
           //exit();
  
                  $this->template->content = View::factory('/manager/dashboard/detailed')->bind('ViewResult',$TripsResult)
                                                                               ->bind('AlertResult',$AlertResult)
                                                                               ->bind('HeaderResult',$HeaderResult)
                                                                               ->bind('weeklyScoresResult',$weeklyScoresResult)
                                                                               ->bind('ModalData',$ModalData)
                                                                               ->bind('WeeklyModalData',$WeeklyModalData)
                                                                               ->bind('MapModalDatas', $MapModalDatas);
      }
      
   }
  }
  }
	// Users CRUD
    public function action_users()
    {
       if (empty($_POST) or isset($_POST['items']))
       {
                
                // Get list of user of current manager
       	        $query = "SELECT `managers_users`.`user_id` FROM `users` JOIN `managers_users` ON `users`.`id`=`managers_users`.`manager_id` WHERE `users`.`id`='".$this->user->id."'";
       	        $ManagersUsersResult = DB::query(Database::SELECT, $query)->as_object()->execute();
       	        if  (count($ManagersUsersResult)!=0)
       	        {
		       	        $ManagersUserArr = array();
		       	        foreach ($ManagersUsersResult as $row) {
		       	        	$ManagersUserArr[] = $row->user_id;
		       	        }

		                $query = "SELECT `users`.*  FROM `users` WHERE `users`.`id` IN(".implode(',',$ManagersUserArr).")";
		          
		                                
		                $UsersResult = DB::query(Database::SELECT, $query)->execute();
		                
		                     
		                 if (isset($_POST['items']))
		                 {
		                    $_SESSION['man_users_items_per_page'] = $_POST['items'];
		                 }
		                 if (!isset($_SESSION['man_users_items_per_page']))
		                 {
		                    $_SESSION['man_users_items_per_page'] = 10;
		                 }
		                 
		                   $pag_data = array
		                            (
		                              'total_items'     => count($UsersResult),
		                              'items_per_page'  => $_SESSION['man_users_items_per_page'],
		                              
		                            );    
		                $pagination =  Pagination::factory($pag_data);
		                
		                //echo kohana::debug($pagination);
		                
		                $query.=" LIMIT ".$pagination->items_per_page." OFFSET ".$pagination->offset;
		                
		                //echo $query;
		                //exit();
		                
		                $UsersResult = DB::query(Database::SELECT, $query)->as_object()->execute();
		                                  
		                 foreach ($UsersResult as $row)
		                 {
		                   $ViewResult[$row->id]['ID'] = $row->id;
		                   $ViewResult[$row->id]['Username'] = $row->username;
		                   $ViewResult[$row->id]['Logins'] = $row->logins;
		                   $ViewResult[$row->id]['LastLogin'] = date("m.d.y - H:m:s",$row->last_login);
		                   $ViewResult[$row->id]['Email'] = $row->email;
		                   // Get user devices
		                   $query = "SELECT * FROM `users_devices` WHERE `user_id`='".$row->id."'";
		                   $DevicesList = DB::query(Database::SELECT, $query)->as_object()->execute();
		                   $DevicesListArr = array();
		                   foreach ($DevicesList as $row1) {
		                                  $DevicesListArr[] = $row1->device_imei;
		                                 } 
		                   $ViewResult[$row->id]['Devices'] = implode(',',$DevicesListArr);                          
		                 }  
		                 // Get Manager Info
		                 $managerInfo = ORM::factory('user')->where('id','=',$this->user->id)->find();    
		                //echo kohana::debug($ViewResult);
		               // exit();
		              
		                 $this->template->content = View::factory('/manager/users/index')->bind('ViewResult',$ViewResult)
		                                                                                 ->bind('pagination',$pagination)
		                                                                                 ->bind('managerInfo',$managerInfo);
                } else {
                	 $this->template->content = View::factory('/manager/users/index')->set('nousers',true);	
                	   }                                                                             
       }
       else
         {
             if (isset($_POST['reset_password_show']))
             {
                $user = ORM::factory('user')->where('id','=',$_POST['id'])->find();
                $_SESSION['reset_password_user_id'] = $user;
                $this->template->content = View::factory('/manager/reset_password')->bind('user',$user);
             }
             // Reset user password
             if (isset($_POST['reset_password']))
             {
              if (isset($_POST['email']))
              {               
                    $user = ORM::factory('user')->where('id', '=', $_SESSION['reset_password_user_id']->id)->find();
                    $new_password = substr(uniqid(), 0, 7);
                    $user->values(array(                       
                          'password' => $new_password,               
                     ));
                    $user->save();
    
                          $email = Email::factory('Password reset', 'New password for user '.$_SESSION['reset_password_user_id']->username.' is '.$new_password)
                            ->to($_POST['email'])
                            ->from('admin@gps.com', 'Admin')
                            ->send();
                            $this->alert('Email with password was successfully sent', '/manager/users');
              }
             }
         }
    }
  public function action_conf()
  {
     if (empty($_POST))
     {
      $this->template->content = View::factory('/manager/conf/index');
     }
     else
     {
        if (isset($_POST['change_password_show']))
        {
          $this->template->content = View::factory('/manager/conf/change_password');
        }
        if (isset($_POST['change_password']))
        {
            $post = Validate::factory($_POST)->rule('password_confirm','not_empty')
                                             ->rule('password','not_empty')
                                             ->callback('password', array($this, 'password_validate'))
                                             ->rule('password', 'min_length', array('6'))
                                             ->rule('password_confirm',  'matches', array('password'));
             if ($post->check())
                {
                    $user = ORM::factory('user')->where('id', '=', $this->user->id)->find();
                    $new_password = $_POST['password'];
                    $user->values(array(                       
                          'password' => $new_password,               
                     ));
                    $user->save();
                    $this->alert('Yous password was successfully changed!!!', '/manager');
                }
                else
                {
                    $errors = $post->errors('validation');
                    $this->template->content = View::factory('/manager/conf/change_password')->bind('errors',$errors);
                }                                  
        }
     }
   }
  
  public function action_fences()
  {
    if (empty($_POST) or isset($_GET['imei']))
     {
       //$sql="SELECT `devices`.* FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$this->user->id."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
       $sql="SELECT `devices`.* FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$this->user->id."'"; 
       //echo $sql;
       //exit();
       $ManagersDevices = DB::query(Database::SELECT, $sql)->as_object()->execute();
       if (count($ManagersDevices)!=0)
       {
	       if (!isset($_GET['imei'])){
	         $CurrentDeviceImei = $ManagersDevices[0]->imei;
	          $sql = "SELECT * FROM `geofences` WHERE `imei`='".$ManagersDevices[0]->imei."'";
	          $FencesList = DB::query(Database::SELECT, $sql)->as_object()->execute();           
	       }
	       else{
		           $accept = false;
		           foreach ($ManagersDevices as $row) 
		           {
		               if (($row->imei==$_GET['imei']) or ($_GET['imei']=='all'))
		                 {
		                   $accept = true;
		                 }
		           }
		          if ($accept){
		          
		          $CurrentDeviceImei = $_GET['imei'];
		          $sql = "SELECT * FROM `geofences` WHERE `imei`='".$CurrentDeviceImei."'";
	              $FencesList = DB::query(Database::SELECT, $sql)->as_object()->execute(); 
		          
		          }else{
		            Request::instance()->redirect('/main'); 
		          }
	            }
	       //$_SESSION['vehicle_imei'] = $CurrentDevice->imei;
              $_SESSION['fence_imei'] = isset($_GET['imei']) ? $_GET['imei'] : $ManagersDevices[0]->imei;
	       $this->template->content = View::factory('/manager/conf/fences/index')->bind('ManagersDevices',$ManagersDevices)
	                                                                             ->bind('CurrentDeviceImei',$CurrentDeviceImei)
	                                                                             ->bind('FencesList',$FencesList);
        } else {
        	$this->template->content = View::factory('/manager/conf/fences/index')->set('nousers',true);
        }
     } else
     {
      if (isset($_POST['add_show'])) {
        $_SESSION['fence_imei'] = $_POST['imei'];
     		$this->template->content = View::factory('/manager/conf/fences/add')->bind('imei',$_POST['imei']);
     	}
      if (isset($_POST['add'])) {
          $_POST['name'] = str_replace('\'', '`', $_POST['name']);
          $sql = "INSERT INTO `geofences` (`imei`,`name`,`type`,`time_start`,`time_end`,`overspeed`,`latitude`,`longitude`,`radius`,`size`) VALUES 
          ('".$_SESSION['fence_imei']."','".$_POST['name']."','".$_POST['type']."','".$_POST['start']."','".$_POST['end']."','".$_POST['overspeed']."','".$_POST['lat']."','".$_POST['lon']."','".$_POST['radius']."','".$_POST['size']."')";
           DB::query(Database::INSERT, $sql)->execute();
           Request::instance()->redirect('/manager/fences?imei='.$_SESSION['fence_imei']); 
         
      }
     if (isset($_POST['edit_show'])) {
         $sql = "SELECT * FROM `geofences` WHERE `id`='".$_POST['id']."'";
         $CurrentFence = DB::query(Database::SELECT, $sql)->as_object()->execute();
         $CurrentFence = $CurrentFence[0];
         $_SESSION['fence_imei'] = $_POST['imei'];
         $_SESSION['fence_id'] = $_POST['id'];
         //var_dump($CurrentFence);
         //exit();
     	 $this->template->content = View::factory('/manager/conf/fences/edit')->bind('imei',$_POST['imei'])
                                                                              ->bind('fence',$CurrentFence);
        }
      if (isset($_POST['edit'])) {
        $_POST['name'] = str_replace('\'', '`', $_POST['name']);
      	$sql = "UPDATE `geofences` SET `imei`='".$_SESSION['fence_imei']."',`name`='".$_POST['name']."',`type`='".$_POST['type']."',`time_start`='".$_POST['start']."',`time_end`='".$_POST['end']."',`overspeed`='".$_POST['overspeed']."',`latitude`='".$_POST['lat']."',`longitude`='".$_POST['lon']."',`radius`='".$_POST['radius']."',`size`='".$_POST['size']."' WHERE `id`='".$_SESSION['fence_id']."'";
      	DB::query(Database::UPDATE, $sql)->execute();
        Request::instance()->redirect('/manager/fences?imei='.$_SESSION['fence_imei']); 
      }
      if (isset($_POST['del_show']))
        {
         $this->template->content = Message::confirm("Warning!","Are you realy want delete this fence?","/manager/fences","del",$_POST['id']);
        }
      
    
        if (isset($_POST['del']))
        {
          if ($_POST['response']=='true')
          {
           $device = ORM::factory('geofence')->where('id','=',$_POST['del'])->find();
           $device->delete();
           Request::instance()->redirect('/manager/fences?imei='.$_SESSION['fence_imei']); 
          }
        else
          Request::instance()->redirect('/manager/fences?imei='.$_SESSION['fence_imei']); 
        }   
     }
       if (isset($_POST['id']) and isset($_POST['imei']) and !isset($_POST['edit_show']) and !isset($_POST['edit'])){
           $device = ORM::factory('geofence')->where('id','=',$_POST['id'])->find();
           $device->delete();
           Request::instance()->redirect('/manager/fences?imei='.$_SESSION['fence_imei']);
           } 
  }

  public function action_vehicle()
  {
    if (empty($_POST) or isset($_GET['imei']))
     {
       //$sql="SELECT `devices`.* FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$this->user->id."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
       $sql="SELECT `devices`.* FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$this->user->id."'"; 
       //echo $sql;
       //exit();
       $ManagersDevices = DB::query(Database::SELECT, $sql)->as_object()->execute();
       if (count($ManagersDevices)!=0)
        {
		       if (!isset($_GET['imei'])){
		         $CurrentDevice = $ManagersDevices[0]; 
		       }
		       else{
		            $accept = false;
		           foreach ($ManagersDevices as $row) 
		           {
		               if ($row->imei==$_GET['imei'])
		                 {
		                   $accept = true;
		                 }
		           }
		          if ($accept){
		          $sql = "SELECT * FROM `devices` WHERE `imei`='".$_GET['imei']."'";
		          $Device = DB::query(Database::SELECT, $sql)->as_object()->execute();
		          $CurrentDevice = $Device[0];
		          }else{
		            Request::instance()->redirect('/main'); 
		          }
		       }
		       $_SESSION['vehicle_imei'] = $CurrentDevice->imei;
		       $this->template->content = View::factory('/manager/conf/vehicle/index')->bind('ManagersDevices',$ManagersDevices)
		                                                                              ->bind('CurrentDevice',$CurrentDevice);
        } else {
        	$this->template->content = View::factory('/manager/conf/vehicle/index')->set('nousers',true);
        }
     }
     else
     {
       $post = Validate::factory($_POST)->rule('odometer','is_numeric')
                                        ->rule('drivers_email','email')
                                        ->rule('service_email','email');
       if ($post->check()){
                        $device = ORM::factory('device')->where('imei','=',$_SESSION['vehicle_imei'])->find();
                        foreach ($_POST as $key => $value) {
                           if (empty($_POST[$key]))
                           {
                              $_POST[$key]=NULL;
                           }
                        }
                        //var_dump($_POST);
                        //exit();
                        $device->values($_POST);
                        $device->save();                               
                        Request::instance()->redirect('/manager/vehicle?imei='.$_SESSION['vehicle_imei']);                          
                }
                else
                {
                    $errors = $post->errors('validation');
                    //$sql="SELECT `devices`.* FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$this->user->id."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
                    $sql="SELECT `devices`.* FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$this->user->id."'"; 
                    $ManagersDevices = DB::query(Database::SELECT, $sql)->as_object()->execute();
                    $sql = "SELECT * FROM `devices` WHERE `imei`='".$_SESSION['vehicle_imei']."'";
                    $Device = DB::query(Database::SELECT, $sql)->as_object()->execute();
                    $CurrentDevice = $Device[0];
                    $this->template->content = View::factory('/manager/conf/vehicle/index')->bind('ManagersDevices',$ManagersDevices)
                                                                                           ->bind('CurrentDevice',$CurrentDevice)
                                                                                           ->bind('errors',$errors);
                }
     }
  }

  public function action_schedule()
  {
     if (empty($_POST) and isset($_GET['imei'])){
        $_SESSION['schedule_imei'] = $_GET['imei'];
        $device = ORM::factory('device')->where('imei','=',$_GET['imei'])->find();
        $sql = "SELECT `schedules`.* FROM `schedules` JOIN `devices` ON `schedules`.`device_imei`=`devices`.`imei` WHERE `devices`.`imei`='".$_GET['imei']."'";
        $ViewResult = DB::query(Database::SELECT, $sql)->as_object()->execute();
        //echo $sql;
       // exit();
        $this->template->content = View::factory('/manager/conf/schedule/index')->bind('device',$device)
                                                                                ->bind('ViewResult',$ViewResult);
     }else{
      if (isset($_POST['add_show']))
      {
        $this->template->content = View::factory('/manager/conf/schedule/add');
      }
      if (isset($_POST['add']))
      {
        $post = Validate::factory($_POST)->rule('name','not_empty')
                                         ->rule('mil_due','is_numeric');
       if ((empty($_POST['mil_due'])) and (empty($_POST['date_due']))) 
        {
            $errors1['main'] = 'At Least one fields of "Kilometres service due" or "Date service due" must not be empty!';
        }                                
       if (($post->check()) and !isset($errors1['main'])){
                        $schedule = ORM::factory('schedule');
                        foreach ($_POST as $key => $value) {
                           if (empty($_POST[$key]))
                           {
                              $_POST[$key]=NULL;
                           }
                        }
                        $_POST['device_imei'] =  $_SESSION['schedule_imei'];
                        $schedule->values($_POST);
                        $schedule->save();                               
                        Request::instance()->redirect('/manager/schedule?imei='.$_SESSION['vehicle_imei']);                          
                }
                else
                {
                    $errors = $post->errors('validation');
                    $this->template->content = View::factory('/manager/conf/schedule/add')->bind('errors',$errors)
                                                                                          ->bind('errors1',$errors1);                    
                }
      }
      if (isset($_POST['edit_show']))
      {
        $schedule = ORM::factory('schedule')->where('id','=',$_POST['id'])->find();
        $_SESSION['schedule_id'] = $_POST['id'];
        $this->template->content = View::factory('/manager/conf/schedule/edit')->bind('schedule',$schedule);
      }
      if (isset($_POST['edit']))
      {
        $post = Validate::factory($_POST)->rule('name','not_empty')
                                         ->rule('mil_due','is_numeric');
                                        
      if ((empty($_POST['mil_due'])) and (empty($_POST['date_due'])))  
        {
            $errors1['main'] = 'At Least one fields of "Kilometres service due" or "Date service due" must not be empty!';
        }                                
       if (($post->check()) and !isset($errors1['main'])){
                        $schedule = ORM::factory('schedule')->where('id','=',$_SESSION['schedule_id'])->find();
                        foreach ($_POST as $key => $value) {
                           if (empty($_POST[$key]))
                           {
                              $_POST[$key]=NULL;
                           }
                        }
                        $_POST['device_imei'] =  $_SESSION['schedule_imei'];
                        $schedule->values($_POST);
                        $schedule->save();                               
                        Request::instance()->redirect('/manager/schedule?imei='.$_SESSION['vehicle_imei']);                          
                }
                else
                {
                    $errors = $post->errors('validation');
                    $this->template->content = View::factory('/manager/conf/schedule/edit')->bind('errors',$errors)
                                                                                          ->bind('errors1',$errors1);                    
                }
      }

      if (isset($_POST['del_show']))
        {
         $this->template->content = Message::confirm("Warning!","Are you realy want delete this schedule?","/manager/schedule","del",$_POST['id']);
        }
      
    
        if (isset($_POST['id']) and !isset($_POST['edit_show']) and !isset($_POST['edit'])) 
        {
           $device = ORM::factory('schedule')->where('id','=',$_POST['id'])->find();
           $device->delete();
           Request::instance()->redirect('/manager/schedule?imei='.$_SESSION['vehicle_imei']); 
        }

     }
  }
	public function action_logout()
    {
	   Auth::instance()->logout();
	   Request::instance()->redirect('/');
	}

  // Static function for password Validation    
        public static function password_validate(Validate $array, $field)
        {
           $password = $array[$field];
           
           $UPPER_validate = false;
           $NUM_validate = false;

           for($i=0;$i<strlen($password);$i++)
           {
              if (is_numeric($password[$i]))
              {
                $NUM_validate = true;
              }
              if (ctype_upper($password[$i]))
              {
                $UPPER_validate = true;
              }
           }
           if ((!$NUM_validate) or (!$UPPER_validate))
           {
              $array->error('password', 'too simple');
           }
        }
	  // Static function for alert messages
	  public function alert($message,$redirect='/')
	  {
	    $this->template->content = message::alert("Warning",$message,$redirect);
	  }
    // Static functions
    protected static function make_timestamp($date)
    {
      $temp = explode(' ',$date);
      $temp1 = explode('-',$temp[0]);
      $temp2 = explode(':',$temp[1]);
      return mktime($temp2[0],$temp2[1],$temp2[2],$temp1[1],$temp1[2],$temp1[0]);
    }

} // End Manager
