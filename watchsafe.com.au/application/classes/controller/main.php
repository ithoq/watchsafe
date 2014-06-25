<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Main extends Controller_Template {

    public $template = 'main/index';

	public function alert($message,$redirect='/main')
	{
		$this->template->content = message::alert("Warning",$message,$redirect);
	}
    /*
    public function action_test()
    {
       $temp = ORM::factory('2location')->find_all();
       foreach ($temp as $row) {
           $temp1 = ORM::factory('2location')->where('id','=',$row->id)->find();
           $temp1->GPS_DataTime = $this->convert_gpsDataTime_to_timestamp($row->GPS_DataTime);
           $temp1->RTC_DataTime = $this->convert_gpsDataTime_to_timestamp($row->RTC_DataTime);
           $temp1->save();
       }
    }
    function convert_gpsDataTime_to_timestamp($date)
        {
              $year = substr($date,0,4);
              $month = substr($date,4,2);
              $day = substr($date,6,2);
              $hours = substr($date,8,2);
              $min = substr($date,10,2);
              $sec = substr($date,12,2);
              return $year.'-'.$month.'-'.$day.' '.$hours.':'.$min.':'.$sec;
        }
  */
	public function action_index()
	    {
   
              $this->action_view();
	    }
         
	    public function action_view()
	    {
	    if(Auth::instance()->logged_in())
	            {
    	            $this->select_template(Session::instance()->get('role'));
	            }
	    else
	        {
            
	            return $this->action_login();
	        }
	 
	    }
	 
	    public function action_logout()
	    {
	    if (Auth::instance()->logout())
	        {
	           Session::instance()->set('role', 0);
	            return $this->action_login();
	        }
	    else
	        {
                $this->alert('Fail logout!!!');
	        }
	    }

    public function get_role($login){
            $rows = DB::query(Database::SELECT, "SELECT U.`id`, R.`role_id` FROM `users` U, `roles_users` R 
                                                    WHERE  U.`username`='".$login."' 
                                                       AND R.`user_id`=U.`id` 
                                                       AND R.`role_id`<>1")->execute()->as_array();
            Session::instance()->set('role', $rows[0]['role_id']);
            return Session::instance()->get('role');
    }
    
    public function select_template($role){
            if($role!=9){
                switch($role){
                    case 2:
                        Request::instance()->redirect('admin');
                        break;
                    case 4:
                        Request::instance()->redirect('user');
                        break;
                    case 3:
                        Request::instance()->redirect('manager');
                        break;
                }      
           }else{
    		  $this->alert('This user is blocked by admin!!!');
           }    
    }

    public function login_attempt($user_info,$status){
        if(!$user_info){
            DB::query(Database::INSERT, "INSERT `login_attempts` (`user_id`,`user_login`,`ip`,`status`) VALUES ('0','UNKNOWN','".$_SERVER["REMOTE_ADDR"]."','".$status."')")->execute();       
        }else{
            DB::query(Database::INSERT, "INSERT `login_attempts` (`user_id`,`user_login`,`ip`,`status`) VALUES ('".$user_info['id']."','".$user_info['username']."','".$_SERVER["REMOTE_ADDR"]."','".$status."')")->execute();
        }
    }
    
    public function error_login(){
        $rows = DB::query(Database::SELECT, "SELECT * FROM `users` WHERE `username`='".$_POST['username']."' OR `email`='".$_POST['username']."'")->execute()->as_array();
        $rows2 = DB::query(Database::SELECT, "SELECT * FROM `list_ip` WHERE `ip`='".$_SERVER["REMOTE_ADDR"]."'")->execute()->as_array();
        if($rows){
            $this->login_attempt($rows[0],2);
            $this->case_error_status($rows[0]['status_login']);
            $this->case_error_status($rows2[0]['status'],1);
        }else{
            $this->login_attempt(null,3);
            $this->alert('That user does not exist in the system!!!');
        }
    }
    
    public function case_error_status($status,$flag=0){
        $param=$status+10;
        switch($status){
            case 0:
                $this->set_status($param,$flag);
                $this->alert('Incorrect password. The following incorrect attempt will not allow you to log in '.$param.' seconds!!!');
                break;
            case 10:
            case 20:
            case 30:
            case 40:
                $this->set_status($param,$flag);
                $this->alert('Incorrect password. You can not log in for '.$status.' seconds. The following incorrect attempt will not allow you to log in '.$param.' seconds!!!');
                break;
            case 50:
                $this->set_status($param,$flag);
                $this->alert('Incorrect password. You can not log in for '.$status.' seconds. The following incorrect attempt will not allow you to log in 30 minute!!!');
                break;
            case 60:
                $this->set_status(1800,$flag);
                $this->alert('Incorrect password. You can not log in for 30 minute!!!');
                break;
            default:
                $this->set_status(1800,$flag);
                $this->alert('Incorrect password. You can not log in for 30 minute!!!');    
        }        
    }
    
    public function set_status($param,$flag=0){
        if($flag){
            $rows=DB::query(Database::SELECT, "SELECT * FROM `list_ip` WHERE `ip`='".$_SERVER["REMOTE_ADDR"]."'")->execute()->as_array();
            if(!$rows){
                DB::query(Database::INSERT, "INSERT `list_ip` (`ip`,`status`,`mod_date`) VALUES ('".$_SERVER["REMOTE_ADDR"]."','".$param."',NOW())")->execute();
            }else{
                DB::query(Database::UPDATE, "UPDATE `list_ip` SET `status`=".$param.", `mod_date`=NOW() WHERE `ip`='".$_SERVER["REMOTE_ADDR"]."'")->execute();
            }
        }else{
            DB::query(Database::UPDATE, "UPDATE `users` SET `status_login`=".$param.", `date_mode_status`=NOW() WHERE `username`='".$_POST['username']."'")->execute();
        }
    }
    
    public function get_blocked(){
        $wait=0;
        $rows = DB::query(Database::SELECT, "SELECT * FROM `list_ip` WHERE `ip`='".$_SERVER["REMOTE_ADDR"]."'")->execute()->as_array();
        if($rows){
            $wait=time()-($rows[0]['status']+strtotime($rows[0]['mod_date']));
        }else{
            $rows = DB::query(Database::SELECT, "SELECT * FROM `users` WHERE `username`='".$_POST['username']."'")->execute()->as_array();
            if($rows){
                $wait=time()-($rows[0]['status_login']+strtotime($rows[0]['date_mode_status']));
            }
        }
        return $wait;
    }
        
public function action_login() 
   {   
    if (!empty($_POST)){
	  $auth = Auth::instance();
	  
      if ($auth->login($_POST['username'],$_POST['password']))
	  {
	   $rows=DB::query(Database::SELECT, "SELECT * FROM `users` WHERE `username`='".$_POST['username']."' OR `email`='".$_POST['username']."'")->execute()->as_array();
       $_POST['username']=$rows[0]['username'];
	   $wait=$this->get_blocked();
	   if($wait>=0){
	        $this->login_attempt($rows[0],0);
            $role=$this->get_role($_POST['username']);
            $this->set_status(0);
            $this->set_status(0,1);
    		$this->select_template($role);
        }else{
            $this->login_attempt($rows[0],1);
            $this->alert('You must login to post in '.abs($wait).' seconds!!!','/main/logout');
        }
      }
      else{
		  $this->error_login();
        }
    }else{
        // var_dump($_POST);
        //exit();
	 $this->template->content = '';
     //View::factory('main/index')"";
     }
	}

}