<?php

class Pie
{

   var $pieHeight;
   var $pieWidth;
   var $arrayColors;
   var $textColor;
	var $textSize;
   var $imageWidth;
   var $imageHeight;
   var $backgroundColor;
	var $fontName;
	var $antiAlias;

	var $image;    
	var $arrayInput;		

   function Pie($width, $height, $input, $bgcolor=0xffffff) {
				
		$this->imageHeight = $height;
		$this->imageWidth = $width;
		$this->pieHeight = $height * .75;
		$this->pieWidth = $width * .75;
		$this->backgroundColor = $bgcolor;
		$this->arrayInput = $input;
		$this->fontName = "L_10646";
		$this->textSize = 6;
		$this->antiAlias = false;
		
		$this->image = imagecreatetruecolor($this->imageWidth,$this->imageHeight);

		$fill = imagefill($this->image,0,0,$this->backgroundColor);
		
		$this->textColor = imagecolorallocate($this->image, -50, -50, -50);
		
		
		$color1	= imagecolorallocate($this->image, 0xD7, 0xEA, 0xB3);
		$color2	= imagecolorallocate($this->image, 0xBD, 0xDC, 0x84);
		$color3	= imagecolorallocate($this->image, 0x7B, 0x9F, 0x53);
		$color4	= imagecolorallocate($this->image, 0x91, 0xB0, 0x68);
		$color5	= imagecolorallocate($this->image, 0xB7, 0xD9, 0x78);
		$color6	= imagecolorallocate($this->image, 0x9D, 0xCB, 0x48);
		$color7	= imagecolorallocate($this->image, 0x83, 0xAF, 0x31);
		$color8	= imagecolorallocate($this->image, 0xF0, 0xF7, 0xE2);
	
		$this->arrayColors = array($color1,$color2,$color3,$color4,$color5,$color6,$color7,$color8);

	}
		
		
	function drawSlices() {

		// ******************************************
		// PIE SLICES
		// ******************************************
		
		$lastAngle = 0;
		$i = 0;
		
		foreach ($this->arrayInput as $data) {
			
			if($data["size"] == 0) {
				$size = 1;
				$sizeLabel = "<1";
				
			} else {
				$size = $data["size"];
				$sizeLabel = $data["size"];
			}

			$sliceLabel = $data["label"]; // label
			$sliceSize = (36 * $size)/10; // %		
			$sliceStart = $lastAngle;
			$sliceEnd = $lastAngle + $sliceSize;	
					
			// Is this the last slice? Let's make sure it doesn't match the first slice's color.
				
			$sliceColor = $this->arrayColors[$i];		
					
			if($sliceLabel == $this->arrayInput[count($this->arrayInput)-1]["label"]) {
				$sliceColor = imagecolorallocate($this->image, 0x84, 0xB7, 0x8D);
			}
					
			imagefilledarc($this->image, $this->imageWidth/2, $this->imageHeight/2, $this->pieWidth, $this->pieHeight, $sliceStart, $sliceEnd, $sliceColor, IMG_ARC_PIE);	
			//imagefilledarc($this->image, $this->imageWidth/2, $this->imageHeight/2, $this->pieWidth+2, $this->pieHeight+2, $sliceStart, $sliceEnd, $white, IMG_ARC_EDGED | IMG_ARC_NOFILL);			

			$i++;
			
			if($i==count($this->arrayColors)) { 
				$i=0; 
			}
			
			$lastAngle = $sliceEnd;


		}
			
		
	}	
	

	
	
	function drawLabels() {
		
		$lastAngle = 0;
		
		foreach ($this->arrayInput as $data) {
			
			// ******************************************
			// PIE LABELS
			// ******************************************
			
			if($data["size"] == 0) {
				$size = 1;
				$sizeLabel = "<1";
				
			} else {
				$size = $data["size"];
				$sizeLabel = $data["size"];
			}

			$sliceSize = (36 * $size)/10; // %
			$sliceLabel = $data["label"]; // label
			
			$sliceStart = $lastAngle;
			$sliceEnd = $lastAngle + $sliceSize;
					
			$piecenter_x = $this->imageWidth/2;
			$piecenter_y = $this->imageHeight/2;
			
			//How close to center for label
			$cheat = $size >	20 ? 1.7 : 1.5;
 
			$radius = ($this->pieWidth/2)+10;
			$angle = floor(($sliceEnd - $sliceStart)/2);

			$pos_x = floor((cos(deg2rad($angle)+deg2rad($lastAngle))*$radius))+$piecenter_x;
			$pos_y = floor((sin(deg2rad($angle)+deg2rad($lastAngle))*$radius))+$piecenter_y;

			$data_x = floor((cos(deg2rad($angle)+deg2rad($lastAngle))*($radius/$cheat)))+$piecenter_x;
			$data_y = floor((sin(deg2rad($angle)+deg2rad($lastAngle))*($radius/$cheat)))+$piecenter_y;

			$this->imagettftextalign($this->image, $this->textSize, 0, $data_x,$data_y, $this->textColor, $this->fontName,$sizeLabel."%", "C","B");
			
			
			
			// ******************************************
			// ALIGNMENT OF TEXT LABELS
			
			$valign="T";
			$alignment="L";
			
			if($pos_x < $piecenter_x) {  
				$alignment="R"; // Label is on the LEFT side of the pie
			} else {
				$alignment="L"; // Label is on the RIGHT side of the pie
			}
		
			if($pos_y < $piecenter_y) {  
				$valign="B"; // Label is on the TOP side of the pie
			} else {
				$valign="T"; // Label is on the BOTTOM side of the pie
			}
			
			if($pos_y > ($piecenter_y + $radius - 5)) {
				$alignment="C"; // Label is on the VERY BOTTOM side of the pie
				$pos_y += 5;
			}
			
			if($pos_y < ($piecenter_y - $radius + 5)) {
				$alignment="C"; // Label is on the VERY TOP side of the pie
				$pos_y -= 5;
			}
		
			if($pos_x > ($piecenter_x + $radius - 5)) {
				$valign="T"; // Label is on the FAR RIGHT side of the pie
				
			}
			
			if($pos_x < ($piecenter_x - $radius + 5)) {
				$valign="M"; // Label is on the FAR LEFT side of the pie
				;
			}
		
		 	$this->imagettftextalign($this->image, $this->textSize, 0, $pos_x,$pos_y, $this->textColor, $this->fontName, $sliceLabel, $alignment,$valign);

			
			$lastAngle = $sliceEnd;

		}
	
	}
	
	
	
	function drawImage() {
		
		header('Content-type: image/png');
		imagepng($this->image);
		imagedestroy($this->image);
		
	}
	
	
	
	
	function imagettftextalign($image, $size, $angle, $x, $y, $color, $font, $text, $alignment='L',$valign='M') {

		$original_y = $y;

	   //check width of the text
	   $bbox = imagettfbbox ($size, $angle, $font, $text);
	   $textWidth = $bbox[2] - $bbox[0];
		$textHeight = $bbox[1] - $bbox[7];
	   switch ($alignment) {
	       case "R":
	           $x -= $textWidth;
	           break;
	       case "C":
	           $x -= $textWidth / 2;
	           break;
	   }

		switch ($valign) {
			case "M":
				$y -= $textHeight / 2;
				break;
			case "B":
				$y += $textHeight;
				break;
		}
/*
		if($textHeight > $this->textSize) {
			$y = $y - $textHeight;
		}
	*/	
		//$text .= "\n--".$textHeight . " ". $original_y . " " . $y . " " . $valign;
		//$text .= "\n--". $valign;
	   //write text
	   imagettftext ($image, $size, $angle, $x, $y, $color, $font, $text);

	}
	
		
		
		
}	
?>