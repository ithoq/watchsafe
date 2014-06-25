<div class="hero-unit" style="height: 400px;">
        <h2>Add Schedule</h2>
            <form class="navbar-form pull-left" method="post" accept-charset="utf-8" action="/manager/schedule">
              <input class="span3" type="text" placeholder="Schedule Name" value="<?=(isset($_POST['name']) ? $_POST['name'] : '' )?>" name="name"><?=(isset($errors['name']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['name'].'</span>' : '')?><br />
              <div id="datetimepicker1" class="input-append date">
	            <input class="span3" type="text" name='date_due' placeholder='Date service due' value="<?=(isset($_POST['date_due']) ? $_POST['date_due'] : '' )?>"></input>
	            <span class="add-on">
	              <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
	            </span> <?=(isset($errors['date_due']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['date_due'].'</span>' : '')?>
              </div>
              <input class="span3" type="text" placeholder="Kilometers Service Due(km)"value="<?=(isset($_POST['mil_due']) ? $_POST['mil_due'] : '' )?>" name="mil_due"><?=(isset($errors['mil_due']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors['mil_due'].'</span>' : '')?><br />
              <?=(isset($errors1['main']) ? '<span style="color: red; font-weight: bold; font-size: 15px; padding: 10px;">'.$errors1['main'].'</span><br>' : '')?>
              <button type="submit" name='add' class="btn btn-primary btn-small">Add</button>
            </form>     
      </div>
      <a href="/manager/schedule?imei=<?=$_SESSION['schedule_imei']?>" class="btn-link" >back</a>
    <link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/css/bootstrap-combined.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" media="screen"
     href="http://tarruda.github.com/bootstrap-datetimepicker/assets/css/bootstrap-datetimepicker.min.css">  
    <script type="text/javascript"
     src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js">
    </script> 
    <script type="text/javascript"
     src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.2/js/bootstrap.min.js">
    </script>
    <script type="text/javascript"
     src="http://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.min.js">
    </script>
 
    <script type="text/javascript">
      $('#datetimepicker1').datetimepicker({
        format: 'yyyy-MM-dd hh:mm:ss',
        pick12HourFormat: true,
        language: 'en-EN'
      });
    </script>