<!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>
		AdminFlare - Dashboard
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
	</script>
	
	<script src="/templates/AdminFlare/html/assets/javascripts/1.3.0/modernizr-jquery.min.js" type="text/javascript"></script>
	<script src="/templates/AdminFlare/html/assets/javascripts/1.3.0/bootstrap.min.js" type="text/javascript"></script>
	<script src="/templates/AdminFlare/html/assets/javascripts/1.3.0/adminflare.min.js" type="text/javascript"></script>


	<script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDwf6jM1SkCL_tQLN1qzeqaJX4Wor5380A&sensor=true&libraries=geometry,places">
    </script>

	<style type="text/css">
		/* ======================================================================= */
		/* Server Statistics */
		.well.widget-pie-charts .box {
			margin-bottom: -20px;
		}

		/* ======================================================================= */
		/* Why AdminFlare */
		#why-adminflare ul {
			position: relative;
			padding: 0 10px;
			margin: 0 -10px;
		}

		#why-adminflare ul:nth-child(2n) {
			background: rgba(0, 0, 0, 0.02);
		}

		#why-adminflare li {
			padding: 8px 10px;
			list-style: none;
			font-size: 14px;
			padding-left: 23px;
		}

		#why-adminflare li i {
			color: #666;
			font-size: 14px;
			margin: 3px 0 0 -23px;
			position: absolute;
		}


		/* ======================================================================= */
		/* Supported Browsers */
		#supported-browsers header { color: #666; display: block; font-size: 14px; }
			
		#supported-browsers header strong { font-size: 18px; }

		#supported-browsers .span10 { margin-bottom: -15px; text-align: center; }

		#supported-browsers .span10 div {
			margin-bottom: 15px;
			margin-right: 15px;
			display: inline-block;
			width: 120px;
		}

		#supported-browsers .span10 div:last-child { margin-right: 0; }

		#supported-browsers .span10 img { height: 40px; width: 40px; }

		#supported-browsers .span10 span { line-height: 40px; font-size: 14px; font-weight: 600; }
		
		@media (max-width: 767px) {
			#supported-browsers header { text-align: center; margin-bottom: 20px; }
		}

		/* ======================================================================= */
		/* Status panel */
		.status-example { line-height: 0; position:relative; top: 22px }
	</style>
	


</head>
<body>
	<!-- Main navigation bar
		================================================== -->
	<header class="navbar navbar-fixed-top" id="main-navbar">
		<div class="navbar-inner">
			<div class="container">
				<a class="logo" href="#"><img alt="Af_logo" src="/templates/AdminFlare/WatchSafe.logo.png"></a>

				<a class="btn nav-button collapsed" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-reorder"></span>
				</a>
			</div>
		</div>
	</header>
	<!-- / Main navigation bar -->
	
	<!-- Left navigation panel
		================================================== -->
	<nav id="left-panel">
		<div id="left-panel-content">
			<ul>
				<li class="active">
					<a href="/user/dashboard"><span class="icon-dashboard"></span>Dashboard</a>
				</li>
				<li>
					<a href="#"><span class="icon-th-large"></span>Locate</a>
				</li>
				<li>
					<a href="#"><span class="icon-font"></span>Journeys</a>
				</li>
				<li>
					<a href="#"><span class="icon-edit"></span>WatchZones</a>
				</li>
				<li>
					<a href="#"><span class="icon-table"></span>Drives</a>
				</li>
				
				<li>
					<a href="#"><span class="icon-inbox"></span>Vehicles</a>
				</li>
				<li>
					<a href="/user/conf"><span class="icon-cog"></span>Settings</a>
				</li>
				<li>
					<a href="/user/logout"><span class="icon-unlink"></span>Logout</a>
				</li>
				
			</ul>
		</div>
		<div class="icon-caret-down"></div>
		<div class="icon-caret-up"></div>
	</nav>
	<!-- / Left navigation panel -->
	
	<!-- Page content
		================================================== -->
	<section class="container">

		<?=isset($content) ? $content : ''?>

    <footer id="main-footer">
			Copyright © 2014 <a href="#">WatchSafe</a>, all rights reserved.
	</footer>
	
	</section>
	    
</body>
</html>
