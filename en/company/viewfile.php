<?php
	define("NO_TYPE", true);
	require_once "../../lib/common.php";
	
	if(isset($_GET["id"]) == false){
		exit;
	}
	$con = my_db_connect();
	$sql = sprintf("select contents_savename, contents_dispname from t_catalog where catalog_id = %d", intval($_GET["id"]));
	$result = my_db_exec($con, $sql);
	$num = my_db_num_rows($result);
	if($num == 0){
		header_remove();
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	$arr = pg_fetch_array($result, 0);
	pg_free_result($result);
	pg_close($con);
	
	$base = sprintf("%s/dl/%s", OUTPUTPATH, $arr['contents_savename']);
	$file = realpath($base);
	if($file === false){
		header_remove();
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	if(DIRECTORY_SEPARATOR != "/"){
		$file = str_replace(DIRECTORY_SEPARATOR, "/", $file);
	}
	if(is_file($file) === false){
		header_remove();
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	if(preg_match("#" . $base . "#", $file) == 0){
		header_remove();
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	if(file_exists($file) == false){
		header_remove();
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	$len = filesize($file);
	$filename = $arr['contents_dispname'];
	if(preg_match("/(MSIE|TRIDENT|EDGE)/i",$_SERVER['HTTP_USER_AGENT'])){
	  if (strlen(rawurlencode($filename)) > 21 * 3 * 3) {
	    $filename = mb_convert_encoding($filename, "SJIS-win","UTF-8");
	    $filename = str_replace('#', '%23', $filename);
	  }else{
	    $filename = rawurlencode($filename);
	  }
	}
	header("Accept-Ranges: bytes");
	header("Content-Length: $len");
	// header("Connection: close");
	header("Content-type: application/pdf");
	// header("Content-disposition: attachment;filename=\"" . $filename . "\"");
//	header("Content-type: application/octet-stream");
//	header("Content-Disposition: attachment;filename=\"" . htmlspecialchars(addslashes(urldecode(mb_convert_encoding($arr['contents_dispname'],"sjis-win","utf-8")))) . "\"");
	readfile($file);
	exit;
?>
