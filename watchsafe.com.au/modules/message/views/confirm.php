<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Messages</title>
<script type="text/javascript" src="/jquery/js/jquery-1.6.2.min.js"></script>
<script src="/jquery/development-bundle/ui/jquery.alerts.js" type="text/javascript"></script>
<link href="/jquery/css/jquery.alerts-1.1/jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body>
<script>
jConfirm('<?=$text?>', '<?=$head?>', function(r) {
      $("#response").val(r);
	  $("#alert_response").submit();
    });
</script>
<form  action='<?=$return_path?>' method='post' id='alert_response'>
<input type='hidden' name='<?=$id_field_name?>' value='<?=$id?>' />
<?php
  if ($param_array!=NULL)
  {  
  foreach($param_array as $index=>$value)
  {
?>
  <input type='hidden' name='<?=$index?>' value='<?=$value?>' />
<?php
  }
  }
?>
<input type='hidden' name='response' id='response'/>
</form>
</body>
</html>