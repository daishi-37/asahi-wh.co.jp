<?php
	require_once("../../lib/common.php");
	
	if(checkAdminSession() == false){
		header("Location: ../login.php");
		exit;
	}
	
	$arr_itemall = array(
		"post_date", "post_hour", "post_min",
		"title",
		"s_disp_date", "s_disp_hour", "s_disp_min",
		"e_disp_date", "e_disp_hour", "e_disp_min",
		"category",
		"fixed_disp", "fixed_disp_date",
		"format", "file_only_savename", "file_only_dispname", "link_only_url", "body"
	);
	
	$group = 'admin_news';

	$all_text = "";
	foreach($arr_itemall as $fldname)
	{
		$all_text .= fromSession($group, $fldname);
	}
	$all_text .= md5("magic");
	if(strcmp(fromSession($group,'checksum'), md5($all_text)) != 0)
	{
		clearSession($group);
		header("Location: new.php");
		exit;
	}
	
	$post_date = fromSession($group,'post_date');
	$post_hour = fromSession($group,'post_hour');
	$post_min = fromSession($group,'post_min');
		
	$title = fromSession($group,'title');
	
	$s_disp_date = fromSession($group,'s_disp_date');
	$s_disp_hour = fromSession($group,'s_disp_hour');
	$s_disp_min = fromSession($group,'s_disp_min');
	
	$e_disp_date = fromSession($group,'e_disp_date');
	$e_disp_hour = fromSession($group,'e_disp_hour');
	$e_disp_min = fromSession($group,'e_disp_min');

	$category = fromSession($group,'category');
	$arr = explode(";", $category);
	$category_val = intval($arr[0]);
	$category_text = $arr[1];

	$fixed_disp = fromSession($group,'fixed_disp');
	$arr = explode(";", $fixed_disp);
	$fixed_disp_val = intval($arr[0]);
	$fixed_disp_text = $arr[1];
	$fixed_disp_date = fromSession($group,'fixed_disp_date');
	
	$format = fromSession($group,'format');
	$arr = explode(";", $format);
	$format_val = intval($arr[0]);
	$format_text = $arr[1];
	
	$body = fromSession($group,'body');
	$file_only_savename = fromSession($group,'file_only_savename');
	$file_only_dispname = fromSession($group,'file_only_dispname');
	$link_only_url = fromSession($group,'link_only_url');
	
	if(!empty($_POST["regist"]) && isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true)
	{
		//ここで登録
		$con = my_db_connect();
		
		my_db_begin($con);
		
		$sql = "select nextval('t_wnew_serial_seq')";
		$result = my_db_exec($con, $sql);
		$serial = my_db_fetch_result($result, 0, 0);
		my_db_free_result($result);
		
		if($format_val == 2 && !empty($file_only_savename))
			$file_only_file = sprintf("%d.file", $serial);
		else
			$file_only_file = '';
		
		if($post_hour == ''){
			$post_hour = '0';
		}
		if($post_min == ''){
			$post_min = '0';
		}
		$post_date = sprintf("%s %02d:%02d:00", 
			$post_date,
			$post_hour, $post_min);
			
		if(!empty($s_disp_date))
		{
			if($s_disp_hour == ''){
				$s_disp_hour = '0';
			}
			if($s_disp_min == ''){
				$s_disp_min = '0';
			}
				$s_disp_date = sprintf("%s %02d:%02d:00", 
				$s_disp_date,
				$s_disp_hour, $s_disp_min);
		}
		else
			$s_disp_date = null;
		
		if(!empty($e_disp_date))
		{
			if($e_disp_hour == ''){
				$e_disp_hour = '0';
			}
			if($e_disp_min == ''){
				$e_disp_min = '0';
			}
			$e_disp_date = sprintf("%s %02d:%02d:00", 
				$e_disp_date,
				$e_disp_hour, $e_disp_min);
		}
		else
			$e_disp_date = null;
			
		if($fixed_disp_val == 1 && !empty($fixed_disp_date))
		{
			$fixed_disp_date = sprintf("%s 00:00:00", 
				$fixed_disp_date);
		}
		else{
			$fixed_disp_date = null;
		}
		
		$sql = 'insert into t_wnew (' .
				'serial, ' .
				'post_date, title, ' .
				's_disp_date, e_disp_date, ' .
				'fixed_disp, fixed_disp_date, ' .
				'format, ' .
				'file_only_dispname, file_only_savename, link_only_url, ' .
				'body, target, category, lang) ' .
				'values ('.
				'$1, ' .
				'$2, $3, ' .
				'$4, $5, ' .
				'$6, $7, ' .
				'$8, ' .
				'$9, $10, $11, ' .
				'$12, ' .
				'$13, $14, $15)';
		$params = array(
			$serial,
			$post_date, $title,
			$s_disp_date, $e_disp_date,
			$fixed_disp_val, $fixed_disp_date,
			$format_val,
			$file_only_dispname, $file_only_file, $link_only_url,
			$body, 0, $category_val, 'ja');
		$result = my_db_query_params($con, $sql, $params);
		my_db_free_result($result);
		
		$filename = sprintf("%s/wnew/%d.file", OUTPUTPATH, $serial);
		if(file_exists($filename))
			@unlink($filename);
		if(!empty($file_only_file))
			@rename(TEMPPATH . "/" . $file_only_savename, $filename);
		
		my_db_commit($con);
		my_db_close($con);
		
		clearSession('admin_news');
		header("Location: finish.php");
		exit;
	}
	
	$tmpl = new Tmpl2( "templates/confirm.html" ) ;

	$tmpl->assign("token", my_make_token($_SERVER['SCRIPT_FILENAME']));
	
	$dt = date_parse($post_date);
	$tmpl->assign("post_year", $dt['year']);
	$tmpl->assign("post_month", $dt['month']);
	$tmpl->assign("post_day", $dt['day']);
	$tmpl->assign("post_hour", sprintf("%d", intval($post_hour)));
	$tmpl->assign("post_min", sprintf("%02d", intval($post_min)));
	$tmpl->assign("title", htmlspecialchars($title));
	if(!empty($s_disp_date)){
		$tmpl->assign_def("s_disp_date_exists");
		$dt = date_parse($s_disp_date);
		$tmpl->assign("s_disp_year", $dt['year']);
		$tmpl->assign("s_disp_month", $dt['month']);
		$tmpl->assign("s_disp_day", $dt['day']);
		$tmpl->assign("s_disp_hour", sprintf("%d", intval($s_disp_hour)));
		$tmpl->assign("s_disp_min", sprintf("%02d", intval($s_disp_min)));
	}
	if(!empty($e_disp_date)){
		$tmpl->assign_def("e_disp_date_exists");
		$dt = date_parse($e_disp_date);
		$tmpl->assign("e_disp_year", $dt['year']);
		$tmpl->assign("e_disp_month", $dt['month']);
		$tmpl->assign("e_disp_day", $dt['day']);
		$tmpl->assign("e_disp_hour", sprintf("%d", intval($e_disp_hour)));
		$tmpl->assign("e_disp_min", sprintf("%02d", intval($e_disp_min)));
	}
	$tmpl->assign("category", htmlspecialchars($category_text));
	$tmpl->assign("fixed_disp", htmlspecialchars($fixed_disp_text));
	$tmpl->assign_def("fixed_disp" . $fixed_disp_val);
	if($fixed_disp_val == "1"){
		$dt = date_parse($fixed_disp_date);
		$tmpl->assign("fixed_disp_year", $dt['year']);
		$tmpl->assign("fixed_disp_month", $dt['month']);
		$tmpl->assign("fixed_disp_day", $dt['day']);
	}
	$tmpl->assign_def("format" . $format_val);
	if($format_val == "1"){
		$tmpl->assign("body", $body);
	}
	else if($format_val == "2"){
		$tmpl->assign("file_only", htmlspecialchars($file_only_dispname));
	}
	else if($format_val == "3"){
		$tmpl->assign("link_only_url", htmlspecialchars($link_only_url));
	}
	
	$tmpl->flush();
?>
