<?php 
//return View::factory('timer/show',get_object_vars($this))->render();
class message
{
     
	 public static function alert($head,$text,$return_path,$param_array=null)
	 {
	    return View::factory('alert')->bind('text',$text)
		                             ->bind('return_path',$return_path)
									 ->bind('head',$head)
									 ->bind('param_array',$param_array)
									 ->render();
	 }
	 
	 public static function confirm($head,$text,$return_path,$id_field_name,$id,$param_array=null)
	 {
	    return View::factory('confirm')->bind('text',$text)
		                               ->bind('return_path',$return_path)
									   ->bind('head',$head)
									   ->bind('id_field_name',$id_field_name)
									   ->bind('id',$id)
									   ->bind('param_array',$param_array)
									   ->render();
	 }
	 
 }