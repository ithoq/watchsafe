<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin extends Controller_Template {

    public $template = 'admin/index';
    protected $user,$session;
  
  public function before()
  {
      parent::before();

      $this->session = Session::instance();
      /*
      $email = Email::factory('Password reset', 'New password for user')
                            ->to('dinaris@list.ru')
                            ->from('admin@gps.com', 'Admin')
                            ->send();
                            exit();
                            */
        
      if (!Auth::instance()->logged_in('admin'))
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
		$this->template->content = 'Hello, Admin!';
	}
  //Dashboard
  public function action_dashboard()
  {
    $this->template->content = View::factory('/admin/dashboard')->bind('ViewResult',$data);
  }

  // For commands
  public function action_commandsHisory()
  {
    if (isset($_POST['commands_items']))
           {
              $_SESSION['commands_items_per_page'] = $_POST['commands_items'];
           }
           if (!isset($_SESSION['commands_items_per_page']))
           {
              $_SESSION['commands_items_per_page'] = 10;
           }
           
           $sql = "SELECT count(*) count FROM `historycommands`";
  
           $DevicesCount = DB::query(Database::SELECT,$sql)->execute()->as_array();

            $pag_data = array
                                    (
                                      'total_items'     => $DevicesCount[0]['count'],
                                      'items_per_page'  => $_SESSION['commands_items_per_page'],
                                      
                                    );      
            $pagination =  Pagination::factory($pag_data);
            
            $query = "SELECT * FROM `historycommands`";
            
            $query.=" ORDER BY `id` DESC LIMIT ".$pagination->items_per_page." OFFSET ".$pagination->offset;

            $CommandsResult = DB::query(Database::SELECT, $query)->as_object()->execute();


            $this->template->content = View::factory('/admin/commands/history')->bind('ViewResult',$CommandsResult)
                                                                               ->bind('pagination',$pagination);
  }
  public function action_commands()
  {
     if (empty($_POST) or isset($_POST['commands_items']))
        {
       if (isset($_POST['commands_items']))
           {
              $_SESSION['commands_items_per_page'] = $_POST['commands_items'];
           }
           if (!isset($_SESSION['commands_items_per_page']))
           {
              $_SESSION['commands_items_per_page'] = 10;
           }
           
           $sql = "SELECT count(*) count FROM `commands`";
  
           $DevicesCount = DB::query(Database::SELECT,$sql)->execute()->as_array();

            $pag_data = array
                                    (
                                      'total_items'     => $DevicesCount[0]['count'],
                                      'items_per_page'  => $_SESSION['commands_items_per_page'],
                                      
                                    );      
            $pagination =  Pagination::factory($pag_data);
            
            $query = "SELECT * FROM `commands`";
            
            $query.=" ORDER BY `id` DESC LIMIT ".$pagination->items_per_page." OFFSET ".$pagination->offset;

            $CommandsResult = DB::query(Database::SELECT, $query)->as_object()->execute();


            $this->template->content = View::factory('/admin/commands/index')->bind('ViewResult',$CommandsResult)
                                                                             ->bind('pagination',$pagination);
    }                                                                       
    else
    {
      // Add device
       if (isset($_POST['add_show']))
       {
         $imei_arr = array();
         $sql = "SELECT * FROM `commands`";
         $ImeiResult = DB::query(Database::SELECT, $sql)->as_object()->execute();
         foreach ($ImeiResult as $row) {
           $imei_arr[] = $row->imei;
         }
         if (count($imei_arr)>0)
         {
         $sql = "SELECT * FROM `devices` WHERE `devices`.`imei` NOT IN(".implode(',',$imei_arr).")";
         }
         else
         {
         $sql = "SELECT * FROM `devices`"; 
         }
         $DevicesList = DB::query(Database::SELECT, $sql)->as_object()->execute();

         $this->template->content = View::factory('admin/commands/add')->bind('DevicesList',$DevicesList);
       }
       if (isset($_POST['add']))
       {
           $post = Validate::factory($_POST)->rule('command','not_empty');
                if ($post->check())
                {
                        $model = ORM::factory('command');
                        $model->values(array(
                           'command' => $this->ascii2hex($_POST['command']),
                           'imei' => $_POST['imei']
                        ));
                            $model->save();                               
                            Request::instance()->redirect('/admin/commands');                          
                }
                else
                {
                    $errors = $post->errors('validation');

                     $imei_arr = array();
                     $sql = "SELECT * FROM `commands`";
                     $ImeiResult = DB::query(Database::SELECT, $sql)->as_object()->execute();
                     foreach ($ImeiResult as $row) {
                       $imei_arr[] = $row->imei;
                     }
                     if (count($imei_arr)>0)
                     {
                     $sql = "SELECT * FROM `devices` WHERE `devices`.`imei` NOT IN(".implode(',',$imei_arr).")";
                     }
                     else
                     {
                     $sql = "SELECT * FROM `devices`"; 
                     }
                     $DevicesList = DB::query(Database::SELECT, $sql)->as_object()->execute();

                    $this->template->content = View::factory('/admin/commands/add')->bind('errors',$errors)
                                                                                  ->bind('DevicesList',$DevicesList);
                }    
       }
       // Edit device
       if (isset($_POST['reset']))
       {
         $sql = "UPDATE `commands` SET `status`=0, `ip`='', `response`='' WHERE `id`='".$_POST['id']."'";
         DB::query(Database::UPDATE, $sql)->as_object()->execute();
         Request::instance()->redirect('/admin/commands');
       }

       if (isset($_POST['del_show']))
        {
         $this->template->content = Message::confirm("Warning!","Are you realy want delete this command?","/admin/commands","del",$_POST['id']);
        }
      
    
        if (isset($_POST['id']) and !isset($_POST['reset']) and !isset($_POST['add_show']) and !isset($_POST['add']))
        {

           $device = ORM::factory('command')->where('id','=',$_POST['id'])->find();
           $device->delete();
           Request::instance()->redirect('/admin/commands');
  
        }

    }
  }
  // For device tests
  public function action_deviceTest()
  {
      if (isset($_POST['clear']))
      {
        $sql='DELETE FROM `2locations`';
        DB::query(Database::DELETE,$sql)->execute();
        Request::instance()->redirect('/admin/deviceTest');
      }
      if (isset($_POST['device_filter']))
      {
          $_SESSION['matt1_device_filter'] = $_POST['device_filter'];
      }
      if (!isset($_SESSION['matt1_device_filter']))
      { 
          $_SESSION['matt1_device_filter']='all';
      }

      if (isset($_SESSION['matt1_device_filter']) and ($_SESSION['matt1_device_filter']!='all'))
      {
          $device_filter = " AND `Modem_ID`='".$_SESSION['matt1_device_filter']."'";
      }     
        else
          {
             $device_filter='';
          }

      if (isset($_POST['message_type_filter']))
      {
          $_SESSION['matt1_message_type_filter'] = $_POST['message_type_filter'];
      }
      if (!isset($_SESSION['matt1_message_type_filter']))
      { 
          $_SESSION['matt1_message_type_filter']='all';
      }

      if (isset($_SESSION['matt1_message_type_filter']) and ($_SESSION['matt1_message_type_filter']!='all'))
      {
          $message_type_filter = " AND `Message_ID`='".$_SESSION['matt1_message_type_filter']."'";
      }     
        else
          {
             $message_type_filter='';
          }
      if (isset($_POST['show_all']))
      {
        $_SESSION['matt1_device_filter']='all';
        $_SESSION['matt1_message_type_filter']='all';
        $message_type_filter='';
        $device_filter='';
      }        
          $query="SELECT * FROM `2locations` WHERE 1".$device_filter.$message_type_filter;

          $Result = DB::query(Database::SELECT, $query)->execute();
          
               
           if (isset($_POST['items']))
           {
              $_SESSION['items_per_page'] = $_POST['items'];
           }
           if (!isset($_SESSION['items_per_page']))
           {
              $_SESSION['items_per_page'] = 200;
           }
           
             $pag_data = array
                      (
                        'total_items'     => count($Result),
                        'items_per_page'  => $_SESSION['items_per_page'],
                        
                      );    
          $pagination =  Pagination::factory($pag_data);

          //$ViewResult = ORM::factory('2location')->limit($pagination->items_per_page)->offset($pagination->offset)->find_all();
          $query="SELECT * FROM `2locations` WHERE 1".$device_filter.$message_type_filter." LIMIT ".$pagination->items_per_page." OFFSET ".$pagination->offset;
          //echo $query;
          //exit();
           

          $ViewResult = DB::query(Database::SELECT, $query)->as_object()->execute();
          
           $temp = $result = Database::instance()->query(Database::SELECT, 'SHOW TABLE STATUS');
           $Count = count($Result);
           foreach($temp as $row)
           { 
             if ($row['Name']=='2locations')
             {
              $Size = round($row['Data_length']/1024,2);
             }
             
           }
           
           $DevicesList = ORM::factory('2location')->group_by('Modem_ID')->find_all();
           $MessagesTypesList = ORM::factory('2location')->group_by('Message_ID')->find_all();
      

           $this->template->content = View::factory('/admin/device_tests')->bind('ViewResult',$ViewResult)
                                                                ->bind('pagination',$pagination)
                                                                ->bind('Size',$Size)
                                                                ->bind('small',$Small)
                                                                ->bind('DevicesList',$DevicesList)
                                                                ->bind('MessagesTypesList',$MessagesTypesList)
                                                                ->bind('Count',$Count); 
  }

  // Devices CRUD
  public function action_devices()
  {   
        //Search            
        if (empty($_POST) or isset($_POST['device_items']) or isset($_POST['search']))
        {
          if (isset($_POST['search']) and (!empty($_POST['search'])))
        {
            $_SESSION['device_search'] = $_POST['search'];
        }   
        if (isset($_POST['show_all']))
        {
            unset($_SESSION['device_search']);
        }   
        if (isset($_SESSION['device_search']))
        {
          $search =" WHERE (`name` LIKE '%".$_SESSION['device_search']."%' OR `imei` LIKE '%".$_SESSION['device_search']."%')";
        }       
          else $search='WHERE 1';

       if (isset($_POST['device_items']))
           {
              $_SESSION['device_items_per_page'] = $_POST['device_items'];
           }
           if (!isset($_SESSION['device_items_per_page']))
           {
              $_SESSION['device_items_per_page'] = 10;
           }
           
           $sql = "SELECT count(*) count FROM `devices` ".$search;
  
           $DevicesCount = DB::query(Database::SELECT,$sql)->execute()->as_array();

            $pag_data = array
                                    (
                                      'total_items'     => $DevicesCount[0]['count'],
                                      'items_per_page'  => $_SESSION['device_items_per_page'],
                                      
                                    );      
            $pagination =  Pagination::factory($pag_data);
            
            $query = "SELECT * FROM `devices` ".$search;
            
            $query.=" ORDER BY `imei` DESC LIMIT ".$pagination->items_per_page." OFFSET ".$pagination->offset;

            $UsersResult = DB::query(Database::SELECT, $query)->as_object()->execute();


            $this->template->content = View::factory('/admin/devices/index')->bind('ViewResult',$UsersResult)
                                                                            ->bind('pagination',$pagination);
    }                                                                       
    else
    {
      // Add device
       if (isset($_POST['add_show']))
       {
         $this->template->content = View::factory('admin/devices/add');
       }
       if (isset($_POST['add']))
       {
           $post = Validate::factory($_POST)->rule('name','not_empty')
                                            ->rule('imei','not_empty')
                                            ->rule('imei','is_numeric')
                                            ->callback('imei', array($this, 'unique_imei'));
                if ($post->check())
                {
                        $model = ORM::factory('device');
                        $model->values(array(
                           'name' => $_POST['name'],
                           'imei' => $_POST['imei'],
                        ));
                            $model->save();                               
                            Request::instance()->redirect('/admin/devices');                          
                }
                else
                {
                    $errors = $post->errors('validation');

                    $this->template->content = View::factory('/admin/devices/add')->bind('errors',$errors);
                }    
       }
       // Edit device
       if (isset($_POST['edit_show']))
       {
         $DeviceResult = ORM::factory('device')->where('id','=',$_POST['id'])->find();
         $this->template->content = View::factory('admin/devices/edit')->bind('DeviceResult',$DeviceResult);
       }
       if (isset($_POST['edit']))
       {
           $post = Validate::factory($_POST)->rule('name','not_empty')
                                            ->rule('imei','not_empty')
                                            ->rule('imei','is_numeric')
                                            ->filter(TRUE, 'trim')
                                            ->rule('id','not_empty')
                                            ->callback('imei', array($this, 'unique_imei'));
                if ($post->check())
                {
                        $model = ORM::factory('device')->where('id','=',$_POST['id'])->find();
                        $model->values(array(
                           'name' => $_POST['name'],
                           'imei' => $_POST['imei'],
                        ));
                            $model->save();                               
                            Request::instance()->redirect('/admin/devices');                         
                }
                else
                {
                    $errors = $post->errors('validation');
                    $DeviceResult = ORM::factory('device')->where('id','=',$_POST['id'])->find();
                    $this->template->content = View::factory('/admin/devices/edit')->bind('DeviceResult',$DeviceResult)
                                                                                   ->bind('errors',$errors);
                }    
       }

       if (isset($_POST['del_show']))
        {
         $this->template->content = Message::confirm("Warning!","Are you realy want delete this device?","/admin/devices","del",$_POST['id']);
        }
      
    
        if (isset($_POST['id']) and !isset($_POST['edit_show']) and !isset($_POST['edit']))
        {
           $device = ORM::factory('device')->where('id','=',$_POST['id'])->find();
           $device->delete();
           Request::instance()->redirect('/admin/devices');
        }

    }
  }
  // Login attemps CRUD
    public function action_login_attempts(){
            
           //Filters
            if (isset($_POST['usertype_filter']))
            {
                $_SESSION['usertype_filter'] = $_POST['usertype_filter'];
            }
            if (!isset($_SESSION['usertype_filter']))
            { 
                $_SESSION['usertype_filter']='all';
            }

           if (isset($_SESSION['usertype_filter']) and ($_SESSION['usertype_filter']!='all'))
            {
              $usertype_filter = " AND `user_login`='".$_SESSION['usertype_filter']."'";
            }     
            else
            {
              $usertype_filter='';
            }

             if (isset($_POST['ip_filter']))
            {
                $_SESSION['ip_filter'] = $_POST['ip_filter'];
            }
            if (!isset($_SESSION['ip_filter']))
            { 
                $_SESSION['ip_filter']='all';
            }

           if (isset($_SESSION['ip_filter']) and ($_SESSION['ip_filter']!='all'))
            {
              $ip_filter = " AND `ip`='".$_SESSION['ip_filter']."'";
            }     
            else
            {
              $ip_filter='';
            }

            // Clear all filters
            if (isset($_GET['clear_all_filters']))
            {
              $_SESSION['ip_filter']='all';
              $_SESSION['usertype_filter']='all';
              $ip_filter='';
              $usertype_filter='';
            }


           if (isset($_POST['usertype_items']))
           {
              $_SESSION['usertype_items_per_page'] = $_POST['usertype_items'];
           }
           if (!isset($_SESSION['usertype_items_per_page']))
           {
              $_SESSION['usertype_items_per_page'] = 10;
           }
           
           $sql = "SELECT count(*) count FROM `login_attempts` WHERE 1".$usertype_filter.$ip_filter;

           $AttemptsCount = DB::query(Database::SELECT,$sql)->execute()->as_array();

            $pag_data = array
                                    (
                                      'total_items'     => $AttemptsCount[0]['count'],
                                      'items_per_page'  => $_SESSION['usertype_items_per_page'],
                                      
                                    );      
            $pagination =  Pagination::factory($pag_data);
  
            
            $query = "SELECT * FROM `login_attempts` WHERE 1".$usertype_filter.$ip_filter;
            
            $query.=" ORDER BY `id` DESC LIMIT ".$pagination->items_per_page." OFFSET ".$pagination->offset;
            
            //echo $query;
            //exit();
            $UserTypeList = DB::query(Database::SELECT, "SELECT DISTINCT `user_login` FROM `login_attempts`")->as_object()->execute();
            $IPList = DB::query(Database::SELECT, "SELECT DISTINCT `ip` FROM `login_attempts`")->as_object()->execute();

            $AttemptsResult = DB::query(Database::SELECT, $query)->as_object()->execute();


            $this->template->content = View::factory('/admin/loginAttempts')->bind('ViewResult',$AttemptsResult)
                                                                            ->bind('pagination',$pagination)
                                                                            ->bind('UserTypeList',$UserTypeList)
                                                                            ->bind('IPList',$IPList);
    }

    // Users CRUD
    public function action_users()
    {
       if (empty($_POST) or isset($_POST['roles_filter']) or !empty($_GET) or isset($_POST['show_all']) or isset($_POST['items']) or isset($_POST['search']))
       {
                    // echo kohana::debug($_GET);           
            //Filters
            // Roles filter
            if (isset($_POST['roles_filter']))
            {
                $_SESSION['users_roles_filter'] = $_POST['roles_filter'];
            }
            if (!isset($_SESSION['users_roles_filter']))
            { 
                $_SESSION['users_roles_filter']='all';
            }
              //end Filters 
            
              // Order by specified value   
                if (isset($_GET['users_order_val']))
                {   
                     $order_value = $_GET['users_order_val'];
            
                    if (isset($_SESSION['users_order_value']) and isset($_SESSION['users_order_action']))
                   {
                       if ($order_value==$_SESSION['users_order_value'])
                     {
                         // Change order_action
                          ($_SESSION['users_order_action']=='DESC') ? $_SESSION['users_order_action']='ASC' :  $_SESSION['users_order_action']='DESC';
                     }
                     else
                          {
                            $_SESSION['users_order_value'] = $order_value;
                            $_SESSION['users_order_action']='ASC';
                      }  
                   }
                   else
                    {
                     $_SESSION['users_order_value'] = $order_value;
                     $_SESSION['users_order_action'] = 'ASC';
                  }
                unset($_GET['users_order_val']);  
                }
                else
                {
                   if (!isset($_SESSION['users_order_value']) and !isset($_SESSION['users_order_action']))
                   {
                     $_SESSION['users_order_value'] = 'id';
                     $_SESSION['users_order_action'] = 'ASC';
                   }
                }
                
              // Search 
                  
                  if (isset($_POST['search']) and (!empty($_POST['search'])))
                  {
                 $_SESSION['users_search'] = $_POST['search'];
              }   
                  if (isset($_POST['show_all']))
                  {
                 unset($_SESSION['users_search']);
                 $_SESSION['users_order_value'] = 'id';
                 $_SESSION['users_order_action'] = 'ASC';
                 $_SESSION['users_roles_filter'] = 'all';
              }   
               // echo 'Value='.$_SESSION['order_value'].'<br>';
               // echo 'Action='.$_SESSION['order_action'].'<br>';
                  
                // Generate query   
                if (isset($_SESSION['users_search']))
                 {
                 
                 $search =" WHERE (`username` LIKE '%".$_SESSION['users_search']."%' OR `fullname` LIKE '%".$_SESSION['users_search']."%' OR `email` LIKE '%".$_SESSION['users_search']."%')";
                                   
                 }       
                 else
                       $search='WHERE 1';
                
                if (isset($_SESSION['users_roles_filter']) and ($_SESSION['users_roles_filter']!='all'))
                      {
                   $search = str_replace('WHERE','',$search);
                   $roles_filter = "JOIN `roles_users` ON (`roles_users`.`user_id` = `users`.`id`) JOIN `roles` ON (`roles_users`.`role_id` = `roles`.`id`) WHERE `roles`.`name` = '".$_SESSION['users_roles_filter']."' AND ";
                }     
                  else
                {
                  $roles_filter='';
                      }       
                  
                $query = "SELECT `users`.*  FROM `users`".$roles_filter.$search;
                
                //echo $query;
                
                
                
                $UsersResult = DB::query(Database::SELECT, $query)->execute();
                
                     
                 if (isset($_POST['items']))
                 {
                    $_SESSION['users_items_per_page'] = $_POST['items'];
                 }
                 if (!isset($_SESSION['users_items_per_page']))
                 {
                    $_SESSION['users_items_per_page'] = 10;
                 }
                 
                   $pag_data = array
                            (
                              'total_items'     => count($UsersResult),
                              'items_per_page'  => $_SESSION['users_items_per_page'],
                              
                            );    
                $pagination =  Pagination::factory($pag_data);
                
                //echo kohana::debug($pagination);
                
                $query.=" ORDER BY `users`.`".$_SESSION['users_order_value']."` ".$_SESSION['users_order_action']." LIMIT ".$pagination->items_per_page." OFFSET ".$pagination->offset;
                
                //echo $query;
                //exit();
                
                $UsersResult = DB::query(Database::SELECT, $query)->as_object()->execute();
                                  
                 foreach ($UsersResult as $row)
                 {
                   $ViewResult[$row->id]['ID'] = $row->id;
                   if ((implode(',',$this->getUserRoles($row->id))) == 'user')
                   {
                     $query = "SELECT * FROM `users` JOIN `managers_users` ON `managers_users`.`manager_id`=`users`.`id` WHERE `managers_users`.`user_id`='".$row->id."'";
                     $UserManager = DB::query(Database::SELECT, $query)->execute();
                     $ViewResult[$row->id]['UserRoles'] = implode(',',$this->getUserRoles($row->id)).'<br>Manager:<br><span style="color:green;">'.$UserManager[0]['username'].'</span>';
                     $ViewResult[$row->id]['Username'] = $row->username;
                   }
                   else if ((implode(',',$this->getUserRoles($row->id))) == 'fleet manager')
                     {
                      $query = "SELECT * FROM `users` JOIN `managers_users` ON `managers_users`.`user_id`=`users`.`id` WHERE `managers_users`.`manager_id`='".$row->id."'";
                      $ManagerUsers = DB::query(Database::SELECT, $query)->execute();
                      $ManagerUsersArr = array();
                      foreach ($ManagerUsers as $row1) {
                        $ManagerUsersArr[] = $row1['username'];
                      }
                      
                      if (count($ManagerUsersArr)>0)
                        {
                          $ViewResult[$row->id]['UserRoles'] = implode(',',$this->getUserRoles($row->id));
                          $ViewResult[$row->id]['Username'] = $row->username.'<br>Users:<br><span style="color:brown;">'.implode('<br>',$ManagerUsersArr).'</span>';
                        }
                        else
                        {
                          $ViewResult[$row->id]['UserRoles'] = implode(',',$this->getUserRoles($row->id));
                          $ViewResult[$row->id]['Username'] = $row->username; 
                        }
                     }
                     else
                       {
                          $ViewResult[$row->id]['UserRoles'] = implode(',',$this->getUserRoles($row->id));
                          $ViewResult[$row->id]['Username'] = $row->username;
                       }

                   // get devices of current user or manager
                       $user_role = implode(',',$this->getUserRoles($row->id));
                       if ($user_role=='user')
                       {
                          $query = "SELECT `device_imei` FROM `users_devices` WHERE `user_id`='".$row->id."'";
                          $DeviceImei = DB::query(Database::SELECT, $query)->execute();
                          $DeviceImei = $DeviceImei[0]['device_imei'];
                          $ViewResult[$row->id]['Devices'] = $DeviceImei;
                       }
                       else if ($user_role=='admin')
                       {
                          $ViewResult[$row->id]['Devices'] = '';
                       }
                       else
                       {
                          $query = "SELECT `device_imei` FROM `users_devices` JOIN `managers_users` ON `users_devices`.`user_id` = `managers_users`.`user_id` WHERE `managers_users`.`manager_id`='".$row->id."'";
                          $DevicesImei = DB::query(Database::SELECT, $query)->as_object()->execute();
                          $DevicesArr = array();
                          foreach ($DevicesImei as $row1) 
                          {
                            $DevicesArr[] = $row1->device_imei;
                          }

                          $ViewResult[$row->id]['Devices'] = implode('<br>',$DevicesArr);
                       }
                   $ViewResult[$row->id]['Logins'] = $row->logins;
                   $ViewResult[$row->id]['LastLogin'] = date("m.d.y - H:m:s",$row->last_login);
                   $ViewResult[$row->id]['Address'] = $row->unit_number.' '.$row->street .' '.$row->post_code.' '.$row->state.' '.$row->suburd;
                   $ViewResult[$row->id]['Email'] = $row->email;
                   $ViewResult[$row->id]['DOB'] = $row->DOB;
                   $ViewResult[$row->id]['Fullname'] = $row->fullname; 
                   $ViewResult[$row->id]['Phone'] = $row->phone;
                   $ViewResult[$row->id]['Suspend'] = $row->suspend;                 
                 }  
                     
                //echo kohana::debug($ViewResult);
               // exit();
              
                 $this->template->content = View::factory('admin/users/index')->bind('ViewResult',$ViewResult)
                                                                              ->bind('pagination',$pagination);
       }
       else
         {

             if (isset($_POST['suspend']))
             {
               $user = ORM::factory('user')->where('id','=',$_POST['id'])->find();
               $user->suspend = 1;
               $user->save();
               Request::instance()->redirect('/admin/users');
             }

             if (isset($_POST['unsuspend']))
             {
               $user = ORM::factory('user')->where('id','=',$_POST['id'])->find();
               $user->suspend = 0;
               $user->save();
               Request::instance()->redirect('/admin/users');
             }

             if (isset($_POST['reset_password_show']))
             {
                $user = ORM::factory('user')->where('id','=',$_POST['id'])->find();
                $_SESSION['reset_password_user_id'] = $user;
                $this->template->content = View::factory('admin/reset_password')->bind('user',$user);
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
                        
                            $this->alert('Email with password was successfully sent', '/admin/users');
              }
             }
            // Edit user or manager
            if (isset($_POST['edit_show']))
            {
               $_SESSION['edit_user_id'] = $_POST['id'];
               $user = DB::query(Database::SELECT, "SELECT * FROM `users` WHERE `id`='".$_POST['id']."'")->execute();
               $user_role = implode(',',$this->getUserRoles($_POST['id']));
               
               if ($user_role=='user'){
                 $user_device = DB::query(Database::SELECT, "SELECT * FROM `users_devices` WHERE `user_id`='".$_POST['id']."'")->execute();
                 $user_device = DB::query(Database::SELECT, "SELECT * FROM `devices` WHERE `imei`='".$user_device[0]['device_imei']."'")->execute();
                 $user_device = $user_device[0];
               }

               $this->template->content = View::factory('/admin/users/edit')->bind('user',$user)
                                                                            ->bind('user_role',$user_role)
                                                                            ->bind('user_device',$user_device);
            }
            if (isset($_POST['edit']))
            {
              $role = $this->getUserRoles($_SESSION['edit_user_id']);
              $id = $_SESSION['edit_user_id'];
              $_POST['id'] = $id;  
              if ($role[0]=='admin')
                 {
                  $post = Validate::factory($_POST)->rule('username','not_empty')
                                                   ->callback('username', array($this, 'unique_username'))
                                                   ->rule('email','not_empty')
                                                   ->rule('id','not_empty')
                                                   ->callback('email', array($this, 'unique_email'));
                }
                else if ($role[0]=='user')
                {
                  $post = Validate::factory($_POST)->rule('username','not_empty')
                                                   ->callback('username', array($this, 'unique_username'))
                                                   ->rule('email','not_empty')
                                                   ->rule('id','not_empty')
                                                   ->rule('device','not_empty')
                                                   ->callback('email', array($this, 'unique_email'));
                }
                else
                {
                   $post = Validate::factory($_POST)->rule('username','not_empty')
                                                   ->callback('username', array($this, 'unique_username'))
                                                   ->rule('email','not_empty')
                                                   ->rule('id','not_empty')
                                                   ->rule('DOB','not_empty')
                                                   ->rule('DOB','regex',array('^[0-9]{2}/[0-9]{2}/[0-9]{4}^'))
                                                   ->rule('fullname','not_empty')
                                                   ->rule('unit_number','not_empty')
                                                   ->rule('street','not_empty')
                                                   ->rule('suburd','not_empty')
                                                   ->rule('post_code','max_length',array('4'))
                                                   ->rule('post_code','not_empty')
                                                   ->rule('post_code','is_numeric')
                                                   ->rule('phone','not_empty')
                                                   ->rule('phone','regex',array('^04[0-9]{8}^'))
                                                   ->rule('phone', 'max_length', array('10'))
                                                   ->callback('email', array($this, 'unique_email'));
                }

             if ($post->check())
                {
                  if ($role[0]=='admin')
                  {
                        $model = ORM::factory('User')->where('id','=',$id)->find();
                        $model->values(array(
                           'username' => $_POST['username'],
                           'email' => $_POST['email'],
                        ));
                            $model->save();
                            unset($_SESSION['edit_user_id']);    
                            Request::instance()->redirect('/admin/users');  
                  }
                  else if ($role[0]=='user')
                  {
                        $model = ORM::factory('User')->where('id','=',$id)->find();
                        $model->values(array(
                           'username' => $_POST['username'],
                           'email' => $_POST['email'],
                        ));
                            $model->save();
                            DB::query(Database::UPDATE,"UPDATE `users_devices` set `device_imei`='".$_POST['device']."' WHERE user_id='".$id."'")->execute();
                            unset($_SESSION['edit_user_id']);    
                            Request::instance()->redirect('/admin/users'); 
                  }
                  else
                  {
                        $model = ORM::factory('User')->where('id','=',$id)->find();
                        $model->values(array(
                           'username' => $_POST['username'],
                           'email' => $_POST['email'],
                           'DOB' => $_POST['DOB'],
                           'phone' => $_POST['phone'],
                           'unit_number' => $_POST['unit_number'],
                           'street' => $_POST['street'],
                           'suburd' => $_POST['suburd'],
                           'post_code' => $_POST['post_code'],
                           'fullname' => $_POST['fullname'],
                        ));
                            $model->save();
                            unset($_SESSION['edit_user_id']);                                         
                            Request::instance()->redirect('/admin/users'); 
                  }                        
                }
                else
                {
                    $errors = $post->errors('validation');
                   // var_dump($errors);
                   // exit();
                    $user = DB::query(Database::SELECT, "SELECT * FROM `users` WHERE `id`='".$id."'")->execute(); 
                    $this->template->content = View::factory('/admin/users/edit')->bind('errors',$errors)
                                                                                 ->bind('user',$user);
                }           
            }
            if (isset($_POST['del_show']))
            {
              echo  Message::confirm("Warning!","Are you realy want delete this user?","/admin/users","del",$_POST['id']);
            }
          
        
            if (isset($_POST['del']))
            {
              if ($_POST['response']=='true')
              {
               $user = ORM::factory('user')->where('id','=',$_POST['del'])->find();
               $user->delete();
               $query = "DELETE FROM `users_devices` WHERE `user_id`='".$_POST['del']."'";
               DB::query(Database::DELETE, $query)->execute();
               $query = "DELETE FROM `managers_users` WHERE `user_id`='".$_POST['del']."'";
               DB::query(Database::DELETE, $query)->execute();
              
               $manager_users = DB::query(Database::SELECT, "SELECT * FROM `managers_users` WHERE `manager_id`='".$_POST['del']."'")->as_object()->execute();
               $number = $manager_users->count();
               if ($number > 0)
                  {
                      foreach ($manager_users as $row) 
                      {
                        $user = ORM::factory('user')->where('id','=',$row->user_id)->find();
                        $user->delete();
                        DB::query(Database::DELETE, "DELETE FROM `users_devices` WHERE `user_id`='".$row->user_id."'")->execute();
                        DB::query(Database::DELETE, "DELETE FROM `managers_users` WHERE `user_id`='".$row->user_id."'")->execute();
                      }
                  }
               Request::instance()->redirect('/admin/users');
              }
            else
              Request::instance()->redirect('/admin/users');
            }
         }
    }

    public function action_register()
	    {
	       if(!Session::instance()->get('role') || Session::instance()->get('role')!=2){
	            Request::instance()->redirect('/main');
	       }else{
    	    if ($_POST)
    	        {
                 if ($_POST['role']=='user')
                 {
                  $post = Validate::factory($_POST)->rule('username','not_empty')
                                                   ->callback('username', array($this, 'unique_username'))
                                                   ->rule('email','not_empty')
                                                   ->callback('email', array($this, 'unique_email'))
                                                   ->rule('password_confirm','not_empty')
                                                   ->rule('password','not_empty')
                                                   ->rule('manager','not_empty')
                                                   ->rule('device','not_empty')
                                                   ->rule('password', 'min_length', array('6'))
                                                   ->rule('password_confirm',  'matches', array('password'))
                                                   ->callback('password', array($this, 'password_validate'));
                }
                else
                {
                   $post = Validate::factory($_POST)->rule('username','not_empty')
                                                   ->callback('username', array($this, 'unique_username'))
                                                   ->rule('email','not_empty')
                                                   ->rule('DOB','not_empty')
                                                   ->rule('DOB','regex',array('^[0-9]{2}/[0-9]{2}/[0-9]{4}^'))
                                                   ->rule('fullname','not_empty')
                                                   ->rule('unit_number','not_empty')
                                                   ->rule('street','not_empty')
                                                   ->rule('state','not_empty')
                                                   ->rule('suburd','not_empty')
                                                   ->rule('post_code','max_length',array('4'))
                                                   ->rule('post_code','not_empty')
                                                   ->rule('post_code','is_numeric')
                                                   ->rule('phone','not_empty')
                                                   ->rule('phone','regex',array('^04[0-9]{8}^'))
                                                   ->rule('phone', 'max_length', array('10'))
                                                   ->callback('email', array($this, 'unique_email'))
                                                   ->rule('password_confirm','not_empty')
                                                   ->rule('password','not_empty')
                                                   ->callback('password', array($this, 'password_validate'))
                                                   ->rule('password', 'min_length', array('6'))
                                                   ->rule('password_confirm',  'matches', array('password'));
                }                                   
                if ($post->check())
                {
                  if ($_POST['role']=='user')
                  {
                        $model = ORM::factory('User');
                        $model->values(array(
                           'username' => $_POST['username'],
                           'email' => $_POST['email'],
                           'password' => $_POST['password'],
                           'password_confirm' => $_POST['password_confirm'],
                           'role' => $_POST['role'],
                        ));
                            $model->save();
                            $model->add('roles', ORM::factory('Role')->where('name', '=', $_POST['role'])->find());
                            $model->add('roles', ORM::factory('Role')->where('name', '=', 'login')->find());

                              // Add specific Manager to User
                  
                           $currect_user_id = ORM::factory('user')->where('username','=',$_POST['username'])->find()->id;
                           DB::query(Database::INSERT,"INSERT INTO `managers_users`(`manager_id`,`user_id`) VALUES ('".$_POST['manager']."','".$currect_user_id."')")->execute();
                           DB::query(Database::INSERT,"INSERT INTO `users_devices`(`user_id`,`device_imei`) VALUES ('".$currect_user_id."','".$_POST['device']."')")->execute();
                                               
                            Request::instance()->redirect('/admin/users');  
                  }
                  else
                  {
                        $model = ORM::factory('User');
                        $model->values(array(
                           'username' => $_POST['username'],
                           'email' => $_POST['email'],
                           'DOB' => $_POST['DOB'],
                           'phone' => $_POST['phone'],
                           'unit_number' => $_POST['unit_number'],
                           'street' => $_POST['street'],
                           'state' => $_POST['state'],
                           'suburd' => $_POST['suburd'],
                           'post_code' => $_POST['post_code'],
                           'fullname' => $_POST['fullname'],
                           'password' => $_POST['password'],
                           'password_confirm' => $_POST['password_confirm'],
                           'role' => $_POST['role'],
                        ));
                            $model->save();
                            $model->add('roles', ORM::factory('Role')->where('name', '=', $_POST['role'])->find());
                            $model->add('roles', ORM::factory('Role')->where('name', '=', 'login')->find());
                                               
                            Request::instance()->redirect('/admin/users'); 
                  }                        
                }
                else
                {
                    $errors = $post->errors('validation');
                   // var_dump($errors);
                   // exit();

                    $this->template->content = View::factory('/admin/register')->bind('errors',$errors);
                }       

    	        }else{
    	           $this->template->content = View::factory('/admin/register');
                }
                
             }
	    }

	public function action_logout()
    {
	   Auth::instance()->logout();
	   Request::instance()->redirect('/main');
	}

  // Static function for alert messages
  public function alert($message,$redirect='/')
  {
    $this->template->content = message::alert("Warning",$message,$redirect);
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

	// Static functions for validate username and email
        public static function unique_username(Validate $array, $field)
            {
                // Check if the username already exists in the database
              if (isset($array['id']))
               {
                 $exists = DB::select(array(DB::expr('COUNT(username)'), 'total'))
                    ->from('users')
                    ->where('username', '=', $array[$field])
                    ->and_where('id','!=',$array['id'])
                    ->execute()
                    ->get('total');
               }
               else
               {
                  $exists = DB::select(array(DB::expr('COUNT(username)'), 'total'))
                    ->from('users')
                    ->where('username', '=', $array[$field])
                    ->execute()
                    ->get('total');
               }      
                if ($exists)
                    $array->error('username', 'is_exists');

            }

        public static function unique_imei(Validate $array, $field)
            {
                // Check if the imei already exists in the database
              //var_dump($array['id']);
              //exit();
               if (isset($array['id']))
               {
                 $exists = DB::select(array(DB::expr('COUNT(imei)'), 'total'))
                    ->from('devices')
                    ->where('imei', '=', $array[$field])
                    ->and_where('id','!=',$array['id'])
                    ->execute()
                    ->get('total');
              }
              else
              {
                $exists = DB::select(array(DB::expr('COUNT(imei)'), 'total'))
                    ->from('devices')
                    ->where('imei', '=', $array[$field])
                    ->execute()
                    ->get('total');
              }
                if ($exists)
                    $array->error('imei', 'is_exists');

            }    

        public static function unique_email(Validate $array, $field)
            {
              if (isset($array['id']))
               {
                // Check if the email already exists in the database
                $exists =  DB::select(array(DB::expr('COUNT(email)'), 'total'))
                    ->from('users')
                    ->where('email', '=', $array[$field])
                    ->and_where('id','!=',$array['id'])
                    ->execute()
                    ->get('total');
               }
               else
               {
                   $exists =  DB::select(array(DB::expr('COUNT(email)'), 'total'))
                    ->from('users')
                    ->where('email', '=', $array[$field])
                    ->execute()
                    ->get('total');
               }     
                if ($exists)
                    $array->error('email', 'is_exists');
            }
      // Static function for get roles array of given user id
        public static function getUserRoles($user_id)
        {
              $roles_id =  DB::select('role_id')
                  ->from('roles_users')
                  ->where('user_id','=',$user_id)
                  ->execute();
          $roles_arr=array();       
          foreach($roles_id as $value)
          {
           
            $role = DB::select()->from('roles')->where('id','=',$value['role_id'])->execute()->get('name');
            if ($role!='login')
            {
               $roles_arr[] = $role;
            }
            
          }
          
          return $roles_arr;
        }
     // Static function for ascii to hex
        function ascii2hex($ascii) {
        $hex = '';
        for ($i = 0; $i < strlen($ascii); $i++) {
        $byte = strtoupper(dechex(ord($ascii{$i})));
        $byte = str_repeat('0', 2 - strlen($byte)).$byte;
        $hex.=$byte." ";
        }
        return $hex;
        }

    // End static functions	

} // End Welcome
