<?php

/**************************************************************************
 graph_fetch.php
 
 This file is called from various graph_xxx.php files to return the source
 of the appropriate SVG graph. It places a GET call into the Mint script
 to connect to the Fresh View Pepper to retrieve the Past Day SVG graph
 data. It is echoed with the proper MIME type.
 **************************************************************************/

foreach ($_COOKIE as $key => $value) {
    if ($key == 'MintAuth') {
        $mintAuth = $value;
        }
    }

// Fetch the graph
curlGateway($mintURL, $mintURI, $mintAuth, $serverDomain);

// Is there cURL functionality?
function curlGateway($mintURL, $mintURI, $mintAuth, $serverDomain) {
    if (function_exists('curl_init')) {
        $data = curlFetch($mintURL, $mintURI, $mintAuth, $serverDomain);
        
        if ($data != '') {
            // Return SVG graph
            header("Content-type: image/svg+xml");
            echo $data;
            }
        else {
            // returned empty, try next routine
            fileGateway($mintURL, $mintURI, $mintAuth, $serverDomain);
            }
        }
    else {
        // no cURL library, try next routine
        fileGateway($mintURL, $mintURI, $mintAuth, $serverDomain);
        }
    }

// Is there file_get_contents() functionality
function fileGateway($mintURL, $mintURI, $mintAuth, $serverDomain) {
    if (!ini_get('allow_url_fopen') && version_compare(phpversion(),'5.0.0') >= 0) {
        $data = fileFetch($mintURL, $mintURI, $mintAuth, $serverDomain);
        
        if ($data != '') {
            // Return SVG graph
            header("Content-type: image/svg+xml");
            echo $data;
            }
        else {
            // returned empty, try last routine
            socketGateway($mintURL, $mintURI, $mintAuth, $serverDomain);
            }
        }
    else {
        // file_get_contents() error, try last routine
        socketGateway($mintURL, $mintURI, $mintAuth, $serverDomain);
        }
    }

function socketGateway($mintURL, $mintURI, $mintAuth, $serverDomain) {
    if (ini_get('allow_url_fopen')) {
        $data = socketFetch($mintURL, $mintURI, $mintAuth, $serverDomain);
        
        if ($data != '') {
            // Return SVG graph
            header("Content-type: image/svg+xml");
            echo $data;
            }
        else {
            // COMPLETE FETCH FAILURE
            // returned empty
            $errMsg = 'Fresh View v1.11 received an error attempting to retrieve the</text>
    <text x="120" y="112">SVG graph:</text>
    <rect x="130" y="120" width="5" height="5" fill="#666666" stroke="none" />
    <text x="138" y="126">No data was returned from fsockopen</text>
    <text x="120" y="154">For more information, please contact the author of <tspan xlink:href="http://www.sensoryoutput.com/projects/freshview/">Fresh View</tspan> or</text>
    <text x="120" y="168">visit the <tspan xlink:href="http://www.haveamint.com/forums/">Mint forums</tspan>.';
            include_once('graph_error.php');
            exit;
            }
        }
    else {
        // COMPLETE FETCH FAILURE
        // cURL library or file_get_contents() or fsockopen() not installed/allowed
        $errMsg = 'Fresh View v1.11 requires a PHP installation with support for one</text>
    <text x="120" y="112">of the following:</text>
    <rect x="130" y="120" width="5" height="5" fill="#666666" stroke="none" />
    <text x="138" y="126"><tspan xlink:href="http://www.php.net/curl">cURL library</tspan>, PHP 4.0.2+</text>
    <rect x="130" y="134" width="5" height="5" fill="#666666" stroke="none" />
    <text x="138" y="140"><tspan xlink:href="http://www.php.net/manual/en/function.file-get-contents.php">file_get_contents() function</tspan>, PHP_INI variable</text>
    <text x="138" y="154"><tspan style="font-family: Monaco, Courier, monospace">allow-url-fopen</tspan> set to true, PHP 5.0.0+</text>
    <rect x="130" y="162" width="5" height="5" fill="#666666" stroke="none" />
    <text x="138" y="168"><tspan xlink:href="http://www.php.net/manual/en/function.fsockopen.php">fsockopen() function</tspan>, PHP 3.0.0+</text>
    <text x="120" y="196">Please update PHP on the server used by Mint, or revert to a</text>
    <text x="120" y="210">previous version of <tspan xlink:href="http://www.sensoryoutput.com/projects/freshview/">Fresh View</tspan>.';
        include_once('graph_error.php'); 
        exit;
        }
    }

// Uses cURL library, which is required
function curlFetch($mintURL, $mintURI, $mintAuth, $serverDomain) {
    // Initiate a connection
    $x = curl_init($mintURL . $mintURI);
    curl_setopt($x, CURLOPT_HEADER, 0);
    curl_setopt($x, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($x, CURLOPT_HTTPHEADER, array("Accept: */*","Accept-Language: en"));
    curl_setopt($x, CURLOPT_ENCODING, "gzip, deflate");
    curl_setopt($x, CURLOPT_HTTPHEADER, array("Cookie: MintIgnore=true; MintAuth=$mintAuth"));
    curl_setopt($x, CURLOPT_REFERER, $mintURL);
    curl_setopt($x, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($x);
    curl_close($x);
    
    return $data;
    }

// Uses file_get_contents(), which requires PHP 5+
function fileFetch($mintURL, $mintURI, $mintAuth, $serverDomain) {
    // Compose HTTP request header
    $header  = "Accept: */*\r\n";
    $header .= "Accept-Language: en\r\n";
    $header .= "Cookie: MintIgnore=true; MintAuth=$mintAuth\r\n";
    $header .= "Referer: $mintURL\r\n";
    $header .= "Connection: keep-alive\r\n";
    $header .= "Host: $serverDomain\r\n";
        
    // Define context options for HTTP request
    $opts['http']['method'] = 'GET';
    $opts['http']['header'] = $header;
    
    // Create stream context
    $context = stream_context_create($opts);

    // GET request and response
    if ($data = file_get_contents($mintURL . $mintURI, false, $context)) {
        return $data;
        }
    else {
        return '';
        }
    }

// Uses fsockopen()
function socketFetch($mintURL, $mintURI, $mintAuth, $serverDomain) {
    $fp = fsockopen(str_replace('http://','',$mintURL . $mintURI), 80, $errNo, $errStr, 5);

    if (!$fp) {
        // COMPLETE FETCH FAILURE
        // Problem opening socket
        $errMsg = 'Fresh View v1.11 received an error attempting to retrieve the</text>
    <text x="120" y="112">SVG graph via fsockopen():</text>
    <rect x="130" y="120" width="5" height="5" fill="#666666" stroke="none" />
    <text x="138" y="126">' . $errStr . ' (' . $errNo . ')</text>
    <text x="120" y="154">For more information, please contact the author of <tspan xlink:href="http://www.sensoryoutput.com/projects/freshview/">Fresh View</tspan></text>
    <text x="120" y="168">or visit the <tspan xlink:href="http://www.haveamint.com/forums/">Mint forums</tspan>.';
        include_once('graph_error.php');
        exit;
        }
    else {
        $header = "GET " . $mintURI . " HTTP/1.1\r\n";
        $header .= "Accept: */*\r\n";
        $header .= "Accept-Language: en\r\n";
        $header .= "Cookie: MintIgnore=true; MintAuth=$mintAuth\r\n";
        $header .= "Referer: $mintURL\r\n";
        $header .= "Connection: Close\r\n";
        $header .= "Host: $serverDomain\r\n";
    
        $data = '';
        fwrite($fp, $header);
        while (!feof($fp)) {
            $data .= fgets($fp);
            }
        fclose($fp);
        
        return $data;
        }
    }

?>