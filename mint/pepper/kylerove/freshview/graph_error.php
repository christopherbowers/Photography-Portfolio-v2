<?php

/**************************************************************************
 graph_error.php
 
 This file is called from Fresh View's returned HTML as the source of the
 SVG error.
 **************************************************************************/

// Mint error setup
if (!$errMsg) {
    $errMsg = 'Unknown error occurred.';
    }

$svg['err_msg'] = $errMsg;

$svgTemplatePath = 'templates/error_template.svg';
$svgFile = svgTemplate($svgTemplatePath, $svg);  
header("Content-type: image/svg+xml");
echo $svgFile;

/**************************************************************************
 svgTemplate()
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

?>