<?php
/******************************************************************************
 Pepper
 
 Developer		: Scott McMillin
 Plug-in Name	: User Agent Pies

 http://www.scottmcmillin.com/pepper/
 
 Based on Shaun's User Agent 007 - Thanks Shaun!

 ******************************************************************************/

$installPepper = "DSM_UserAgentPies";

class DSM_UserAgentPies extends Pepper
{
	var $version	= 121; 
	var $info		= array
	(
		'pepperName'	=> 'User Agent Pies',
		'pepperUrl'		=> 'http://www.scottmcmillin.com/pepper/',
		'pepperDesc'	=> 'User Agent information in a handy pie chart.',
		'developerName'	=> 'Scott McMillin',
		'developerUrl'	=> 'http://www.scottmcmillin.com/'
	);
	var $panes		= array
	(
		'User Agent Pies'	=> array
		(
			'Browsers',
			'Platform',
			'Resolution',
			'Flash'
		)
	);

	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		
		$gdVersion = $this->getGDVersion();
		$SI_UserAgent = $this->Mint->getPepperByClassName("SI_UserAgent");
		
		if(!$SI_UserAgent) {
		    return array
    		(
    			'isCompatible'	=> false,
    			'explanation'   => "<p>User Agent Pies requires User Agent 007 1.2 or higher.</p>"
    		);
		}
		
		if ($this->Mint->version >= 120 && $gdVersion > 1) {
		
    		return array
    		(
    			'isCompatible'	=> true
    		);
		
	    } elseif ($this->Mint->version < 120 && $gdVersion > 1) {
	       
	        return array
    		(
    			'isCompatible'	=> false,
    			'explanation'   => "<p>User Agent Pies requires Mint 1.2 or higher.</p>"
    		);
    	
    	} elseif ($this->Mint->version > 120 && $gdVersion < 2) {

	        return array
    		(
    			'isCompatible'	=> false,
    			'explanation'   => "<p>User Agent Pies required the GD Library version 2 or higher to be installed on your server and enabled in PHP.</p>"
    		);

    	} elseif ($this->Mint->version < 120 && $gdVersion < 2) {

	        return array
    		(
    			'isCompatible'	=> false,
    			'explanation'   => "<p>User Agent Pies requires the GD Library version 2 or higher to be installed on your server and enabled in PHP. It also requires Mint 1.2 or higher.</p>"
    		);        
	        
	    
	    }
		
		
		
	}
	
	/**************************************************************************
	 onJavaScript()
	 **************************************************************************/
	function onJavaScript() 
	{
        return array();
	}
	
	/**************************************************************************
	 onRecord()
	 **************************************************************************/
	function onRecord() 
	{
        return array();
	}
	
	/**************************************************************************
	 onDisplay()
	 **************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		$html = '';
		
		switch($pane) 
		{
			/* User Agent Pies *************************************************/
			case 'User Agent Pies': 
				switch($tab) 
				{
					/* Browsers ***********************************************/
					case 'Browsers':
						$html .= $this->getHTML_Browsers();
					break;
					/* Platform ***********************************************/
					case 'Platform':
						$html .= $this->getHTML_Platform();
					break;
					/* Resolution *********************************************/
					case 'Resolution':
						$html .= $this->getHTML_Resolution();
					break;
					/* Flash **************************************************/
					case 'Flash':
						$html .= $this->getHTML_FlashVersion();
					break;
				}
			break;
		}
		return $html;
	}
	
	/**************************************************************************
	 onCustom()
	 
	 **************************************************************************/
	function onCustom() 
	{

	}
		
	/**************************************************************************
	 onDisplayPreferences()
	 
	 **************************************************************************/
	function onDisplayPreferences() {
        $piestest = $this->Mint->cfg['installFull'] . "/pepper/scottmcmillin/pies/pietest.php";
		$threshold = $this->prefs['displayThreshold'];
		
		$preferences['Display']	= "<table>
									<tr>
										<td><label for=\"displayThreshold\">Combine Slices Less Than <input maxlength=\"2\" type=\"text\" name=\"displayThreshold\" value=\"" . $threshold . "\" style=\"width:20px; font-size: 10px;\"/>&nbsp;%</label></td>
									</tr>
								</table>";
								
		$preferences['Debug']	=  "<a href=\"$piestest\" target=\"_blank\">Graphics Test</a>";

		return $preferences;
		}
	
	/**************************************************************************
	 onSavePreferences()
	 
	 **************************************************************************/
	function onSavePreferences() {
		$this->prefs['displayThreshold']	= (isset($_POST['displayThreshold'])) ? $_POST['displayThreshold'] : 0;
		}		
		
	
	/**************************************************************************
	 getHTML_Browsers()
	 
	 **************************************************************************/
	function getHTML_Browsers() {
 		$html = '';

 		//$prefs = $this->Mint->getPluginPreferences($this->plugin_id);
 		$threshold = (isset($this->prefs['displayThreshold'])) ? $this->prefs['displayThreshold'] : 0;

 		$query = "SELECT `browser_family`, COUNT(`browser_family`) as `total`
 					FROM `{$this->Mint->db['tblPrefix']}visit` 
 					WHERE
 					`browser_family`!='' 
 					GROUP BY `browser_family` 
 					ORDER BY `total` DESC 
 					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";

 		$fam = array();
 		$total = 0;
 		if ($result = mysql_query($query)) {
 			while ($r = mysql_fetch_array($result)) {
 				$fam[$r['browser_family']] = $r['total'];
 				$total += $r['total'];
 				}
 			}



 		$piedata = "";
 		$t = 0;

 		foreach ($fam as $family=>$count) {

 				$piedata .= "&c" . $t . "=" . urlencode(round(($count/$total)*100,1)) . "&d" . $t . "=" . urlencode($family);
 				$t++;		
 			}

 		$querystring = "t=" . count($fam) . "&th=" . $threshold . $piedata;

        $mintpath = $this->Mint->cfg['installDir'] . "/pepper/scottmcmillin/pies/";


 		$tableData['hasFolders'] = false;
 		$tableData['thead'] = array(
 			// display name, CSS class(es) for each column
 			array('value'=>'Browser Family','class'=>''),
 			array('value'=>'Total: ' . $total,'class'=>'dsmheader')
 			);
 		$tableData['tbody'][] = array();
 		$html = '<style type="text/css" title="text/css" media="screen">
 					/* <![CDATA[ */
 					.dsmheader { width: 75px; }
 					/* ]]> */
 					</style>';
 		$html .= $this->Mint->generateTable($tableData);
 		$html .= "<div style=\"width:350px;height:270px;margin:0px auto;background-color: white;background:url(" . $mintpath . "pieimage.php?" . $querystring . ") center center no-repeat\"></div>";


 		return $html;
 		}
	
	
	/**************************************************************************
	 getHTML_Platform()
	 **************************************************************************/
 	function getHTML_Platform() {
 		$html = '';

 		//$prefs = $this->Mint->getPluginPreferences($this->plugin_id);
 		$threshold = (isset($this->prefs['displayThreshold'])) ? $this->prefs['displayThreshold'] : 0;

 		$query = "SELECT `platform`, COUNT(`platform`) as `total`
 					FROM `{$this->Mint->db['tblPrefix']}visit` 
 					WHERE
 					`platform`!='' 
 					GROUP BY `platform` 
 					ORDER BY `total` DESC 
 					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";

 		$platforms = array();
 		$total = 0;
 		if ($result = mysql_query($query)) {
 			while ($r = mysql_fetch_array($result)) {
 				$platforms[$r['platform']] = $r['total'];
 				$total += $r['total'];
 				}
 			}


 			$piedata = "";
 			$t = 0;

 			foreach ($platforms as $platform=>$count) {

 					$piedata .= "&c" . $t . "=" . urlencode(round(($count/$total)*100,1)) . "&d" . $t . "=" . urlencode($platform);
 					$t++;		
 				}

 			$querystring = "t=" . count($platforms) . "&th=" . $threshold . $piedata;

            $mintpath = $this->Mint->cfg['installDir'] . "/pepper/scottmcmillin/pies/";

 			$tableData['hasFolders'] = false;
 			$tableData['thead'] = array(
 				// display name, CSS class(es) for each column
 				array('value'=>'Platform','class'=>''),
 				array('value'=>'Total: ' . $total,'class'=>'dsmheader')
 				);
 			$tableData['tbody'][] = array();
 			$html = '<style type="text/css" title="text/css" media="screen">
 						/* <![CDATA[ */
 						.dsmheader { width: 75px; }
 						/* ]]> */
 						</style>';
 			$html .= $this->Mint->generateTable($tableData);
 			$html .= "<div style=\"width:350px;height:270px;margin:0px auto;background-color: white;background:url(" . $mintpath . "pieimage.php?" . $querystring . ") center center no-repeat\"></div>";

 		return $html;
 		}

	
	/**************************************************************************
	 getHTML_Resolution()
	 **************************************************************************/
 	function getHTML_Resolution() {
 		$html = '';

 		//$prefs = $this->Mint->getPluginPreferences($this->plugin_id);
 		$threshold = (isset($this->prefs['displayThreshold'])) ? $this->prefs['displayThreshold'] : 0;

 		$query = "SELECT `resolution`, COUNT(`resolution`) as `total`
 					FROM `{$this->Mint->db['tblPrefix']}visit` 
 					WHERE
 					`resolution`!='' 
 					GROUP BY `resolution` 
 					ORDER BY `total` DESC 
 					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";

 		$res = array();
 		$total = 0;
 		if ($result = mysql_query($query)) {
 			while ($r = mysql_fetch_array($result)) {
 				$res[$r['resolution']] = $r['total'];
 				$total += $r['total'];
 				}
 			}

 			$piedata = "";
 			$t = 0;

 			foreach ($res as $resolution=>$count) {

 					$piedata .= "&c" . $t . "=" . urlencode(round(($count/$total)*100,1)) . "&d" . $t . "=" . urlencode($resolution);
 					$t++;		
 				}

 			$querystring = "t=" . count($res) . "&th=" . $threshold . $piedata;

            $mintpath = $this->Mint->cfg['installDir'] . "/pepper/scottmcmillin/pies/";

 			$tableData['hasFolders'] = false;
 			$tableData['thead'] = array(
 				// display name, CSS class(es) for each column
 				array('value'=>'Screen Resolution','class'=>''),
 				array('value'=>'Total: ' . $total,'class'=>'dsmheader')
 				);
 			$tableData['tbody'][] = array();
 			$html = '<style type="text/css" title="text/css" media="screen">
 						/* <![CDATA[ */
 						.dsmheader { width: 75px; }
 						/* ]]> */
 						</style>';
 			$html .= $this->Mint->generateTable($tableData);
 			$html .= "<div style=\"width:350px;height:270px;margin:0px auto;background-color: white;background:url(" . $mintpath . "pieimage.php?" . $querystring . ") center center no-repeat\"></div>";

 		return $html;
 		}
	
	/**************************************************************************
	 getHTML_FlashVersion()
	 **************************************************************************/
 	function getHTML_FlashVersion() {
 		$html = '';

 		//$prefs = $this->Mint->getPluginPreferences($this->plugin_id);
 		$threshold = (isset($this->prefs['displayThreshold'])) ? $this->prefs['displayThreshold'] : 0;

 		$query = "SELECT `flash_version`, COUNT(`flash_version`) as `total`
 					FROM `{$this->Mint->db['tblPrefix']}visit` 
 					WHERE
 					`flash_version`!='' 
 					GROUP BY `flash_version` 
 					ORDER BY `total` DESC 
 					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";

 		$version = array();
 		$total = 0;
 		if ($result = mysql_query($query)) {
 			while ($r = mysql_fetch_array($result)) {
 				$version[$r['flash_version']] = $r['total'];
 				$total += $r['total'];
 				}
 			}

 			$piedata = "";
 			$t = 0;

 			foreach ($version as $flash_version=>$count) {
 				if ($flash_version==0) {
 					$flash_version = "None";
 					}
 				else {
 					$flash_version = "Flash ".$flash_version;
 					}

 					$piedata .= "&c" . $t . "=" . urlencode(round(($count/$total)*100,1)) . "&d" . $t . "=" . urlencode($flash_version);
 					$t++;		
 				}

 			$querystring = "t=" . count($version) . "&th=" . $threshold . $piedata;

            $mintpath = $this->Mint->cfg['installDir'] . "/pepper/scottmcmillin/pies/";

 			$tableData['hasFolders'] = false;
 			$tableData['thead'] = array(
 				// display name, CSS class(es) for each column
 				array('value'=>'Flash Version','class'=>''),
 				array('value'=>'Total: ' . $total,'class'=>'dsmheader')
 				);
 			$tableData['tbody'][] = array();
 			$html = '<style type="text/css" title="text/css" media="screen">
 						/* <![CDATA[ */
 						.dsmheader { width: 75px; }
 						/* ]]> */
 						</style>';
 			$html .= $this->Mint->generateTable($tableData);
 			$html .= "<div style=\"width:350px;height:270px;margin:0px auto;background-color: white;background:url(" . $mintpath . "pieimage.php?" . $querystring . ") center center no-repeat\"></div>";			
 		return $html;
 		}
	
	
	    // ******************************************
        // GET GD Library Version
        // ******************************************		


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
	
	
	
	
}

?>