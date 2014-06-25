<?php defined('SYSPATH') or die('No direct script access.');

class Controller_User extends Controller_Template {

    public $template = 'user/index';
    protected $user;

    public function before()
	  {
	      parent::before();

	      $session = Session::instance();
	  
	      if (!Auth::instance()->logged_in('user'))
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
		$this->template->content = 'Hello, User!';
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
        $str = "curl -i 'http://www.watchsafe.com.au/ajax/getDashboard?user_id=".$user_id."&user=1'";
 
        $result = exec($str);
        $result = json_decode($result,true);

        $str = "curl -i 'http://www.watchsafe.com.au/ajax/getData?user_id=".$user_id."&user=1'";
        $result1 = exec($str);
        $result1 = json_decode($result1,true);

        $str = "curl -i 'http://www.watchsafe.com.au/ajax/getAlerts?user_id=".$user_id."&user=1'";
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

        $this->template->content = View::factory('/user/dashboard/index')->bind('user_id',$user_id)
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
           // $sql = "SELECT * FROM `2locations` WHERE `Modem_ID`='".$_GET['imei']."' AND (`GPS_DataTime` BETWEEN '".$_GET['start']."' AND '".$_GET['end']."')  ORDER BY `GPS_DataTime`";
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
              case '323' : $AlertResult[$row->id]['Type'] = 'High Throttle'; $ModalData[$row->id]["Data"] = $row->Over_STEP.'%'; $ModalData[$row->id]["Info"] = "High Throttle"; $ModalData[$row->id]["ID"] = $row->id; break;
              case '321' : $AlertResult[$row->id]['Type'] = 'High RPM'; $ModalData[$row->id]["Data"] = $row->Over_RPM.' RPM'; $ModalData[$row->id]["Info"] = "Over RPM"; $ModalData[$row->id]["ID"] = $row->id; break;
              case '180' : $AlertResult[$row->id]['Type'] = 'Impact Detected'; $ModalData[$row->id]["Data"] = "<br>X_Axis = ".$row->X_Axis." G's<br>Y_Axis = ".$row->Y_Axis." G's<br>Z_Axis = ".$row->Z_Axis." G's"; $ModalData[$row->id]["Info"] = "Impact Detected"; $ModalData[$row->id]["ID"] = $row->id; break;
              case '179' : $AlertResult[$row->id]['Type'] = 'Motion Detected'; break;
              case '162' : $AlertResult[$row->id]['Type'] = 'Speeding Alert'; break;
              case '322' : $AlertResult[$row->id]['Type'] = 'High Temp'; $ModalData[$row->id]["Data"] = $row->Over_TEMP.' C'; $ModalData[$row->id]["Info"] = "Over Temperature"; $ModalData[$row->id]["ID"] = $row->id; break;
              case '178' : $AlertResult[$row->id]['Type'] = 'GPS Failure'; break;
              case '170' : $AlertResult[$row->id]['Type'] = 'Device Removal'; break;
              case '160' : $AlertResult[$row->id]['Type'] = 'Device installed'; break;
              case '164' : $AlertResult[$row->id]['Type'] = 'Geo Fence Entry'; break;
              case '165' : $AlertResult[$row->id]['Type'] = 'Geo Fence Exit'; break;
              case '210' : $AlertResult[$row->id]['Type'] = 'Geo Fence Speed'; break;
              case '193' : $AlertResult[$row->id]['Type'] = 'Low Power Mode'; break;
             }
          }

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
         // var_dump($TripsResult);
         // exit();
          if ($is_cut){
             $TripsResult = array_slice(array_reverse($TripsResult),0,10);
          }
          else{
              $TripsResult = array_reverse($TripsResult);
           }
           //var_dump($TripsResult);
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
        $this->template->content = View::factory('/user/dashboard/detailed')->bind('ViewResult',$TripsResult)
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

	public function action_conf()
  {
     if (empty($_POST))
     {
      $this->template->content = View::factory('/user/conf/index');
     }
     else
     {
        if (isset($_POST['change_password_show']))
        {
          $this->template->content = View::factory('/user/conf/change_password');
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
                    $this->template->content = View::factory('/user/conf/change_password')->bind('errors',$errors);
                }                                  
        }
     }
  }

	public function action_logout()
    {
	   Auth::instance()->logout();
	   Request::instance()->redirect('/main');
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

} // End USER
