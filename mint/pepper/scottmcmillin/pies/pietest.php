<html>
	
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title>pietest</title>
		
	</head>
	
	<body>
		<?php
			//error_reporting(E_ALL);
		
			function getGDVersion($user_ver = 0)
			{
			   if (! extension_loaded('gd')) { return; }
			   static $gd_ver = 0;
			   // Just accept the specified setting if it's 1.
			   if ($user_ver == 1) { $gd_ver = 1; return 1; }
			   // Use the static variable if function was called previously.
			   if ($user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }
			   // Use the gd_info() function if possible.
			   if (function_exists('gd_info')) {
			       $ver_info = gd_info();
			       preg_match('/\d/', $ver_info['GD Version'], $match);
			       $gd_ver = $match[0];
			       return $match[0];
			   }
			   // If phpinfo() is disabled use a specified / fail-safe choice...
			   if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
			       if ($user_ver == 2) {
			           $gd_ver = 2;
			           return 2;
			       } else {
			           $gd_ver = 1;
			           return 1;
			       }
			   }
			   // ...otherwise use phpinfo().
			   ob_start();
			   phpinfo(8);
			   $info = ob_get_contents();
			   ob_end_clean();
			   $info = stristr($info, 'gd version');
			   preg_match('/\d/', $info, $match);
			   $gd_ver = $match[0];
			   return $match[0];
			} 
		
		
		$gdv = getGDVersion();
		$pngSupport = "N/A";
		$gifSupport = "N/A";
		$freetypeSupport = "N/A";

		if ($gdv) {
		   if ($gdv >=2) {      
				$arrGDInfo =  gd_info();
				$gdVersion = $arrGDInfo["GD Version"];
				$pngSupport = $arrGDInfo["PNG Support"]==1 ? "Yes":"<strong>No</strong>";
				$gifSupport = $arrGDInfo["GIF Create Support"]==1  ? "Yes":"<strong>No</strong>";
				$freetypeSupport = $arrGDInfo["FreeType Support"]==1  ? "Yes":"<strong>No</strong>";		
		   } else {
		      $gdVersion = "1 (Not supported by Pies)";
		   }
		} else {	   
		   $gdVersion = "GD Library not installed";
		}
		
		
		// getenv("GDFONTPATH");
		//putenv('GDFONTPATH=' . realpath('.'));
		//print_r($_ENV);
		//echo getenv("GDFONTPATH");
		
		echo "<h2>System Information</h2>";
		echo "<h style=\"margin:4px 0px 0px 0px\">PHP Version</h4> " . phpversion() . " Server API: " . $_SERVER['SERVER_SOFTWARE'];
		echo "<h4 style=\"margin:4px 0px 0px 0px\">GD Version</h4> " . $gdVersion . 
													"<h4 style=\"margin:4px 0px 0px 0px\">PNG Support</h4> " . $pngSupport .
													"<h4 style=\"margin:4px 0px 0px 0px\">GIF Create Support</h4> "  . $gifSupport .
													"<h4 style=\"margin:4px 0px 0px 0px\">FreeType Support</h4> "  . $freetypeSupport . " (" . $arrGDInfo["FreeType Linkage"] . ")</p>" ;
		
		echo "<p>Path to Font: " . realpath(".") . "</p>";
		
		
		?>
		
			<img src="pieimage.php?th=5&t=10&c0=50&d0=Internet+Explorer&c1=33&d1=Firefox&c2=7&d2=Safari&c3=5&d3=Crawler%2FSearch+Engine&c4=3.5&d4=Indeterminable&c5=2&d5=Mozilla&c6=2&d6=Netscape&c7=1&d7=Opera&c8=1&d8=Konqueror&c9=1&d9=Unknown" />
		
		
			<img src="pieimage.php?th=10&t=10&c0=50.6&d0=Internet+Explorer&c1=32.3&d1=Firefox&c2=6.7&d2=Safari&c3=4.4&d3=Crawler%2FSearch+Engine&c4=1.9&d4=Indeterminable&c5=1.6&d5=Mozilla&c6=1.6&d6=Netscape&c7=0.6&d7=Opera&c8=0.4&d8=Konqueror&c9=0&d9=Unknown" />		
		
		
	</body>
	
	
</html>	