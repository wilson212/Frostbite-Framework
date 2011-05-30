<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<title><?php echo $Config->get('site_title'); ?> :: <?php echo str_replace(": ", "", ucfirst(strtolower($lvl_txt))); ?></title>
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>system/pages/main.css" type="text/css"/>
</head>

<body>
	<div id="error-box">
		<div class="error-header"><?php echo ucfirst(strtolower($lvl_txt)); ?></div>
		<div class="error-message">
			<b>Message:</b> <?php echo $message; ?><br /><br />
			<?php
				if($file != "none")
				{
			?>
					<b>File:</b> <?php echo $file; ?><br />
					<b>Line:</b> <?php echo $line; 
				} ?>
		</div>
	</div>
</body>
</html>