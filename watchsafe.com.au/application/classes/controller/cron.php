<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Cron extends Controller {
  // Weekly scores calculator
public function action_weeklyAction()
{
  // Calculate weekly scores
      $sql="SELECT * FROM `devices`";
      $Devices = DB::query(Database::SELECT, $sql)->as_object()->execute();

      $day_of_week =  date("w", time());
      if ($day_of_week==0) $day_of_week = 7;
      $day_of_week--;
      $curr_date = getdate();
      $diff = time() - strtotime($curr_date['mday'].'-'.$curr_date['mon'].'-'.$curr_date['year']);
      $start_date = date('Y-m-d H:i:s',time() - ($day_of_week * 86400 + $diff));
      $end_date = date('Y-m-d H:i:s',time());  


      foreach ($Devices as $row) 
      {
        $sql = "SELECT * FROM `scores` WHERE `imei`='".$row->imei."' and (`trip_start` BETWEEN '".$start_date."' AND '".$end_date."')";
        $Scores = DB::query(Database::SELECT, $sql)->as_object()->execute();
        
          $Scores_arr = array();
          $mileage = 0;
        if (count($Scores)!=0){

          foreach ($Scores as $row1) {
             if (is_numeric($row1->score)){
              $Scores_arr[] = $row1->score;
             }
             $mileage+=$row1->mileage;
          }
          $mileage = round($mileage/1000,2);
          
          if (count($Scores_arr)!=0)
          {
          $Avg_score = round(array_sum($Scores_arr)/count($Scores_arr),2);
          }
          else
          {
            $Avg_score = 100;
          }
          //$sql = ""
          //$sql = "INSERT IGNORE INTO `weeklyScores`(`imei`,`week_start`,`score`) VALUES ('".$row->imei."','".$start_date."','".$Avg_score."')";
          $sql = "INSERT INTO `weeklyScores` (`imei`,`week_start`,`score`,`trips`,`mileage`) VALUES ('".$row->imei."','".$start_date."','".$Avg_score."','".count($Scores)."','".$mileage."') ON DUPLICATE KEY UPDATE `score` = '".$Avg_score."',`trips`='".count($Scores)."',`mileage`='".$mileage."'";
      
    
          DB::query(Database::INSERT, $sql)->as_object()->execute();
        }
      }

}

// Weekly email to manager
public function action_sendWeeklyEmailReportToManagers()
{
   $date_end = date('Y-m-d H:i:s',time());
   $date_start = date('Y-m-d H:i:s',time() - (7*24*60*60));

   $Managers = DB::query(Database::SELECT, "SELECT * FROM `users` JOIN roles_users ON `users`.`id` = `roles_users`.`user_id` WHERE `roles_users`.`role_id`= 3 ")->as_object()->execute();
   foreach ($Managers as $row) {
      // Get active manager devices
     $sql="SELECT `devices`.`imei`,`devices`.`name` FROM `devices` JOIN `users_devices` ON `users_devices`.`device_imei`= `devices`.`imei` JOIN `managers_users` ON `managers_users`.`user_id`=`users_devices`.`user_id` WHERE `managers_users`.`manager_id`='".$row->id."' AND ((SELECT count( Modem_ID ) FROM `2locations` WHERE `2locations`.`Modem_id` = `devices`.`imei`) >0)";
     $Devices = DB::query(Database::SELECT, $sql)->as_object()->execute();
     if (count($Devices) > 0){
  
        foreach ($Devices as $row1) {
          $sql = "SELECT * FROM `scores` WHERE `imei`='".$row1->imei."' AND `trip_start` BETWEEN '".$date_start."' AND '".$date_end."'";
          $device = DB::query(Database::SELECT, $sql)->as_object()->execute();
          if (count($device)>0)
          {
             // Calculate datas for email
            $Avg_score = 0;
            $Trips = 0;
            $Mileage = 0;
            $FuelUsed = 0;
            $EngineFaults = 0;
            $score = 0;
            foreach ($device as $row3) {
              if (is_numeric($row3->score))
                  $score+=$row3->score;
               $Mileage+=$row3->mileage;
               $FuelUsed+=$row3->fuel_used;
               $EngineFaults+=$row3->engine_faults;              
            }
            $Trips = count($device);
            $Avg_score = round($score/$Trips,2);

            $text = "Hi. ".$row->username;
            $text.="\nBelow is a summary of last week’s journeys and Driver scores:";
            $text.="\n".$row1->name." ".$Avg_score." points ".$Trips." trips ".round($Mileage/1000,2)." KM";
               if ($FuelUsed!=0){
                $text.=" ".$FuelUsed." L";
               }
               if ($EngineFaults!=0){
                $text.=" ".$EngineFaults." engine faults";
               }

            // check schedules
             $schedules = ORM::factory('schedule')->where('device_imei','=',$row1->imei)->and_where('is_alert','=',0)->find_all();
             foreach ($schedules as $row4) {
             $date_due_timestamp = $this->make_timestamp($row4->date_due);
              if (($date_due_timestamp - 1209600)<=time()){
                $text.="\n   Warning! Schedule ".$row4->name." date due 2 weeks";
              }
              if (($row4->mil_due -1000)<=$row3->odometer){
                $text.="\n   Warning! Schedule ".$row4->name." mileage due 1000 km";
              }
            }

          }
             
        }
          $email = Email::factory('Weekly report to Manager', $text)
                            ->to($row->email)
                            ->from('admin@gps.com', 'Admin')
                            ->send();
      }  
}

}
// Validate if device lost mobile signal
public function action_validateDeviceForMobileCovarage()
{

}

// Static functions
    protected static function make_timestamp($date)
    {
      $temp = explode(' ',$date);
      $temp1 = explode('-',$temp[0]);
      $temp2 = explode(':',$temp[1]);
      return mktime($temp2[0],$temp2[1],$temp2[2],$temp1[1],$temp1[2],$temp1[0]);
    }
} 