<?php

/**************************************************************************
 graph_pastday.php
 
 This file is called from Fresh View's returned HTML as the source of the
 SVG graph. Appropriate variables are initialized here and applied to
 graph_fetch.php, which echos the SVG graph with the correct header.
 **************************************************************************/

// CHANGE PHP CONFIG TO FIX MAGIC_QUOTES ISSUE associated with XML syntax errors
ini_set("magic_quotes_gpc", "0");
ini_set("magic_quotes_runtime", "0");

// Mint variables
$serverDomain = $_SERVER['HTTP_HOST'];
$serverURI = $_SERVER['REQUEST_URI'];
$mintURL = $_SERVER['HTTP_REFERER'];
$mintURL = str_replace('?observe','',$mintURL);
$mintURI = 'index.php?custom=true&freshviewDisplay=day';
$mintAuth = '';

// Use CURL to fetch the graph
include_once('graph_fetch.php');
    
?>