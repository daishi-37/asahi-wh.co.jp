<?php
	require_once "../../lib/common.php";
	
	if(isset($_GET["id"]) == false){
		header_remove();
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	
	$con = my_db_connect();
	$sql = sprintf(
		"select " .
			"serial, " .
			"to_char(post_date, 'YYYY.FMMM.FMDD') as post_date, " .
			"category, " .
			"title, " .
			"body, " .
			"format, " .
			"file_only_savename, " .
			"file_only_dispname, " .
			"link_only_url " .
		"from t_wnew " .
		"where serial = %d", intval($_GET["id"]));
	$result = my_db_exec($con, $sql);
	$num = my_db_num_rows($result);
	if($num == 0){
		my_db_free_result($result);
		my_db_close($con);
		header_remove();
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	$arr = my_db_fetch_array($result);
	my_db_free_result($result);
	my_db_close($con);
	
	if($arr["format"] == 2){
		$base = sprintf("%s/wnew/%s", OUTPUTPATH, $arr['file_only_savename']);
		$file = realpath($base);
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
		$len = filesize($file);
		$filename = $arr['file_only_dispname'];
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
		header("Connection: close");
		header("Content-type: application/octet-stream");
		header("Content-disposition: attachment; filename=\"" . $filename . "\"");
//		header("Content-type: application/octet-stream");
//		header("Content-Disposition: attachment;filename=\"" . htmlspecialchars(addslashes(urldecode(mb_convert_encoding($arr['file_only_dispname'],"sjis-win","utf-8")))) . "\"");
		readfile($file);
		exit;
	}
	else if($arr["format"] == 3){
		header("Location: " . $arr['link_only_url']);
		exit;
	}
	$tmpl = new Tmpl2("memory");
	$tmpl->setmem(parseSSI(file_get_contents("templates/detail.html"), dirname(__FILE__)));
	
	$tmpl->assign("category", htmlspecialchars(getItemName(WNEW_CATEGORY_EN, $arr['category'])));
	$tmpl->assign("post_date", htmlspecialchars($arr['post_date']));
	$tmpl->assign("title", htmlspecialchars($arr['title']));
	$tmpl->assign("body", $arr['body']);
	
	$tmpl->flush();
?>
