<?php
/******************************************************************************
 Pepper
 
 Developer      : Kyle Rove
 Plug-in Name   : Fresh View
 Version        : v111
 
 kyle.rove@gmail.com
 http://www.sensoryoutput.com/projects/freshview/
 
 Please email kyle.rove@gmail.com with comments, bugs, or feature requests.
 THIS SOFTWARE IS PROVIDED AS IS.

 PayPal donations to my email address are most appreciated. (Support a poor
 medical student :-)

 This work is licensed under the Creative Commons Attribution-ShareAlike
 License. To view a copy of this license, visit
 http://creativecommons.org/licenses/by-sa/2.5/ or send a letter to
 
   Creative Commons
   543 Howard Street, 5th Floor
   San Francisco, California, 94105
   USA

 ******************************************************************************/

// CHANGE PHP CONFIG TO FIX MAGIC_QUOTES ISSUE associated with XML syntax errors
ini_set("magic_quotes_gpc", "0");
ini_set("magic_quotes_runtime", "0");

if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file

$installPepper = "KR_FreshView";
	
class KR_FreshView extends Pepper
{
	var $version	= 111; // Displays as 1.11
	var $info		= array
	(
		'pepperName'	=> 'Fresh View',
		'pepperUrl'		=> 'http://www.sensoryoutput.com/projects/freshview/',
		'pepperDesc'	=> 'The Fresh View Pepper uses your existing visitor information from your Mint installation and outputs it in a visually-stunning, minty fresh SVG format. Please note that your browser must be SVG-compatible.',
		'developerName'	=> 'Kyle Rove',
		'developerUrl'	=> 'http://www.sensoryoutput.com/'
	);
	var $panes = array
	(
		'Fresh View' => array
		(
			'Past Day','Past Week','Past Month','Past Year'
		)
	);
	var $prefs = array
	(
		'24HourTime' => 0 // show 24 hour time instead of 12 hour time
	);
	var $manifest = array( );
	var $graphParams = array
	(
	    'svgW' => 480,
        'svgH' => 250,
        'graphAreaX' => 63,
        'graphAreaY' => 9,
        'graphAreaW' => 378,
        'graphAreaH' => 180,
        'graphDivYMin' => 2,
        'graphDivYMax' => 9,
        'xValueInt' => 7,
        'yValueInt' => 1,
        'mintURL' => '',
        'freshViewPath' => 'pepper/kylerove/freshview/'
    );

	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{

		if ($this->Mint->version >= 120)
		{
			return array
			(
				'isCompatible'	=> true
			);
		}
		else if ($this->Mint->version < 120)
		{
			return array
			(
				'isCompatible'	=> false,
				'explanation'	=> '<p>This Pepper is only compatible with Mint 1.2 and higher.</p>'
		    );
		}
	}

	/**************************************************************************
	 onJavaScript()
	 **************************************************************************/
	function onJavaScript() {	}
	
	/**************************************************************************
	 onCustom()
	 *************************************************************************/
	function onCustom() 
	{
	
        $this->graphParams['mintURL'] = $this->Mint->cfg['installFull'] . '/';

		/* Display ----------------------------------------------------------*/
		if
		(
			isset($_GET['freshviewDisplay']) && 
			(
				$_GET['freshviewDisplay'] == 'day' || 
				$_GET['freshviewDisplay'] == 'week' || 
				$_GET['freshviewDisplay'] == 'month' || 
				$_GET['freshviewDisplay'] == 'year'
			)
		)
		{
		    // Determine SI_DefaultPepper plugin_id
            $defaultPepper = $this->Mint->getPepperByClassName('SI_Default');
            $defaultPepperVisits = $defaultPepper->data['visits'];
            
		    if (count($defaultPepperVisits) == 5) {

		        switch($_GET['freshviewDisplay']) {
                /* Past day *************************************************/
		            case 'day':
		                echo $this->generateSVG_PastDay($defaultPepperVisits);
		                break;
                /* Past week ************************************************/
		            case 'week':
		                echo $this->generateSVG_PastWeek($defaultPepperVisits);
		                break;
                /* Past month ***********************************************/
		            case 'month':
		                echo $this->generateSVG_PastMonth($defaultPepperVisits);
		                break;
                /* Past year ************************************************/
		            case 'year':
		                echo $this->generateSVG_PastYear($defaultPepperVisits);
		                break;
                }
            }
            else {
                echo $this->generateSVG_DataError();
            }
		}
	}
    
	/**************************************************************************
	 onDisplay()
	 **************************************************************************/	
    function onDisplay($pane,$tab,$column='',$sort='') {
        $html = '';
        
        $this->graphParams['mintURL'] = $this->Mint->cfg['installFull'] . '/';

        switch($pane) {
        /* Visitors ***********************************************************/
            case 'Fresh View': 
                switch($tab) {
                /* Past day ************************************************/
                    case 'Past Day':
                        $html .= $this->getHTML_PastDay();
                        break;
                /* Past week ***********************************************/
                    case 'Past Week':
                        $html .= $this->getHTML_PastWeek();
                        break;
                /* Past month **********************************************/
                    case 'Past Month':
                        $html .= $this->getHTML_PastMonth();
                        break;
                /* Past year ***********************************************/
                    case 'Past Year':
                        $html .= $this->getHTML_PastYear();
                        break;
                    }
                break;
            }
        return $html;
        }
    
	/**************************************************************************
	 onDisplayPreferences()
	 **************************************************************************/
	function onDisplayPreferences() 
	{  
        /* Fresh View *********************************************************/
        $checked = ($this->prefs['24HourTime'])?' checked="checked"':'';
        $preferences['Fresh View']  = <<<HERE
<table>
    <tr>
        <th scope="row">24 Hour Time</th>
        <td><input type="checkbox" id="24HourTime" name="24HourTime" value="1"$checked /></td>
    </tr>
    <tr>
        <td></td>
        <td>Switch the Past Day graph to show 24 hour time (0 - 23) instead of 12 hour time (1 - 12 am and pm).</td>
    </tr>
</table>

HERE;
        
        return $preferences;
        }
    
	/**************************************************************************
	 onSavePreferences()
	 **************************************************************************/
	function onSavePreferences() 
	{
		$this->prefs['24HourTime']     = (isset($_POST['24HourTime']))?1:0;
	}
    
    /**************************************************************************
     getHTML_PastDay()
     **************************************************************************/
    function getHTML_PastDay() {
        $tableData['table'] = array('id'=>'','class'=>'');
        $tableData['thead'] = array(array('value'=>'Reflects last 24 hours','class'=>'stacked-rows'),
                                    array('value'=>' ','class'=>''));
        $html = $this->Mint->generateTable($tableData);
        $html .= '
  <object type="image/svg+xml" name="pastDayGraph" width="100%" height="' . $this->graphParams['svgH'] . '" data="' . $this->graphParams['mintURL'] . $this->graphParams['freshViewPath'] . 'graph_pastday.php" style="border-top: 1px solid #e3f1cb; border-bottom: 1px solid #e3f1cb; margin: 0; padding: 0; background-color: #edf7df;">
    <p style="margin: 10px; padding: 10px; text-align: center; overflow: hidden;">Your browser does not support Scalable Vector Graphics. upgrade to an SVG-compatible web browser, like <a href="http://www.mozilla.org/projects/firefox/">Firefox 1.5</a> or a <a href="http://nightly.webkit.org/builds/">Safari nightly build</a>, or install the <a href="http://www.adobe.com/svg/viewer/install/auto/" title="Adobe SVG Viewer Download Area">Adobe SVG Viewer</a> for other browsers.</p>
    <p style="margin: 10px; padding: 10px; text-align: center; overflow: hidden;">Visit the <a href="http://www.sensoryoutput.com/freshview/">Fresh View home page</a> for more information about viewing these SVG graphs.</p>
  </object>
';
        
        return $html;
        }
    
    /**************************************************************************
     getHTML_PastWeek()
     **************************************************************************/
    function getHTML_PastWeek() {
        $tableData['table'] = array('id'=>'','class'=>'');
        $tableData['thead'] = array(array('value'=>'Reflects last 7 days','class'=>'stacked-rows'),
                                    array('value'=>' ','class'=>''));
        $html = $this->Mint->generateTable($tableData);
        $html .= '
  <object type="image/svg+xml" name="pastWeekGraph" width="100%" height="' . $this->graphParams['svgH'] . '" data="' . $this->graphParams['mintURL'] . $this->graphParams['freshViewPath'] . 'graph_pastweek.php" style="border-top: 1px solid #e3f1cb; border-bottom: 1px solid #e3f1cb; margin: 0; padding: 0; background-color: #edf7df;">
    <p style="margin: 10px; padding: 10px; text-align: center; overflow: hidden;">Your browser does not support Scalable Vector Graphics. upgrade to an SVG-compatible web browser, like <a href="http://www.mozilla.org/projects/firefox/">Firefox 1.5</a> or a <a href="http://nightly.webkit.org/builds/">Safari nightly build</a>, or install the <a href="http://www.adobe.com/svg/viewer/install/auto/" title="Adobe SVG Viewer Download Area">Adobe SVG Viewer</a> for other browsers.</p>
    <p style="margin: 10px; padding: 10px; text-align: center; overflow: hidden;">Visit the <a href="http://www.sensoryoutput.com/freshview/">Fresh View home page</a> for more information about viewing these SVG graphs.</p>
  </object>
';
        
        return $html;
        }


    /**************************************************************************
     getHTML_PastMonth()
     **************************************************************************/
    function getHTML_PastMonth() {
        $tableData['table'] = array('id'=>'','class'=>'');
        $tableData['thead'] = array(array('value'=>'Reflects last 4 weeks','class'=>'stacked-rows'),
                                    array('value'=>' ','class'=>''));
        $html = $this->Mint->generateTable($tableData);
        $html .= '
  <object type="image/svg+xml" name="pastMonthGraph" width="100%" height="' . $this->graphParams['svgH'] . '" data="' . $this->graphParams['mintURL'] . $this->graphParams['freshViewPath'] . 'graph_pastmonth.php" style="border-top: 1px solid #e3f1cb; border-bottom: 1px solid #e3f1cb; margin: 0; padding: 0; background-color: #edf7df;">
    <p style="margin: 10px; padding: 10px; text-align: center; overflow: hidden;">Your browser does not support Scalable Vector Graphics. upgrade to an SVG-compatible web browser, like <a href="http://www.mozilla.org/projects/firefox/">Firefox 1.5</a> or a <a href="http://nightly.webkit.org/builds/">Safari nightly build</a>, or install the <a href="http://www.adobe.com/svg/viewer/install/auto/" title="Adobe SVG Viewer Download Area">Adobe SVG Viewer</a> for other browsers.</p>
    <p style="margin: 10px; padding: 10px; text-align: center; overflow: hidden;">Visit the <a href="http://www.sensoryoutput.com/freshview/">Fresh View home page</a> for more information about viewing these SVG graphs.</p>
  </object>
';
        
        return $html;
        }
        
    /**************************************************************************
     getHTML_PastYear()
     **************************************************************************/
    function getHTML_PastYear() {
        $tableData['table'] = array('id'=>'','class'=>'');
        $tableData['thead'] = array(array('value'=>'Reflects last 12 months','class'=>'stacked-rows'),
                                    array('value'=>' ','class'=>''));
        $html = $this->Mint->generateTable($tableData);
        $html .= '
  <object type="image/svg+xml" name="pastYearGraph" width="100%" height="' . $this->graphParams['svgH'] . '" data="' . $this->graphParams['mintURL'] . $this->graphParams['freshViewPath'] . 'graph_pastyear.php" style="border-top: 1px solid #e3f1cb; border-bottom: 1px solid #e3f1cb; margin: 0; padding: 0; background-color: #edf7df;">
    <p style="margin: 10px; padding: 10px; text-align: center; overflow: hidden;">Your browser does not support Scalable Vector Graphics. upgrade to an SVG-compatible web browser, like <a href="http://www.mozilla.org/projects/firefox/">Firefox 1.5</a> or a <a href="http://nightly.webkit.org/builds/">Safari nightly build</a>, or install the <a href="http://www.adobe.com/svg/viewer/install/auto/" title="Adobe SVG Viewer Download Area">Adobe SVG Viewer</a> for other browsers.</p>
    <p style="margin: 10px; padding: 10px; text-align: center; overflow: hidden;">Visit the <a href="http://www.sensoryoutput.com/freshview/">Fresh View home page</a> for more information about viewing these SVG graphs.</p>
  </object>
';
        
        return $html;
        }

    /**************************************************************************
     getHTML_DataError()
     **************************************************************************/
    function generateSVG_DataError() {
        $svg['err_msg'] = 'There was an unknown problem obtaining the Default Pepper data. Please verify that you have visitors in your database by checking the Visits pane for non-zero entries.';

        $svgTemplatePath = $this->graphParams['freshViewPath'] . "templates/error_template.svg";
        $svgFile = $this->svgTemplate($svgTemplatePath, $svg);
        return $svgFile;   
        }

    /**************************************************************************
     generateSVG_PastDay()
     **************************************************************************/
    function generateSVG_PastDay($visits) {

        $offset = $this->Mint->cfg['offset'];
    
        $thisHour = $this->Mint->getOffsetTime('hour');
        $thisHourFormatted = $this->Mint->offsetDate('H', $thisHour);
        // Past 24 hours
        for ($i = 0; $i < 24; $i++) {
            $j = $thisHour - ($i * 60 * 60);
            if (isset($visits[1][$j])) { $h = $visits[1][$j]; }
            else { $h = array('total'=>'0','unique'=>'0'); }
            $statsData[] = array( 'hour' => $this->Mint->offsetDate('H', $j),
                                  'hits' => ((isset($h['total']))?$h['total']:'0'),
                                  'uniques' => ((isset($h['unique']))?$h['unique']:'0') );
            }
      
        // Process the data
        $graphScale = $this->getScale($statsData);
        $scaledData = $this->transformData($statsData, $graphScale, 'hour');
        
        // Layout the data
        $svg = $this->generateLayout($statsData, $scaledData, $graphScale, 'day');
        
        // Show only every other hour
        if ($thisHourFormatted % 2 == 1 ) { $svg['oddhour-visible'] = 'visible'; $svg['evnhour-visible'] = 'hidden'; }
        else { $svg['evnhour-visible'] = 'visible'; $svg['oddhour-visible'] = 'hidden'; }
        
        // Put the data into the template and return
        $svgTemplatePath = $this->graphParams['freshViewPath'] . "templates/pastday_template.svg";
        $svgFile = $this->svgTemplate($svgTemplatePath, $svg);
        return $svgFile;
        }

    /**************************************************************************
     generateSVG_PastWeek()
     **************************************************************************/
    function generateSVG_PastWeek($visits) {

        $offset = $this->Mint->cfg['offset'];
        
        $day = $this->Mint->getOffsetTime('today');
        $todayDay = $this->Mint->offsetDate('D', $day);
        // Past 7 days
        for ($i = 0; $i < 7; $i++) {
            $j = $day - ($i * 60 * 60 * 24);
            if (isset($visits[2][$j])) { $d = $visits[2][$j]; }
            else { $d = array('total'=>'0','unique'=>'0'); }
            $statsData[] = array( 'day' => $this->Mint->offsetDate('D', $j),
                                  'hits' => ((isset($d['total']))?$d['total']:'0'),
                                  'uniques' => ((isset($d['unique']))?$d['unique']:'0') );
            }
      
        // Process the data
        $graphScale = $this->getScale($statsData);
        $scaledData = $this->transformData($statsData, $graphScale, 'day');
        
        // Layout the data
        $svg = $this->generateLayout($statsData, $scaledData, $graphScale, 'week');
        
        // Weekend Highlighting
        if (in_array($todayDay,array('Mon','Tue','Wed','Thu','Fri'))) {
            $svg['weekend-1-visible'] = 'visible';
            $svg['weekend-text-1-visible'] = 'visible';
            $svg['weekend-1_w'] = 2 * $svg['grid_w'];
            $svg['weekend-2-visible'] = 'hidden';
            $svg['weekend-text-2-visible'] = 'hidden';
            $svg['weekend-2_x'] = 0;
            $svg['weekend-2_w'] = 0;
            }
        switch ($todayDay) {
            case 'Mon':
                $svg['weekend-1_x'] = $svg['graph_region_x'] + (3.5 * $svg['grid_w']);
                break;
            case 'Tue':
                $svg['weekend-1_x'] = $svg['graph_region_x'] + (2.5 * $svg['grid_w']);
                break;
            case 'Wed':
                $svg['weekend-1_x'] = $svg['graph_region_x'] + (1.5 * $svg['grid_w']);
                break;
            case 'Thu':
                $svg['weekend-1_x'] = $svg['graph_region_x'] + (0.5 * $svg['grid_w']);
                break;
            case 'Fri':
                $svg['weekend-text-1-visible'] = 'hidden';
                $svg['weekend-1_x'] = $svg['graph_region_x'];
                $svg['weekend-1_w'] = 1.5 * $svg['grid_w'];
                break;
            case 'Sat':
                $svg['weekend-1-visible'] = 'visible';
                $svg['weekend-text-1-visible'] = 'hidden';
                $svg['weekend-1_x'] = $svg['graph_region_x'];
                $svg['weekend-1_w'] = 0.5 * $svg['grid_w'];
                $svg['weekend-2-visible'] = 'visible';
                $svg['weekend-text-2-visible'] = 'hidden';
                $svg['weekend-2_x'] = $svg['graph_region_x'] + ($svg['grid_w'] * 5.5);
                $svg['weekend-2_w'] = 0.5 * $svg['grid_w'];
                break;
            case 'Sun':
                $svg['weekend-1-visible'] = 'hidden';
                $svg['weekend-text-1-visible'] = 'hidden';
                $svg['weekend-1_x'] = 0;
                $svg['weekend-1_w'] = 0;
                $svg['weekend-2-visible'] = 'visible';
                $svg['weekend-text-2-visible'] = 'hidden';
                $svg['weekend-2_x'] = $svg['graph_region_x'] + ($svg['grid_w'] * 4.5);
                $svg['weekend-2_w'] = 1.5 * $svg['grid_w'];
                break;
            }
  
        // Put the data into the template and save
        $svgTemplatePath = $this->graphParams['freshViewPath'] . "templates/pastweek_template.svg";
        $svgFile = $this->svgTemplate($svgTemplatePath, $svg);
        return $svgFile;
        }
        
        
    /**************************************************************************
     generateSVG_PastMonth()
     **************************************************************************/
    function generateSVG_PastMonth($visits) {

        $offset = $this->Mint->cfg['offset'];
        
        $day = $this->Mint->getOffsetTime('today');
        for ($i = 0; $i < 29; $i++) {
            $dateStart = $day - ($i * 60 * 60 * 24);
            $dateStop  = $day - ($i * 60 * 60 * 24) + (60 * 60 * 24);
            $date = $this->Mint->offsetDate('M j', $dateStart);
            
            // Days of the month must be pulled from the database
            // as Mint does not serialize this data
            $query = "SELECT COUNT(*) AS `hits`, COUNT(DISTINCT `ip_long`) AS `uniques`
                        FROM `{$this->Mint->db['tblPrefix']}visit`
                        WHERE `dt` > $dateStart AND `dt` <=$dateStop";
            if ($result = $this->query($query)) {
                if ($count = mysql_fetch_array($result)) {
                    $hits = $count['hits'];
                    $uniques = $count['uniques'];
                    $statsData[] = array('day' => $date,
                                         'hits' => $hits,
                                         'uniques' => $uniques);
                    }
                }
            }
        
        // Process the data
        $graphScale = $this->getScale($statsData);
        $scaledData = $this->transformData($statsData, $graphScale, 'day');
        
        // Layout the data
        $svg = $this->generateLayout($statsData, $scaledData, $graphScale, 'month');
        
        // Weekend Highlighting
        $todayDay = gmdate('D',($day + ($offset*3600)));
        for ($i = 1; $i <= 5; $i++) {
            if (($i == 1 || $i == 5) && (in_array($todayDay,array('Mon','Tue','Wed','Thu','Fri')))) {
                $svg['weekend-1-visible'] = 'visible';
                $svg['weekend-1_w'] = 2 * $svg['grid_w'];
                $svg['weekend-5-visible'] = 'hidden';
                $svg['weekend-5_x'] = 0;
                $svg['weekend-5_w'] = 0;
                }
            switch ($todayDay) {
                case 'Mon':
                    $svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + (4.5 * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
                    break;
                case 'Tue':
                    $svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + (3.5 * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
                    break;
                case 'Wed':
                    $svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + (2.5 * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
                    break;
                case 'Thu':
                    $svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + (1.5 * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
                    break;
                case 'Fri':
                    $svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + (0.5 * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
                    break;
                case 'Sat':
                    $svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + ((0 - 0.5) * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
                    if ($i == 1 || $i == 5) {
                        $svg['weekend-1-visible'] = 'visible';
                        $svg['weekend-1_x'] = $svg['graph_region_x'];
                        $svg['weekend-1_w'] = 1.5 * $svg['grid_w'];
                        $svg['weekend-5-visible'] = 'visible';
                        $svg['weekend-5_w'] = 0.5 * $svg['grid_w'];
                        }
                    break;
                case 'Sun':
                    $svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + ((0 - 1.5) * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
                    if ($i == 1 || $i == 5) {
                        $svg['weekend-1-visible'] = 'visible';
                        $svg['weekend-1_x'] = $svg['graph_region_x'];
                        $svg['weekend-1_w'] = 0.5 * $svg['grid_w'];
                        $svg['weekend-5-visible'] = 'visible';
                        $svg['weekend-5_w'] = 1.5 * $svg['grid_w'];
                        }
                    break;
                }
            }
          
        // Put the data into the template and save
        $svgTemplatePath = $this->graphParams['freshViewPath'] . "templates/pastmonth_template.svg";
        $svgFile = $this->svgTemplate($svgTemplatePath, $svg);
        return $svgFile;
        }
        
    /**************************************************************************
     generateSVG_PastYear()
     **************************************************************************/
    function generateSVG_PastYear($visits) {

        $offset = $this->Mint->cfg['offset'];

        // Past 12 months
        $month = $this->Mint->getOffsetTime('month');
        for ($i = 0; $i < 12; $i++) {
            if ($i == 0) { $j = $month; }
            else {
                $days = $this->Mint->offsetDate('t', $this->Mint->offsetMakeGMT(0, 0, 0, $this->Mint->offsetDate('n', $month)-1, 1, $this->Mint->offsetDate('Y', $month))); // days in the month
                $j = $month - ($days * 24 * 3600);
                }
            $month = $j;
            if (isset($visits[4][$j])) { $m = $visits[4][$j]; }
            else { $m = array('total'=>'0','unique'=>'0'); }
            $statsData[] = array('month' => $this->Mint->offsetDate('M', $j),
                                 'hits' => ((isset($m['total']))?$m['total']:'0'),
                                 'uniques' => ((isset($m['unique']))?$m['unique']:'0') );
            }
      
        // Process the data
        $graphScale = $this->getScale($statsData);
        $scaledData = $this->transformData($statsData, $graphScale, 'month');
        
        // Layout the data
        $svg = $this->generateLayout($statsData, $scaledData, $graphScale, 'year');
  
        // Put the data into the template and save
        $svgTemplatePath = $this->graphParams['freshViewPath'] . "templates/pastyear_template.svg";
        $svgFile = $this->svgTemplate($svgTemplatePath, $svg);
        return $svgFile;
        }

    /**************************************************************************
     getScale()
     **************************************************************************/
    function getScale($statsData) {
        // Extract hits, uniques into simpler array
        for ($i = 0; $i < count($statsData); $i++) {
            $hits = $statsData[$i]['hits']; $uniques = $statsData[$i]['uniques'];
            $rawArray[] = $hits;            $rawArray[] = $uniques;
            }
    
        $maxValue = max($rawArray);
        $maxDiv = $this->graphParams['graphDivYMax'];
        $minDiv = $this->graphParams['graphDivYMin'];
        $yScale = 0;

        if ($maxValue == 0) {
            $yScale = 50;
            $maxDiv = 4;
            }
    
        while(!$yScale) {
            if ($maxDiv > $minDiv && $maxDiv != 5) {
                $scaleMax = $maxValue + (5 - $maxValue % 5);
                }
            else if ($maxDiv > $minDiv && $maxDiv == 5) {
                $scaleMax = $maxValue + (5 - $maxValue % 5);
                $maxDiv--;
                }
            else {
                $maxValue += 5;
                $scaleMax = $maxValue + (5 - $maxValue % 5);
                $maxDiv = $this->graphParams['graphDivYMax'];
                }
            
            if (($scaleMax % $maxDiv) == 0) { $yScale = $scaleMax; }
            else { $maxDiv--; }
            }
    
        // Create array with data scale max, divisions
        $graphScale = array( 'data_max' => $yScale,
                             'data_div' => ($maxDiv) );
                                 
        return $graphScale;
        }

    /**************************************************************************
     transformData()
     **************************************************************************/
    function transformData($statsData, $graphScale, $timeUnit) {
        $graphAreaH = $this->graphParams['graphAreaH'];
        $scaleFactor = $graphAreaH / $graphScale['data_max'];

        for ($i = (count($statsData) - 1); $i >= 0; $i--) {
            $time = $statsData[$i][$timeUnit];
            $hits = $statsData[$i]['hits'];
            $uniques = $statsData[$i]['uniques'];
            
            // Y Scale data
            $hits = $hits * $scaleFactor;
            $uniques = $uniques * $scaleFactor;
            
            // Y Mirror data
            $hits = $graphAreaH - $hits;
            $uniques = $graphAreaH - $uniques;
            
            // Y Transform data
            $hits = $hits + $this->graphParams['graphAreaY'];
            $uniques = $uniques + $this->graphParams['graphAreaY'];
            
            // Round off
            $hits = round($hits,0);
            $uniques = round($uniques,0);
            
            $scaledData[] = array($timeUnit => $time,
                                  'hits' => $hits,
                                  'uniques' => $uniques);
            }
            
        return $scaledData;
        }

    /**************************************************************************
     generateLayout()
     **************************************************************************/
     function generateLayout($statsData,$scaledData,$graphScale,$timeUnit) {
     
        // How many data sets and data points per set?
        $dataSets = count($scaledData[0]) - 1;
        $dataPoints = count($scaledData);
     
        // Background
        $svg['svg_w'] = $this->graphParams['svgW'];
        $svg['svg_h'] = $this->graphParams['svgH'];
        $svg['css_path'] = $this->graphParams['mintURL'] . $this->graphParams['freshViewPath'] . 'styles.css';
        $svg['js_path'] = $this->graphParams['mintURL'] . $this->graphParams['freshViewPath'] . 'graph.js';
        
        // Grid pattern
        $svg['grid_w'] = round($this->graphParams['graphAreaW'] / ($dataPoints - 1),2);
        $svg['grid_h'] = $this->graphParams['graphAreaH'] / $graphScale['data_div'];
        
        // Graphing region
        $svg['graph_region_x'] = $this->graphParams['graphAreaX'];
        $svg['graph_region_x2'] = $this->graphParams['graphAreaX'] + $this->graphParams['graphAreaW'];
        $svg['graph_region_y'] = $this->graphParams['graphAreaY'];
        $svg['graph_region_y2'] = $this->graphParams['graphAreaY'] + $this->graphParams['graphAreaH'];
        $svg['graph_region_w'] = $this->graphParams['graphAreaW'];
        $svg['graph_region_h'] = $this->graphParams['graphAreaH'];
        $svg['y-line-template_x'] = 7 + $this->graphParams['graphAreaW'];
        $svg['y-line-start_x'] = $this->graphParams['graphAreaX'] - 7;
        $svg['weekend_w'] = 2 * $svg['grid_w'];
        $svg['weekend_h'] = $svg['graph_region_y2'];
        $svg['weekend-text_x'] = $svg['grid_w'] - 25;
        $svg['x-axis-label_y'] = $svg['graph_region_y2'] + 13;
  
        // X axis
        $xAxis = false;
        $dataMarks = '';
        $dataLabels = '';
        $labelCount = 0;
        for ($j = 0; $j < $dataPoints; $j++) {
      
            if (!$xAxis) {
                if ($timeUnit == 'year') {
                    /* Past Year ***********************************************/
                    $keyName = 'x-axis_' . strtolower($scaledData[$j]['month']);
                    $svg[$keyName] = round($svg['graph_region_x'] + ($j * $svg['grid_w']),1);
                    if ($j == 11) { $xAxis = true; }
                    }
                else if ($timeUnit == 'month') {
                    /* Past Month ***********************************************/
                    if (($j % 7 == 0) || $j == 0 || $j == 28) {
                        $labelCount++;
                        $svg['x-axis_'.$labelCount] = $svg['graph_region_x'] + ($j * $svg['grid_w']);
                        $svg['x-axis-label_'.$labelCount] = $scaledData[$j]['day'];
                        }
                    if ($j == 28) { $xAxis = true; }
                    }
                else if ($timeUnit == 'week') {
                    $keyName = 'x-axis_' . strtolower(substr($scaledData[$j]['day'],0,2));
                    $svg[$keyName] = $svg['graph_region_x'] + ($j * $svg['grid_w']);
                    if ($j == 6) { $xAxis = true; }
                    }
                else if ($timeUnit == 'day') {
                    $hourFormats = array('01'=>'1a','02'=>'2a','03'=>'3a','04'=>'4a','05'=>'5a','06'=>'6a',
                                         '07'=>'7a','08'=>'8a','09'=>'9a','10'=>'10a','11'=>'11a','12'=>'12p',
                                         '13'=>'1p','14'=>'2p','15'=>'3p','16'=>'4p','17'=>'5p','18'=>'6p',
                                         '19'=>'7p','20'=>'8p','21'=>'9p','22'=>'10p','23'=>'11p','00'=>'12a');
                    $keyName = 'x-axis_' . $scaledData[$j]['hour'];
                    $keyLabelName = 'x-axis-label_' . $scaledData[$j]['hour'];
                    $svg[$keyName] = $svg['graph_region_x'] + ($j * $svg['grid_w']);
                    if ($this->prefs['24HourTime'] == 1) { $svg[$keyLabelName] = $scaledData[$j]['hour']; }
                    else { $svg[$keyLabelName] = $hourFormats[$scaledData[$j]['hour']]; }
                    if ($j == 23) { $xAxis = true; }
                    }
                }

            // Define limits for svg tooltip
            $rtLim = $svg['graph_region_x'] + $svg['graph_region_w'];
            $ltLim = $svg['graph_region_x'];
            $topLim = $svg['graph_region_y'];
            $curX = $svg['graph_region_x'] + ($svg['grid_w'] * $j);
            $curYHit = $scaledData[$j]['hits'];
            $curYUnique = $scaledData[$j]['uniques'];
      
            $hitLabelCoords = $this->getTooltipCoords($rtLim, $ltLim, $topLim, $curX,$curYHit);
            $uniqueLabelCoords = $this->getTooltipCoords($rtLim, $ltLim, $topLim, $curX,$curYUnique); 
      
            if ($j == 0) {
                // Define line origin
                $hitsLineCoords  = 'M' . $svg['graph_region_x'] . ',' . $scaledData[$j]['hits'];
                $hitsAreaCoords  = 'M' . $svg['graph_region_x'] . ',' . ($svg['graph_region_y'] + $svg['graph_region_h']) . 'L' . $svg['graph_region_x'] . ',' . $scaledData[$j]['hits'];
                $uniquesLineCoords  = 'M' . $svg['graph_region_x'] . ',' . $scaledData[$j]['uniques'];
                $uniquesAreaCoords  = 'M' . $svg['graph_region_x'] . ',' . ($svg['graph_region_y'] + $svg['graph_region_h']) . 'L' . $svg['graph_region_x'] . ',' . $scaledData[$j]['uniques'];
                }
            else {
                // Define line
                $hitsLineCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . ',' . $scaledData[$j]['hits'];
                $hitsAreaCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . ',' . $scaledData[$j]['hits'];
                $uniquesLineCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . ',' . $scaledData[$j]['uniques'];
                $uniquesAreaCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . ',' . $scaledData[$j]['uniques'];
                }
                    
            $dataMarks .= '        <use id="1_' . $j . '" x="' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . '" y="' . $scaledData[$j]['hits'] . '" xlink:href="#vertex"/>' . "\n" . '        <use id="2_' . $j . '" x="' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . '" y="' . $scaledData[$j]['uniques'] . '" xlink:href="#vertex"/>' . "\n";
            $dataLabels .= '        <text id="label_1_' . $j . '" x="' . $hitLabelCoords['x'] . '" y="' . $hitLabelCoords['y'] . '" visibility="hidden">' . number_format($statsData[$dataPoints - $j - 1]['hits']) . ' total</text>' . "\n" . '        <text id="label_2_' . $j . '" x="' . $uniqueLabelCoords['x'] . '" y="' . $uniqueLabelCoords['y'] . '" visibility="hidden">' . number_format($statsData[$dataPoints - $j - 1]['uniques']) . ' uniques</text>' . "\n";
            }
    
        $hitsAreaCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * ($dataPoints - 1))) . ',' . ($svg['graph_region_y'] + $svg['graph_region_h']) . 'Z';
        $uniquesAreaCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * ($dataPoints - 1))) . ',' . ($svg['graph_region_y'] + $svg['graph_region_h']) . 'Z';
    
        $svg['data-area_1'] = $hitsAreaCoords;
        $svg['data-line_1'] = $hitsLineCoords;
        $svg['data-area_2'] = $uniquesAreaCoords;
        $svg['data-line_2'] = $uniquesLineCoords;
        $svg['data-marks']  = $dataMarks;
        $svg['data-labels'] = $dataLabels;

        // Y axis values
        $svg['y-axis'] = '';
        for ($i = 0; $i < ($graphScale['data_div'] + 1); $i++) {
            if ($i % $this->graphParams['yValueInt'] == 0 && $i != 0 && $i != $graphScale['data_div']) {
                $svg['y-axis'] .= '      <text x="' . ($svg['graph_region_x'] - 10) . '" y="' . (($svg['graph_region_y'] + $svg['grid_h'] * ($graphScale['data_div'] - $i)) + 4.5) . '">' . round($graphScale['data_max'] / $graphScale['data_div'] * $i,0) . '</text>' . "\n"
                               .  '      <use x="' . ($svg['graph_region_x'] - 7) . '" y="' . (($svg['graph_region_y'] + $svg['grid_h'] * ($graphScale['data_div'] - $i))) . '" xlink:href="#y-line"/>' . "\n";
                }
            else {
                $svg['y-axis'] .= '      <text x="' . ($svg['graph_region_x'] - 10) . '" y="' . (($svg['graph_region_y'] + $svg['grid_h'] * ($graphScale['data_div'] - $i)) + 4.5) . '">' . round($graphScale['data_max'] / $graphScale['data_div'] * $i,0) . '</text>' . "\n";
                }
            }
        return $svg;
        }

    /**************************************************************************
     getTooltipCoords()
     **************************************************************************/
    function getTooltipCoords($rtLim, $ltLim, $topLim, $curX, $curY) {
        // Determine if hover will fit in graph region
        if (($curX + 45) > $rtLim) { $labelX = $curX - (45 - ($rtLim - $curX)); } // We are past it on the right
        else if (($curX - 45) < $ltLim) { $labelX = $curX + (45 - ($curX - $ltLim)); } // We are past it on the left
        else { $labelX = $curX; } // We are in the green x-wise
        if (($curY - 30) < $topLim) { $labelY = $curY + 30; } // We are above y-wise
        else { $labelY = $curY - 5; } // We are in the green y-wise
        
        return array('x' => $labelX, 'y' => $labelY);
        }

    /**************************************************************************
     svgTemplate()
     
     Reads the SVG template file and replaces variables
     **************************************************************************/
    function svgTemplate($svgTemplatePath, $data) {
        $file = fopen($svgTemplatePath,"r");
        $svgFile = fread($file, filesize($svgTemplatePath));
        
        foreach ($data as $key => $value) {
            $svgFile = str_replace("%$key%", $value, $svgFile);	
            }
      
        // close out the file handler
        fclose($file);
      
        // return XML
        return $svgFile;
        }   
    }
?>