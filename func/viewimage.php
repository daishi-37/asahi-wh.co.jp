<?php
	define("NO_TYPE", true);
	require_once "../lib/common.php";
	
	$base = OUTPUTPATH . "/" . $_GET["d"] . "/";
	$file = realpath($base . $_GET["f"]);
	if($file === false){
		exit;
	}
	if(DIRECTORY_SEPARATOR != "/"){
		$file = str_replace(DIRECTORY_SEPARATOR, "/", $file);
	}
	if(is_file($file) === false){
		exit;
	}
	if(preg_match("#" . $base . "#", $file) == 0){
		exit;
	}
	if(file_exists($file) == false){
		exit;
	}
	$arr = getimagesize($file);
	header("Content-type: " . $arr["mime"]);
	readfile($file);
	exit;
?>
