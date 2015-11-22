<?php

$configs = scandir(__DIR__ . '/' . 'config');
$available = array();
foreach($configs as $file)
{
	if($file != '.' || $file != '..')
	{
		$available[] = substr($file, 0, -4);
	}
}

?>


<!DOCTYPE html>
<html>
<head>
	<title>Deploy Server</title>
</head>
<body>
	<h1>Available</h1>
	<?php
	foreach ($available as $key => $value) 
	{
		echo $value . ' - <button>Deploy</button><br/>';
	}
	?>
</body>
</html>