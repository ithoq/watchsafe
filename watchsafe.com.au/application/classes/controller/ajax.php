<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Ajax extends Controller {
public function action_test()
{
  echo $this->convert_duration(3456);

}
public function action_sendEmail()
{
  if ($_GET['type']=='test'){
   $email = Email::factory('Hello, World', 'This is my body, it is nice.')
        ->to('dinaris@list.ru')
        ->from('admin@gps.com', 'Admin')
        ->send();

         var_dump($email);
                            exit();
  }
  
  if (($_GET['type']=='engineError') && (isset($_GET['imei'])))
   {
     // Get last error response for device with current IMEI
    $sql = "SELECT * FROM `errCommands` WHERE `imei` = '".$_GET['imei']."' ORDER BY `date` DESC LIMIT 1";
    $err = DB::query(Database::SELECT, $sql)->as_object()->execute();
    if ((isset($err[0]->response)) && (!empty($err[0]->response))) {
       $res = explode('=',$err[0]->response);
       $res = explode('=',$res[1]);
       $res = explode(',',$res[0]);

       $finalResult = array();
       foreach ($res as $key => $value) {
         if ($key!=0){
           $sql = "SELECT * FROM `errCodes` WHERE `code` = 'p".$value."'";
           $code = DB::query(Database::SELECT, $sql)->as_object()->execute();
           $finalResult []  = $code[0]->text;
         } else {
           $number_of_errors = $value;
         }
       }

       $sql = "UPDATE `errCommands` SET `responseText` = '".implode(',',$finalResult)."', `numberErrors` = '".$number_of_errors."' WHERE `id` = '".$err[0]->id."'";
       DB::query(Database::UPDATE, $sql)->as_object()->execute();
       // Send email to manager and device user
       $device = ORM::factory('device')->where('imei','=',$_GET['imei'])->find();

        $text = "Hi";
        $text.="\n".$device->name." has just got an engine errors.";
        $text.="\nEngine got ".$number_of_errors." error(s)";
        $text.="\nList of error(s):";
        foreach ($finalResult as $key => $value) {
          $text.="\n".$value;
        }
        
        
        $email = Email::factory('Engine errors', $text)
                            ->to($device->service_email)
                            ->from('admin@gps.com', 'Admin')
                            ->send();
                          

        $email = Email::factory('Engine errors', $text)
                            ->to($device->drivers_email)
                            ->from('admin@gps.com', 'Admin')
                            ->send();  
                                              


    }
   }
  
  if ($_GET['type']=='trip_data')
   {
      $device = ORM::factory('device')->where('imei','=',$_GET['imei'])->find();
      $sql = "SELECT * FROM `users` JOIN `users_devices` ON `users`.`id`=`users_devices`.`user_id` WHERE `users_devices`.`device_imei`='".$_GET['imei']."'";
      $User_id = DB::query(Database::SELECT, $sql)->as_object()->execute();
      $User_id = $User_id[0]->id;
      $sql = "SELECT * FROM `users` JOIN `managers_users` ON `managers_users`.`manager_id`=`users`.`id` WHERE `managers_users`.`user_id`='".$User_id."'";
      $Manager = DB::query(Database::SELECT, $sql)->as_object()->execute();

      if ($device->notification_type==3)
      {
        
        $text = "Hi. ".$Manager[0]->username;
        $text.="\n".$device->name." has just completed a Journey and below is a summary of their trip.";
        $text.="\nDriver Score: ".$_GET['score']." points";
        $text.="\nStart Time: ".$this->convert_server_date($_GET['trip_start']);
        $text.="\nDuration: ".$this->convert_duration($_GET['duration'])." Distance: ".($_GET['mileage']/1000)." KM";
        $text.="\nTrip alerts: ".$_GET['alerts'];
        $text.="\nFuel used: ".$_GET['fuel_used']." L";
        $text.="\nEngine faults: ".$_GET['engine_faults'];
        $text.="\n WatchSafe.com.au";

        $email = Email::factory('Trip report to Manager', $text)
                            ->to($device->service_email)
                            ->from('admin@gps.com', 'Admin')
                            ->send();                    

      }

        $text = "Hi. ".$device->name;
        $text.="\nYou have just completed a Journey and below is a summary of that trip.";
        $text.="\nDriver Score: ".$_GET['score']." points";
        $text.="\nStart Time: ".$this->convert_server_date($_GET['trip_start']);
        $text.="\nDuration: ".$this->convert_duration($_GET['duration'])." Distance: ".($_GET['mileage']/1000)." KM";
        $text.="\nTrip alerts: ".$_GET['alerts'];
        $text.="\nFuel used: ".$_GET['fuel_used']." L";
        $text.="\nEngine faults: ".$_GET['engine_faults'];
        $text.="\n WatchSafe.com.au";

      $email = Email::factory('Trip report to User', $text)
                            ->to($device->drivers_email)
                            ->from('admin@gps.com', 'Admin')
                            ->send();
   }
   if ($_GET['type']=='alerts')
   {
      $device = ORM::factory('device')->where('imei','=',$_GET['imei'])->find();

      if ($device->notification_type==2)
      {

       $text = "Hi. Manager,\n".$device->drivers_name." has registered an alert in the ".$device->name."\n".$_GET['alert'].":\n".$_GET['data'];
       $text.="\n WatchSafe.com.au";
       $email = Email::factory('Alert to Manager', $text)
                            ->to($device->service_email)
                            ->from('admin@gps.com', 'Admin')
                            ->send();
      }
       $text = "Hi. ".$device->drivers_name."\n".$device->name." has registered an alert\n".$_GET['alert'].":\n".$_GET['data'];
       $text.="\n WatchSafe.com.au";
       $email = Email::factory('Alert to User', $text)
                            ->to($device->drivers_email)
                            ->from('admin@gps.com', 'Admin')
                            ->send();

                            
   }
   if ($_GET['type']=='schedule')
   {
      $text = "Hi this is schedule alert from device with imei ".$_GET['imei']."\n";
      $text.= "schedule name - ".$_GET['name']."\n";
          if (isset($_GET['mileage_duration']))
          {
            $text.="mileage due less then 500 KM";
          }
          else
          {
            $text.="date due less then 1 week";
          }

      $device = ORM::factory('device')->where('imei','=',$_GET['imei'])->find();
      if ($device->schedule_type==2)
      {
      $email = Email::factory('Schedule Alert to User', $text)
                            ->to($device->drivers_email)
                            ->from('admin@gps.com', 'Admin')
                            ->send(); 
      }                                                                  
   }
}
// Geo fence
public function action_sendCoordinates()
{
   if (!empty($_GET)){
      $geofences = ORM::factory('geofence')->where('imei','=',$_GET['imei'])->or_where('imei','=','all')->find_all();
      foreach ($geofences as $row) 
      {
        // Get manager name and username for current fence
          $sql = "SELECT * FROM `users` JOIN `users_devices` ON `users`.`id`=`users_devices`.`user_id` WHERE `users_devices`.`device_imei`='".$_GET['imei']."'";

        $User_id = DB::query(Database::SELECT, $sql)->as_object()->execute();
        $username = $User_id[0]->username;
        $User_id = $User_id[0]->id;
        $sql = "SELECT * FROM `users` JOIN `managers_users` ON `managers_users`.`manager_id`=`users`.`id` WHERE `managers_users`.`user_id`='".$User_id."'";
        $Manager = DB::query(Database::SELECT, $sql)->as_object()->execute();
        $managername = $Manager[0]->username;
        // Get manager and user emails
        $sql = "SELECT * FROM `devices` WHERE `imei`='".$_GET['imei']."'";
        $Data = DB::query(Database::SELECT, $sql)->as_object()->execute();
        $manager_email = $Data[0]->service_email;
        $user_email = $Data[0]->drivers_email;
        
        

        $distance = $this->distance_slc($_GET['lat'], $_GET['lon'], $row->latitude, $row->longitude);

        // Overspeed action
       if ($row->type=='overspeed'){
          if (($distance<=$row->radius) and ($row->overspeed<$_GET['speed'])) {
               if  ((time() - $row->last_action)>60) {             
              // Send overspeed message
              if ($manager_email!=NULL)
              {
              $text = "Hi ".$managername."\n".ucfirst($username)." has just BROKEN SET SPEED in ZONE ".$row->name." at ".date('d  M Y h:i:s A',time()).".\nSpeed - ".$_GET['speed']."KM/h\nThanks\nWatchSave.com.au";
              $email = Email::factory('Geofence overspeed alert', $text)
                            ->to($manager_email)
                            ->from('admin@gps.com', 'Admin')
                            ->send();
              }
              if ($user_email!=NULL)
              {
              $text = "Hi ".ucfirst($username)." has just BROKEN SET SPEED in ZONE ".$row->name." at ".date('d  M Y h:i:s A',time()).".\nSpeed - ".$_GET['speed']."KM/h\nThanks\nWatchSave.com.au";              
              $email = Email::factory('Geofence overspeed alert', $text)
                            ->to($user_email)
                            ->from('admin@gps.com', 'Admin')
                            ->send();
              }              
                                          
             $geofence = ORM::factory('geofence')->where('id','=',$row->id)->find();
             $geofence->last_action = time();
             $geofence->status = 1;
             $geofence->save();

             $geoalert  = ORM::factory('geoalert');
             $geoalert->imei = $_GET['imei'];
             $geoalert->action = "Geo fence speed";
             $geoalert->details = "Broke ".$row->overspeed." KM/h limit in Geofence ".$row->name.".<br>Current speed is - ".$_GET['speed']." KM/h";
             $geoalert->save();   
             }
                          
          }
       }
     if ($row->type=='entry&exit')  {

        if (($row->time_start=='0000-00-00 00:00:00') or ($row->time_end=='0000-00-00 00:00:00'))
        { 

               if ($row->status == 0){
                    if ($distance<=$row->radius) {
                      if ($manager_email!=NULL)
                      {
                      $text = "Hi ".$managername."\n".ucfirst($username)." has just ENTERED in ZONE ".$row->name." at ".date('d  M Y h:i:s A',time()).".\nThanks\nWatchSave.com.au";
                      $email = Email::factory('Geofence alert', $text)
                                    ->to($manager_email)
                                    ->from('admin@gps.com', 'Admin')
                                    ->send();
                      }
                      if ($user_email!=NULL)
                      {
                      $text = "Hi ".ucfirst($username)." has just ENTERED in ZONE ".$row->name." at ".date('d  M Y h:i:s A',time()).".\nSpeed - ".$_GET['speed']."KM/h\nThanks\nWatchSave.com.au";              
                      $email = Email::factory('Geofence alert', $text)
                                    ->to($user_email)
                                    ->from('admin@gps.com', 'Admin')
                                    ->send();
                      }     
                     $geofence = ORM::factory('geofence')->where('id','=',$row->id)->find();
                     $geofence->last_action = time();
                     $geofence->status = 1;
                     $geofence->save();

                     $geoalert  = ORM::factory('geoalert');
                     $geoalert->imei = $_GET['imei'];
                     $geoalert->action = "Geo fence alert";
                     $geoalert->details = ucfirst($username)." has just ENTERED in ZONE ".$row->name;
                     $geoalert->save();  
                    }
               } else {
                  if ($distance>=$row->radius) {
                     if ($manager_email!=NULL)
                      {
                      $text = "Hi ".$managername."\n".ucfirst($username)." has just EXITED from ZONE ".$row->name." at ".date('d  M Y h:i:s A',time()).".\nThanks\nWatchSave.com.au";
                      $email = Email::factory('Geofence alert', $text)
                                    ->to($manager_email)
                                    ->from('admin@gps.com', 'Admin')
                                    ->send();
                      }
                      if ($user_email!=NULL)
                      {
                      $text = "Hi ".ucfirst($username)." has just EXITED from ZONE ".$row->name." at ".date('d  M Y h:i:s A',time()).".\nSpeed - ".$_GET['speed']."KM/h\nThanks\nWatchSave.com.au";              
                      $email = Email::factory('Geofence alert', $text)
                                    ->to($user_email)
                                    ->from('admin@gps.com', 'Admin')
                                    ->send();
                      }    
                     $geofence = ORM::factory('geofence')->where('id','=',$row->id)->find();
                     $geofence->last_action = time();
                     $geofence->status = 0;
                     $geofence->save();

                     $geoalert  = ORM::factory('geoalert');
                     $geoalert->imei = $_GET['imei'];
                     $geoalert->action = "Geo fence alert";
                     $geoalert->details = ucfirst($username)." has just EXITED from ZONE ".$row->name;
                     $geoalert->save();  
                  }

               }
        
        } else {
               if ($row->status == 0){
                 if (($distance<=$row->radius) and ($this->make_timestamp($row->time_start)<=time()) and ($this->make_timestamp($row->time_end)>=time())) {
           
                      if ($manager_email!=NULL)
                      {
                      $text = "Hi ".$managername."\n".ucfirst($username)." has just ENTERED in ZONE ".$row->name." at ".date('d  M Y h:i:s A',time()).".\nThanks\nWatchSave.com.au";
                      $email = Email::factory('Geofence alert', $text)
                                    ->to($manager_email)
                                    ->from('admin@gps.com', 'Admin')
                                    ->send();
                      }
                      if ($user_email!=NULL)
                      {
                      $text = "Hi ".ucfirst($username)." has just ENTERED in ZONE ".$row->name." at ".date('d  M Y h:i:s A',time()).".\nSpeed - ".$_GET['speed']."KM/h\nThanks\nWatchSave.com.au";              
                      $email = Email::factory('Geofence alert', $text)
                                    ->to($user_email)
                                    ->from('admin@gps.com', 'Admin')
                                    ->send();
                      }

                     $geofence = ORM::factory('geofence')->where('id','=',$row->id)->find();
                     $geofence->last_action = time();
                     $geofence->status = 1;
                     $geofence->save();

                     $geoalert  = ORM::factory('geoalert');
                     $geoalert->imei = $_GET['imei'];
                     $geoalert->action = "Geo fence alert";
                     $geoalert->details = ucfirst($username)." has just ENTERED in ZONE ".$row->name;
                     $geoalert->save();  
                    }     
               } else {
                if (($distance>=$row->radius) and ($this->make_timestamp($row->time_start)<=time()) and ($this->make_timestamp($row->time_end)>=time())) {
                     if ($manager_email!=NULL)
                      {
                      $text = "Hi ".$managername."\n".ucfirst($username)." has just EXITED from ZONE ".$row->name." at ".date('d  M Y h:i:s A',time()).".\nThanks\nWatchSave.com.au";
                      $email = Email::factory('Geofence alert', $text)
                                    ->to($manager_email)
                                    ->from('admin@gps.com', 'Admin')
                                    ->send();
                      }
                      if ($user_email!=NULL)
                      {
                      $text = "Hi ".ucfirst($username)." has just EXITED from ZONE ".$row->name." at ".date('d  M Y h:i:s A',time()).".\nSpeed - ".$_GET['speed']."KM/h\nThanks\nWatchSave.com.au";              
                      $email = Email::factory('Geofence alert', $text)
                                    ->to($user_email)
                                    ->from('admin@gps.com', 'Admin')
                                    ->send();
                      }    
                     $geofence = ORM::factory('geofence')->where('id','=',$row->id)->find();
                     $geofence->last_action = time();
                     $geofence->status = 0;
                     $geofence->save();

                     $geoalert  = ORM::factory('geoalert');
                     $geoalert->imei = $_GET['imei'];
                     $geoalert->action = "Geo fence alert";
                     $geoalert->details = ucfirst($username)." has just EXITED from ZONE ".$row->name;
                     $geoalert->save();  
                  }

               }

        }
       
      }
    }
}
}

function distance_slc($lat1, $lon1, $lat2, $lon2) 
  {
      $distance  = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2-$lon1)) ;
      $distance  = acos($distance);
      $distance  = rad2deg($distance);
      //$distance  = $distance * 60 * 1.1515;
      $distance=$distance*111.2;
      $distance  = round($distance, 6);
          if (!is_nan($distance))
        {
        return round($distance * 1000,2);
        }
        else
         return 0;
       //Return distance in meters
        
  }
// end geofence
 
public function action_getManagers()
  {
    $Managers = DB::query(Database::SELECT, "SELECT * FROM `users` JOIN roles_users ON `users`.`id` = `roles_users`.`user_id` WHERE `roles_users`.`role_id`= 3 ")->as_object()->execute();
      ?>
      <option value=''>---Select manager---</option>
      <?php
    foreach ($Managers as $row){
      ?>
      <option value='<?=$row->id?>'><?=$row->username?></option>
      <?php
    }
  }

public function action_getDevices()
   {
     $Devices_worked = DB::query(Database::SELECT, "SELECT * FROM `users_devices`")->as_object()->execute();
     $Devices_worked_arr = array();
     foreach ($Devices_worked as $row) 
     {
      $Devices_worked_arr[] = $row->device_imei;
     }
     if (count($Devices_worked_arr)==0)
     {
      $Devices = DB::query(Database::SELECT, "SELECT * FROM `devices`")->as_object()->execute();
     }
     else
        $Devices = DB::query(Database::SELECT, "SELECT * FROM `devices` WHERE `devices`.`imei` NOT IN (".implode(',',$Devices_worked_arr).")")->as_object()->execute();
      
      if (isset($_POST['current_device_name'])){
          ?>
           <option value='<?=$_POST['current_device_imei']?>'><?=$_POST['current_device_name'].' - '.$_POST['current_device_imei']?></option>
          <?php
      }
      else
      {
         ?>
           <option value=''>---Select device---</option>
         <?php  
      }
    foreach ($Devices as $row){
      ?>
      <option value='<?=$row->imei?>'><?=$row->name.' - '.$row->imei?></option>
      <?php
    }
   }

   public function action_getCommandsStatus()
   {
    
   }

   public function action_getDashboardAdmin()
   {
     // Calculate data here
    $data = array();
    $sql = "SELECT DISTINCT `id` FROM `users` JOIN `managers_users` ON `managers_users`.`manager_id`=`users`.`id";
    $Managers = DB::query(Database::SELECT,$sql)->execute()->as_array();
    $sql = "SELECT `id` FROM users WHERE `suspend`!=0";
    $SuspendedUsers = DB::query(Database::SELECT,$sql)->execute()->as_array();
    $sql = "SELECT count(id) as count FROM `2locations`";
    $Locations = DB::query(Database::SELECT,$sql)->execute()->as_array();
    // Calculate number of data for today
    $date_start = date('Y-m-d',time());
    $date_end = date('Y-m-d',time()-86400);
    $sql = "SELECT count(id) as count FROM `2locations` WHERE `server_date` <= '".$date_start."' AND `server_date` >='".$date_end."'";
    $Locations1 = DB::query(Database::SELECT,$sql)->execute()->as_array();

    $data['Number_of_locations_today'] = $Locations1[0]['count'];
    $data['Number_of_locations'] = $Locations[0]['count'];
    $data['Number_of_managers'] = count($Managers);
    $data['Number_of_blocked_users'] = count($SuspendedUsers);
    $data['Number_of_devices'] = count(ORM::factory('device')->find_all());
    $data['daemon_status']= (@fsockopen('localhost',1338)) ? '<b style="color:green;">Running</b>' : '<b style="color:red;">Stopped</b>';
    $sql = "SELECT DISTINCT `id` FROM `users`";
    $Users = DB::query(Database::SELECT,$sql)->execute()->as_array();
    $data['Number_of_all_users'] = count($Users);
    $ViewResult = $data;
   ?>
       <table class='table table-bordered'>
     <tr>
       <td>Number of GPS records received by server</td>
       <td><?=$ViewResult['Number_of_locations']?></td>
     </tr>
     <tr>
       <td>Number of GPS records received by server today</td>
       <td><?=$ViewResult['Number_of_locations_today']?></td>
     </tr>
     <tr>
       <td>Number of blocked users</td>
       <td><?=$ViewResult['Number_of_blocked_users']?></td>
     </tr>
     <tr>
       <td>Number of registered devices</td>
       <td><?=$ViewResult['Number_of_devices']?></td>
     </tr>
     <tr>
       <td>Number of registered Manager accounts</td>
       <td><?=$ViewResult['Number_of_managers']?></td>
     </tr>
     <tr>
       <td>Number of all Registered users</td>
       <td><?=$ViewResult['Number_of_all_users']?></td>
     </tr>
     <tr>
       <td>Daemon status</td>
       <td><?=$ViewResult['daemon_status']?></td>
     </tr>
     
       </table>
   <?php    
   }

   public function action_getPointsForDashboard()
   {
    //Get list of manager devices
        if (!isset($_GET['user'])){
          $sql="SELECT `devices`.`imei`,`devices`.`name` FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$_GET['manager_id']."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";  
        } else {
          $sql="SELECT `devices`.`imei`,`devices`.`name` FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` WHERE `users_devices`.`user_id`='".$_GET['user_id']."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
        }
       $ManagersDevices = DB::query(Database::SELECT, $sql)->as_object()->execute();
       $ViewResult = array();
        foreach ($ManagersDevices as $row) {
          // Verify device status (if device don't send any action during 30 minutes)
          $sql = "SELECT `GPS_DataTime` , `Latitude`, `Longitude` FROM `2locations` WHERE `Modem_ID`='".$row->imei."' ORDER BY `id` DESC LIMIT 1";
          $Result = DB::query(Database::SELECT, $sql)->as_object()->execute();
          $last_time = $this->make_timestamp($Result[0]->GPS_DataTime);
          $Latitude = $Result[0]->Latitude;
          $Longitude = $Result[0]->Longitude;
          $is_ON = true;
          if ((time() - $last_time) > 1800){
             //$is_ON = false;
          }
          // Name
          $ViewResult[$row->imei]['lastUpdate'] = $Result[0]->GPS_DataTime;
          $ViewResult[$row->imei]['latitude'] = $Latitude;
          $ViewResult[$row->imei]['longitude'] = $Longitude;
          $ViewResult[$row->imei]['imei'] = $row->name;
          $ViewResult[$row->imei]['id'] = $row->imei;
          // Get  last #300 message
          $sql = "SELECT `MIL_status`,`number_of_DTC_codes`,`Speed`,`Analog_Input1` FROM `2locations` WHERE `Message_ID`='300' AND `Modem_ID`='".$row->imei."' ORDER BY `id` DESC";
     
          $Result = DB::query(Database::SELECT, $sql)->as_object()->execute();
          if ($Result[0]!==NULL){
          $ViewResult[$row->imei]['MIL_status'] = $Result[0]->MIL_status;
          $ViewResult[$row->imei]['number_of_DTC_codes'] = $Result[0]->number_of_DTC_codes;
          $ViewResult[$row->imei]['speed'] = $Result[0]->Speed;
          $ViewResult[$row->imei]['battery_volt'] = $Result[0]->Analog_Input1;
          }
          else {
            unset($ViewResult[$row->imei]);
            continue;
          }
          // Points
          $sql = "SELECT `score` FROM `weeklyScores` WHERE `imei`='".$row->imei."' ORDER BY `week_start` DESC";
          $Result = DB::query(Database::SELECT, $sql)->as_object()->execute();
          if ($Result[0]!==NULL){
            $ViewResult[$row->imei]['points'] = $Result[0]->score;
          }
          else
          $ViewResult[$row->imei]['points'] = 100;
          // Trips
          $sql = "SELECT count(`id`) as `count` FROM `2locations` WHERE (`Message_ID`='11' AND `Input_Status`='1') AND `Modem_ID`='".$row->imei."'";
          //echo $sql.'<br>';
          $start_trips = DB::query(Database::SELECT, $sql)->as_object()->execute();
          $start_trips = $start_trips[0]->count;
          $sql = "SELECT count(`id`) as `count` FROM `2locations` WHERE (`Message_ID`='11' AND `Input_Status`='0') AND `Modem_ID`='".$row->imei."'";
          //echo $sql.'<br>';
          $end_trips = DB::query(Database::SELECT, $sql)->as_object()->execute();
          $end_trips = $end_trips[0]->count;
          $ViewResult[$row->imei]['trips'] = ($start_trips>$end_trips) ? $end_trips : $start_trips;
          $trips = $ViewResult[$row->imei]['trips'];
          
          //exit();
          // Mileage
          $Mileage = 0;
          $sql = "SELECT `Mileage` FROM `2locations` WHERE `Message_ID`='11' AND `Input_Status`='0' and `Modem_ID`='".$row->imei."' ORDER BY `GPS_DataTime` DESC";
          //echo $sql;
          //exit();
          $Trips_mileage = DB::query(Database::SELECT, $sql)->as_object()->execute();
          foreach ($Trips_mileage as $row1) {
            if ($trips>0)
            {
            $Mileage+=$row1->Mileage;
            $trips--;
            }
          }
          $ViewResult[$row->imei]['mileage'] = $Mileage;
          // Parked/driwing
          $sql = "SELECT `Input_Status`,`Speed`,`Analog_Input1` FROM `2locations` WHERE `Message_ID`='11' AND `Modem_ID`='".$row->imei."' ORDER BY `GPS_DataTime` DESC";
         // echo $sql;
          //exit();
          $Trips = DB::query(Database::SELECT, $sql)->as_object()->execute();
          if ($Trips[0]!==NULL){
          $ViewResult[$row->imei]['trip_status'] = ($Trips[0]->Input_Status==0) ? 'Parked' : 'Driwing';
          } else {
            unset($ViewResult[$row->imei]);
            continue;
          }

          // Calculate number alerts
          if ($Trips[0]->Input_Status==0)
          {
             $ViewResult[$row->imei]['alerts'] = 'No';
          }
          else
          {
            $sql = "SELECT count(`id`) as `count` FROM `2locations` WHERE `Message_ID` IN (208,199,206,323,321,180,179,162,322,178,170,160,164,165,210,193,163) AND `Modem_ID`=".$row->imei." AND GPS_DataTime > (SELECT `GPS_DataTime` FROM `2locations` WHERE `Message_ID`='11' AND `Modem_ID`=".$row->imei." AND `Input_Status`='1' ORDER BY `GPS_DataTime` DESC limit 1)";
      
            $Alerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
            $ViewResult[$row->imei]['alerts'] = $Alerts[0]->count;
          }
          // Get device driver name and mark of car
          $sql = "SELECT `drivers_name`,`manufacter` FROM `devices` WHERE `imei`='".$row->imei."'";
            $Info = DB::query(Database::SELECT, $sql)->as_object()->execute();
            $ViewResult[$row->imei]['driver_name'] = $Info[0]->drivers_name;
            $ViewResult[$row->imei]['manufacter'] = $Info[0]->manufacter;
        }
          echo json_encode($ViewResult);
   }
   public function action_getAlerts()
   {
      // Calculate Alerts

        if (isset($_GET['user_id'])) { $_POST['user_id'] = $_GET['user_id'];}
        if (isset($_GET['user'])) { $_POST['user'] = $_GET['user'];}
  
        //Get list of manager devices
        if (!isset($_POST['user'])){
        $sql="SELECT `devices`.`imei`,`devices`.`name`, `devices`.`odometer` FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$_POST['user_id']."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
        } else {
        $sql="SELECT `devices`.`imei`,`devices`.`name`, `devices`.`odometer`  FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` WHERE `users_devices`.`user_id`='".$_POST['user_id']."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
        }
        $ManagersDevices = DB::query(Database::SELECT, $sql)->as_object()->execute();
        $Devices = array();
        foreach ($ManagersDevices as $row) {
          $Devices[$row->name] = $row->imei;
        }

        
        $sql = "SELECT * FROM `2locations` WHERE `Modem_ID` IN (".implode(',',$Devices).") AND `Message_ID` IN (208,199,206,323,321,180,322,178,170,160,164,165,210,227,163,11)  ORDER BY `GPS_DataTime` DESC limit 10";
          $Alerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
          $AlertResult = array();
          $ModalData = array();
          foreach ($Alerts as $row) {
             $AlertResult[$row->id]['ID'] = $row->id;
             $AlertResult[$row->id]['Date'] = $row->GPS_DataTime;
             $AlertResult[$row->id]['name'] = array_search($row->Modem_ID, $Devices);
             $AlertResult[$row->id]['Latitude'] = $row->Latitude;
             $AlertResult[$row->id]['Longitude'] = $row->Longitude;

             switch($row->Message_ID)
             {
              case '208' : $AlertResult[$row->id]['Type'] = 'Harsh Breaking'; $AlertResult[$row->id]["Data"] = "Sudden breaking detected"; $AlertResult[$row->id]["icon"] = "icon-road"; break;
              case '199' : $AlertResult[$row->id]['Type'] = 'Harsh Breaking'; $AlertResult[$row->id]["Data"] = "Sudden emergency detected"; $AlertResult[$row->id]["icon"] = "icon-road";break;
              case '206' : $AlertResult[$row->id]['Type'] = 'Sudden Acceleration'; $AlertResult[$row->id]["Data"] = "Sudden acceleration detected"; $AlertResult[$row->id]["icon"] = "icon-road"; break;
              case '323' : $AlertResult[$row->id]['Type'] = 'High Throttle'; $AlertResult[$row->id]["Data"] = "High Throttle - ".$row->Over_STEP.'%'; $AlertResult[$row->id]["icon"] = "icon-tachometer"; break;
              case '321' : $AlertResult[$row->id]['Type'] = 'High RPM'; $AlertResult[$row->id]["Data"] = "Over RPM - ".$row->Over_RPM.' RPM'; $AlertResult[$row->id]["icon"] = "icon-tachometer"; break;
              case '180' : $AlertResult[$row->id]['Type'] = 'Possible Accident Detected!'; 

              //$AlertResult[$row->id]["Data"] = "Impact Detected<br>X_Axis = ".$row->X_Axis." G's<br>Y_Axis = ".$row->Y_Axis." G's<br>Z_Axis = ".$row->Z_Axis." G's"; 
              $AlertResult[$row->id]["Data"] = getLocation($row->Latitude,$row->Longitude);
              $AlertResult[$row->id]["icon"] = "icon-ambulance"; break;
              //case '179' : $AlertResult[$row->id]['Type'] = 'Motion Detected'; break;
              case '163' : $AlertResult[$row->id]['Type'] = 'Speeding Alert'; $AlertResult[$row->id]["Data"] = "Top Speed - ".round($row->MAX_Speed/10)."kmh <br> Average Speed - ".round($row->AVG_Speed/10).". Duration ".$row->MAX_Speed_Duration." seconds"; $AlertResult[$row->id]["icon"] = "icon-road"; break;
              //case '162' : $AlertResult[$row->id]['Type'] = 'Speeding Alert'; $AlertResult[$row->id]["Data"] = "Top Speed - ".$row->Speed.'kmh'; $AlertResult[$row->id]["icon"] = "icon-road"; break;
              case '322' : $AlertResult[$row->id]['Type'] = 'High Temp'; $AlertResult[$row->id]["Data"] = "Over Temperature - ".$row->Over_TEMP.' C'; $AlertResult[$row->id]["icon"] = "icon-fire-extinguisher"; break;
              case '178' : $AlertResult[$row->id]['Type'] = 'GPS Failure'; break;
              case '170' : $AlertResult[$row->id]['Type'] = 'Device Removal'; $AlertResult[$row->id]["Data"] = "It appears the device has been removed from the vehicle!!"; $AlertResult[$row->id]["icon"] = "icon-signout"; break;
              case '160' : $AlertResult[$row->id]['Type'] = 'Device installed'; $AlertResult[$row->id]["Data"] = "It appears the device has been installed to the vehicle!!"; $AlertResult[$row->id]["icon"] = " icon-signin"; break;
              case '164' : $AlertResult[$row->id]['Type'] = 'Geo Fence Entry'; break;
              case '165' : $AlertResult[$row->id]['Type'] = 'Geo Fence Exit'; break;
              case '210' : $AlertResult[$row->id]['Type'] = 'Geo Fence Speed'; break;
              case '227' : $AlertResult[$row->id]['Type'] = 'Harsh Cornering'; $AlertResult[$row->id]["Data"] = "Harsh Cornering was detected!!"; $AlertResult[$row->id]["icon"] = "icon-road"; break;
              //case '193' : $AlertResult[$row->id]['Type'] = 'Low Power Mode'; $AlertResult[$row->id]["Data"] = "Voltage - ".$row->Analog_Input1." volts"; break; break;
               case '11' : if ($row->Input_Status==0) { 
                $AlertResult[$row->id]['Type'] = 'Trip end'; 
                // Get trip datas
                $sql = "SELECT * FROM `scores` WHERE `trip_end` = '".$row->GPS_DataTime."'";
                $scores = DB::query(Database::SELECT, $sql)->as_object()->execute();
                $duration = $this->convert_duration(strtotime($scores[0]->trip_end) - strtotime($scores[0]->trip_start));
                $AlertResult[$row->id]["Data"] = "Journey Completed<br>Score: ".$scores[0]->score."  Distance: ".round($scores[0]->mileage/1000,1)."k  Duration: ".$duration." Alerts: ".$scores[0]->alerts."  Fuel: ".(($scores[0]->fuel_used == 0) ? 'N/A' : $scores[0]->fuel_used." L"); 
                $AlertResult[$row->id]["icon"] = "icon-road";
                } else { unset($AlertResult[$row->id]);}  break;

             }
          }
         // var_dump($AlertResult);
         // exit();
          // Calculate geofence alerts

            $sql = "SELECT * FROM `geoalerts` WHERE `imei` IN (".implode(',',$Devices).") ORDER BY `date` DESC limit 10";
          $Alerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
          foreach ($Alerts as $row) {
            if (strpos($row->details,"ENTERED")){
               $type= "Entry";
            } else if (strpos($row->details,"EXITED")) {
               $type = "Exit";
            } else {
               $type = "Speed";
            }
            //echo $type.'<br>';
            //echo $row->details.'<br>';
            //var_dump(strpos("Pauls Child has just ENTERED in ZONE home", "ENTEREDH"));
            //exit();
            
            array_push($AlertResult, array('ID' => $row->id,'imei' => $row->imei, 'icon' => 'icon-question-sign', 'name' => array_search($row->imei, $Devices), 'Type' => ucfirst(str_replace('speed', '', str_replace('alert', '', str_replace('Geo fence', 'Watchzone '.$type, $row->action)))) , 'Data' => $row->details, 'Date' => $row->date, 'action' => $type));
           }

          // Calculate engine faults

            $sql = "SELECT * FROM `errCommands` WHERE `responseText`!='' AND `numberErrors`>0 AND `imei` IN (".implode(',',$Devices).") ORDER BY `date` DESC limit 10";
          $Alerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
          foreach ($Alerts as $row) {
               array_push($AlertResult, array('ID' => $row->id,'imei' => $row->imei, 'icon' => 'icon-exclamation-sign', 'name' => array_search($row->imei, $Devices), 'Type' => 'Engine fault', 'Data' => $row->responseText, 'Date' => $row->date));
           }
           //var_dump($AlertResult);
          // exit();
            $AlertResult = array_sort_by_date($AlertResult);
            echo json_encode($AlertResult);

   }
   public function action_getData()
   {
       $ViewResult = array();
       
       $ViewResult['TotalAlerts'] = 0;
       $ViewResult['SpeedAlerts'] = 0;
       $ViewResult['BreakingAlerts'] = 0;
       $ViewResult['WatchZoneAlerts'] = 0;
       $ViewResult['mileage'] = 0;
       $ViewResult['fuel'] = 0;
       $ViewResult['nextService'] = PHP_INT_MAX;
       $ViewResult['fuelLeft'] = PHP_INT_MAX;

       if (isset($_GET['user_id'])) { $_POST['user_id'] = $_GET['user_id'];}
       if (isset($_GET['user'])) { $_POST['user'] = $_GET['user'];}
  
        //Get list of manager devices
        if (!isset($_POST['user'])){
        $sql="SELECT `devices`.`imei`,`devices`.`name`, `devices`.`odometer`, `devices`.`manufacter`, `devices`.`model` FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$_POST['user_id']."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
        } else {
        $sql="SELECT `devices`.`imei`,`devices`.`name`, `devices`.`odometer`, `devices`.`manufacter`, `devices`.`model`  FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` WHERE `users_devices`.`user_id`='".$_POST['user_id']."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
        }
        $ManagersDevices = DB::query(Database::SELECT, $sql)->as_object()->execute();
        
        foreach ($ManagersDevices as $row) {
          //var_dump($row->name);
             $today = date("Y-m-d",time()-86400);
             //$today = date("Y-m-d");
             //echo time();
             //exit();
             // Get all alerts today
             $sql = "SELECT * FROM `2locations` WHERE `Modem_ID`='".$row->imei."' AND `Message_ID` IN (208,199,206,323,321,180,179,162,322,178,170,160,164,165,210,193) AND (`GPS_DataTime` > '$today')";  
             $TotalAlerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
             $ViewResult['TotalAlerts']+=count($TotalAlerts);
             // Get Speed Alerts Today
             $sql = "SELECT * FROM `2locations` WHERE `Modem_ID`='".$row->imei."' AND `Message_ID` IN (162) AND (`GPS_DataTime` > '$today')";  
             $TotalAlerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
             $ViewResult['SpeedAlerts']+=count($TotalAlerts);
             // Get Speed Breaking Today
             $sql = "SELECT * FROM `2locations` WHERE `Modem_ID`='".$row->imei."' AND `Message_ID` IN (199) AND (`GPS_DataTime` > '$today')";  
             $TotalAlerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
             $ViewResult['BreakingAlerts']+=count($TotalAlerts);
             // Get WatchZone Alerts Today
             $sql = "SELECT * FROM `geoalerts` WHERE `imei`='".$row->imei."' AND (`date` > '$today')";  
             $TotalAlerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
             $ViewResult['WatchZoneAlerts']+=count($TotalAlerts);
             // Get Kilometers travelled today
             $sql = "SELECT * FROM `scores` WHERE `imei`='".$row->imei."' AND (`trip_start` > '$today')";
             $Total = DB::query(Database::SELECT, $sql)->as_object()->execute(); 
             $counter1 = 0;
             $counter2 = 0;
             foreach ($Total as $row1) {
                $counter1+=$row1->mileage;
                $counter2+=$row1->fuel_used;
             }
             $ViewResult['mileage'] += $counter1;
             // Get Liters of fuel used today
             $ViewResult['fuel'] += $counter2; 
             // Get kilometers to next sevice
             $odometer = $row->odometer;
             $sql = "SELECT * FROM `schedules` WHERE `device_imei`='".$row->imei."' ORDER BY `id` DESC LIMIT 1";
             $Result = DB::query(Database::SELECT, $sql)->as_object()->execute(); 
             if (is_numeric($Result[0]->mil_due) and ($Result[0]->mil_due > 0)){ 
             $diff = $Result[0]->mil_due - $odometer;
             //echo "Diff = ".$diff.'<br>';
             if ($diff < $ViewResult['nextService']) {
                $ViewResult['nextService'] = $diff.'#'.$row->manufacter.' '.$row->model;
             }
             } else {
                $ViewResult['nextService'] = 'N/A';
             }
             if ($ViewResult['nextService'] < 0) {$ViewResult['nextService']='0#'.$row->manufacter.' '.$row->model;}
             // Get Fuel left
             $sql = "SELECT * FROM `2locations` WHERE `Modem_ID` = '".$row->imei."' AND `Message_ID`='300' ORDER BY `id` DESC LIMIT 1";
             $Result = DB::query(Database::SELECT, $sql)->as_object()->execute(); 
             if (is_numeric($Result[0]->Fuel_level) and ($Result[0]->Fuel_level > 0)){
             if ($Result[0]->Fuel_level < $ViewResult['fuelLeft']) {
                $ViewResult['fuelLeft'] = $Result[0]->Fuel_level.'#'.$row->manufacter.' '.$row->model;
             }
             } else {
                $ViewResult['fuelLeft'] = 'N/A';
             }

             
          }
          if ($ViewResult['fuel'] === 0) {$ViewResult['fuel'] = 'N/A';};
          echo json_encode($ViewResult);
          //var_dump($ViewResult);
   }

   public function action_getDashboard()
   {


         $ViewResult = array();
         //var_dump($_GET);
         //exit();
         //echo "Hello";
         if (isset($_GET['user_id'])) { $_POST['user_id'] = $_GET['user_id'];}
         if (isset($_GET['user'])) { $_POST['user'] = $_GET['user'];}
  
        //Get list of manager devices
        if (!isset($_POST['user'])){
        $sql="SELECT `devices`.`imei`,`devices`.`name`, `devices`.`odometer` FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$_POST['user_id']."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
        } else {
        $sql="SELECT `devices`.`imei`,`devices`.`name`, `devices`.`odometer`  FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` WHERE `users_devices`.`user_id`='".$_POST['user_id']."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
        }
        $ManagersDevices = DB::query(Database::SELECT, $sql)->as_object()->execute();

        foreach ($ManagersDevices as $row) {
          $ViewResult[$row->imei]['odometer'] = $row->odometer;
          // Verify device status (if device don't send any action during 30 minutes)
          $sql = "SELECT `GPS_DataTime` FROM `2locations` WHERE `Modem_ID`='".$row->imei."' ORDER BY `id` DESC LIMIT 1";
          $Result = DB::query(Database::SELECT, $sql)->as_object()->execute();
          $last_time = $this->make_timestamp($Result[0]->GPS_DataTime);
          $is_ON = true;
          if ((time() - $last_time) > 1800){
             //$is_ON = false;
          }
          // Name
          $ViewResult[$row->imei]['imei'] = $row->name;
          $ViewResult[$row->imei]['id'] = $row->imei;
          // Get  last #300 message
          $sql = "SELECT `MIL_status`,`number_of_DTC_codes`,`Speed`,`Analog_Input1` FROM `2locations` WHERE `Message_ID`='300' AND `Modem_ID`='".$row->imei."' ORDER BY `id` DESC";
     
          $Result = DB::query(Database::SELECT, $sql)->as_object()->execute();
          if ($Result[0]!==NULL){
          $ViewResult[$row->imei]['MIL_status'] = $Result[0]->MIL_status;
          $ViewResult[$row->imei]['number_of_DTC_codes'] = $Result[0]->number_of_DTC_codes;
          $ViewResult[$row->imei]['speed'] = $Result[0]->Speed;
          $ViewResult[$row->imei]['battery_volt'] = $Result[0]->Analog_Input1;
          }
          else {
            unset($ViewResult[$row->imei]);
            continue;
          }
          // Get errors deatiled info
          $sql = "SELECT * FROM `errCommands` WHERE `imei` = '".$ViewResult[$row->imei]['id']."' AND `numberErrors` = '".$Result[0]->number_of_DTC_codes."' ORDER BY `date` DESC LIMIT 1";
          $Result = DB::query(Database::SELECT, $sql)->as_object()->execute();
          if ($Result[0]!==NULL)
              $ViewResult[$row->imei]['errorsDetailed'] = explode(',',$Result[0]->responseText);
          else {
              $ViewResult[$row->imei]['errorsDetailed'] = "Unknown error";
          }

          // Points
          $sql = "SELECT `score` FROM `weeklyScores` WHERE `imei`='".$row->imei."' ORDER BY `week_start` DESC";
          $Result = DB::query(Database::SELECT, $sql)->as_object()->execute();
          if ($Result[0]!==NULL){
            $ViewResult[$row->imei]['points'] = $Result[0]->score;
          }
          else
          $ViewResult[$row->imei]['points'] = 100;

          if ($ViewResult[$row->imei]['points'] == 0) { $ViewResult[$row->imei]['points'] = 100; };
          // Trips
          $sql = "SELECT count(`id`) as `count` FROM `2locations` WHERE (`Message_ID`='11' AND `Input_Status`='1') AND `Modem_ID`='".$row->imei."'";
          //echo $sql.'<br>';
          $start_trips = DB::query(Database::SELECT, $sql)->as_object()->execute();
          $start_trips = $start_trips[0]->count;
          $sql = "SELECT count(`id`) as `count` FROM `2locations` WHERE (`Message_ID`='11' AND `Input_Status`='0') AND `Modem_ID`='".$row->imei."'";
          //echo $sql.'<br>';
          $end_trips = DB::query(Database::SELECT, $sql)->as_object()->execute();
          $end_trips = $end_trips[0]->count;
          $ViewResult[$row->imei]['trips'] = ($start_trips>$end_trips) ? $end_trips : $start_trips;
          $trips = $ViewResult[$row->imei]['trips'];
          
          //exit();
          // Mileage
          $Mileage = 0;
          $sql = "SELECT `Mileage` FROM `2locations` WHERE `Message_ID`='11' AND `Input_Status`='0' and `Modem_ID`='".$row->imei."' ORDER BY `GPS_DataTime` DESC";
          //echo $sql;
          //exit();
          $Trips_mileage = DB::query(Database::SELECT, $sql)->as_object()->execute();
          foreach ($Trips_mileage as $row1) {
            if ($trips>0)
            {
            $Mileage+=$row1->Mileage;
            $trips--;
            }
          }
          $ViewResult[$row->imei]['mileage'] = $Mileage;
          // Parked/driwing
          $sql = "SELECT `Input_Status`,`Speed`,`Analog_Input1` FROM `2locations` WHERE `Message_ID`='11' AND `Modem_ID`='".$row->imei."' ORDER BY `GPS_DataTime` DESC";
         // echo $sql;
          //exit();
          $Trips = DB::query(Database::SELECT, $sql)->as_object()->execute();
          if ($Trips[0]!==NULL){
          $ViewResult[$row->imei]['trip_status'] = ($Trips[0]->Input_Status==0) ? 'Parked' : 'Driwing';
          } else {
            unset($ViewResult[$row->imei]);
            continue;
          }

          // Calculate number alerts
          if ($Trips[0]->Input_Status==0)
          {
             $ViewResult[$row->imei]['alerts'] = 'No';
          }
          else
          {
            $sql = "SELECT count(`id`) as `count` FROM `2locations` WHERE `Message_ID` IN (208,199,206,323,321,180,179,162,322,178,170,160,164,165,210,193,163) AND `Modem_ID`=".$row->imei." AND GPS_DataTime > (SELECT `GPS_DataTime` FROM `2locations` WHERE `Message_ID`='11' AND `Modem_ID`=".$row->imei." AND `Input_Status`='1' ORDER BY `GPS_DataTime` DESC limit 1)";
      
            $Alerts = DB::query(Database::SELECT, $sql)->as_object()->execute();
            $ViewResult[$row->imei]['alerts'] = $Alerts[0]->count;
          }
        }
       
        if (count($ViewResult)>0)
      {
        //var_dump($ViewResult);
        echo json_encode($ViewResult);
        exit();
      ?>
       <table class='table table-bordered'>       
       <?php foreach($ViewResult as $row)
       { 
       ?>
                 
                <tr>            
              <td>
                         <a href="/<?=(!isset($_POST['user'])) ? 'manager' : 'user'?>/dashboard?imei=<?=$row['id']?>&points=<?=$row['points']?>&trips=<?=$row['trips']?>&mileage=<?=$row['mileage']?>&name=<?=$row['imei']?>&imei=<?=$row['id']?>" class="btn <?=($row['trip_status']=='Parked') ? 'btn-danger' : 'btn-success'?> btn-small"><?=$row['imei']?></a>
              </td>
              <td>
                          <?=$row['MIL_status']?>
              </td>
              <td>
                          <?=$row['number_of_DTC_codes']?> errors

                          <?php if ($row['MIL_status']=='ON') { ?>
                          <div style="color:red; font-size:10px;">
                          <br>Detailed: <br>
                          <?php if (is_array($row['errorsDetailed'])) {?>
                          <?php foreach ($row['errorsDetailed'] as $value) { 
                              echo $value.'<br>';
                            }} else {
                            	echo $row['errorsDetailed'];
                            	}?>
                          </div>
                          <?php }?>


              </td>
              <td <?=($row['trip_status']=='Parked') ? 'style="color:red;"' : 'style="color:green;"'?>>
                         <?=$row['points'].' Points'?>
              </td>
              <td>
                  <?=($is_ON) ? $row['alerts'].' Alerts' : 'Vehicle has not checked in!'?>
              </td>
              <td>
                  <?=$row['trips'].' Trips'?>
              </td>
              <td>
                          <?=($row['mileage']/1000).' Km'?>
              </td>
              <td>
                          <?=$row['trip_status']?>
              </td>
              <td>
                          <?=$row['speed'].' KPH'?>
              </td>
              <td>
                          <?=$row['battery_volt'].' Volts'?>
              </td>
              <!--
              <td>
                 <div id="showPosition"><a href="#" class="btn btn-success btn-small">Location</a></div>
                 <div id='test'></div>
              </td>
              -->
          </tr>     
      
       <?php }?>  
         </table>          
        <?php
      }
      else{
        echo "<p>No any datas from devices...</p>";
        //echo "<a href='/admin/l?clear_all_filters=true'>Show all</a>";
      }
   }

function convert_server_date($date)
{
  $date = explode(' ',$date);
  $month = explode('-',$date[0]);
  $days = explode(':',$date[1]);
  
  $timestamp = mktime ($days[0]+9,$days[1],$days[2],$month[1],$month[2],$month[0]);
  return date('d  M Y h:i:s A',$timestamp);
  //echo $timestamp;
}

function convert_duration($time)
{
  if ($time!=0)
  {
  $hours = floor($time/3600);
  $min = floor(($time%3600)/60);
  $sec = $time%60;

  ($hours==0) ? $hours='' : $hours.=' h';
  ($min==0) ? $min='' : $min.=' min';
  ($sec==0) ? $sec='' : $sec.=' sec';
  return $hours.' '.$min.' '.$sec;
  }
  else
  {
    return '0 sec';
  }  
}
 // Static functions
    protected static function make_timestamp($date)
    {
      if ($date!='0000-00-00 00:00:00'){
      $temp = explode(' ',$date);
      $temp1 = explode('-',$temp[0]);
      $temp2 = explode(':',$temp[1]);
      return mktime($temp2[0],$temp2[1],$temp2[2],$temp1[1],$temp1[2],$temp1[0]);
      } else {
        return 0;
      }
    }

} 
function array_sort_by_date($data) 
{
   $result = array();
   $sort = array();
   foreach ($data as $index => $value) {
      $sort[$index] = strtotime($value['Date']);
   }
   arsort($sort);
   foreach ($sort as $key => $value) {
      $result[$key] = $data[$key];
   }
   return array_slice($result,0,10);
}
function getLocation($lat,$lon){
        $str = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lon&sensor=true";

        //echo $str;
        //exit();
        $result = (model_curl::GETquery($str));
        $result = json_decode($result,true);
        if (isset($result['results'][0]['formatted_address'])) {
          return $result['results'][0]['formatted_address'];
        } else {
          return 'Unknown address'; 
        }
}