<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>FrostBite Framework Test Template</title>
	<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/application/views/welcome/style.css"/>
</head>

<body>
	<div id="header">
		<h1>Welcome To The Frostbite Framework!</h1>
	</div>
	<div id="container">
		<div id="content">
			<center><?php echo $test_var; ?></center>
			<p>
				You can edit this page by going here:
				<pre>
					application/views/welcome/index.php
				</pre>
				
				<br />
				The page Controller is located here:
				<pre>
					application/controllers/welcome.php
				</pre>
				
				<br />
				And the page Model is located here:
				<pre>
					application/models/welcome_model.php
				</pre>
			</p>
		</div>
		<div id="footer">
			<center>
				Page rendered in {PAGE_LOAD_TIME} seconds, using {MEMORY_USAGE}
				<br />
				<small>Frostbite Framework &copy 2011, Steven Wilson</small>
			</center>
		</div>
	</div>	
</body>
</html>