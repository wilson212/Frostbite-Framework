<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<title><?php echo $Config->get('site_title'); ?> :: 404 - Not Found</title>
	<link rel="stylesheet" href="core/pages/main.css" type="text/css"/>
</head>

<body>
	<div id="error-box">
		<div class="header">Oops, Page Not Found</div>
		<div class="message">
			You may have mis-typed the URL, or the page was deleted. Please check your spelling.<br /><br />
		</div>
		<div class="links">
			<a href='<?php echo $Config->get('site_base_href'); ?>'>Return to Index</a> | <a href='javascript: history.go(-1)'>Previous Page</a>
		</div>
	</div>
</body>
</html>