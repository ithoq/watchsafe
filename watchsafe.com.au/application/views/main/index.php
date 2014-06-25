<!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>
		WatchSafe Dashboard - Sign In
	</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width">

	<script src="/templates/AdminFlare/html/assets/javascripts/1.3.0/adminflare-demo-init.min.js" type="text/javascript"></script>

	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,300,600,700" rel="stylesheet" type="text/css">
	<script type="text/javascript">
		// Include Bootstrap stylesheet 
		document.write('<link href="/templates/AdminFlare/html/assets/css/' + DEMO_ADMINFLARE_VERSION + '/' + DEMO_CURRENT_THEME + '/bootstrap.min.css" media="all" rel="stylesheet" type="text/css" id="bootstrap-css">');
		// Include AdminFlare stylesheet 
		document.write('<link href="/templates/AdminFlare/html/assets/css/' + DEMO_ADMINFLARE_VERSION + '/' + DEMO_CURRENT_THEME + '/adminflare.min.css" media="all" rel="stylesheet" type="text/css" id="adminflare-css">');
		// Include AdminFlare page stylesheet 
		document.write('<link href="/templates/AdminFlare/html/assets/css/' + DEMO_ADMINFLARE_VERSION + '/pages.min.css" media="all" rel="stylesheet" type="text/css">');
	</script>
	
	
	<!--[if lte IE 9]>
		<script src="assets/javascripts/jquery.placeholder.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function () {
				$('input, textarea').placeholder();
			});
		</script>
	<![endif]-->
</head>
<body class="signin-page">
	
	<!-- Page content
		================================================== -->
	<section id="signin-container" style="margin-top:300px;">
		<a href="#" title="AdminFlare" class="header">
			<span>
				Sign in to<br>
				<strong>www.watchsafe.com.au</strong>
			</span>
		</a>
		<form action="/main/index" method="post" accept-charset="utf-8">
			<fieldset>
				<div class="fields">
					<input type="text" name="username" placeholder="Username" id="id_username" tabindex="1">

					<input type="password" name="password" placeholder="Password" id="id_password" tabindex="2">
				</div>
				<button type="submit" class="btn btn-primary btn-block" tabindex="4">Sign In</button>
				<?=isset($content) ? $content : ''?>
			</fieldset>
		</form>
	</section>

</body>
</html>