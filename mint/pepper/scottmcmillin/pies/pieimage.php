<?php

require( 'class.pie.php' );


// QS Veriables
$entries = $_GET["t"];
$displayThreshold = isset($_GET["th"]) ? $_GET["th"]:0;


$input = Array();
$pieTotal = 0;
$otherLabel = "Other";
$otherSlice = 0;

for($i=0;$i<$entries;$i++) {  // data array for the graph
		
		$s = "c" . $i;
		$d = "d" . $i;

		$size = $_GET[$s];
		$label = $_GET[$d];
				
		if($label=="Crawler/Search Engine") $label = "Crawler";  // too long, so shorten


		if($size>=$displayThreshold) {
			$pieTotal += $size;
			$input[$i] = array("size"=>$size,"label"=>$label);
		
			}   else {  // Too Small
			
				$otherSlice += $size; //==0) ? 1 : $size;
			
			}

}

// Other should equal the rest of the pie unless it's already 100%

//$otherSlice = 100 - abs($pieTotal - $otherSlice);

if($otherSlice) {

	$input[] = array("size"=>$otherSlice,"label"=>$otherLabel);

}

//print_r($input);

// size of canvas
$width = 350;
$height = 270;

// instantiate pie object
$graph = new Pie($width, $height, $input);

// Pie properties
$graph->pieHeight = 220;
$graph->pieWidth = 220;

$graph->textSize = 8;

// Trying to fix font issues
putenv('GDFONTPATH=' . realpath('.'));
$graph->fontName = realpath('.') . "/L_10646.ttf";

$graph->textColor = imagecolorallocate($graph->image, 0x33, 0x33, 0x33);

// Pie public methods
$graph->drawSlices();
$graph->drawLabels();
$graph->drawImage();

?>