<?php
	require_once("../../lib/common.php");
	
	if(checkAdminSession() == false){
		header("Location: ../login.php");
		exit;
	}
	
	$arr_itemall = array("post_date","post_hour","post_min",
		"title",
		"s_disp_date", "s_disp_hour", "s_disp_min",
		"e_disp_date", "e_disp_hour", "e_disp_min",
		"category",
		"fixed_disp", "fixed_disp_date",
		"format","file_only_savename","file_only_dispname","link_only_url","body");
	
	$group = 'admin_news_edit';
	$all_text = "";
	foreach($arr_itemall as $fldname)
	{
		$all_text .= fromSession($group, $fldname);
	}
	$all_text .= md5("magic");
	if(strcmp(fromSession($group, 'checksum'), md5($all_text)) != 0)
	{
		clearSession($group);
		header("Location: list.php");
		exit;
	}
	
	$post_date = fromSession($group,'post_date');
	$post_hour = fromSession($group, 'post_hour');
	$post_min = fromSession($group, 'post_min');
	
	$title = fromSession($group, 'title');
	
	$s_disp_date = fromSession($group,'s_disp_date');
	$s_disp_hour = fromSession($group, 's_disp_hour');
	$s_disp_min = fromSession($group, 's_disp_min');
	
	$e_disp_date = fromSession($group,'e_disp_date');
	$e_disp_hour = fromSession($group, 'e_disp_hour');
	$e_disp_min = fromSession($group, 'e_disp_min');

	$category = fromSession($group,'category');
	$arr = explode(";", $category);
	$category_val = intval($arr[0]);
	$category_text = $arr[1];

	$fixed_disp = fromSession($group, 'fixed_disp');
	$arr = explode(";", $fixed_disp);
	$fixed_disp_val = intval($arr[0]);
	$fixed_disp_text = $arr[1];
	$fixed_disp_date = fromSession($group,'fixed_disp_date');
	
	$format = fromSession($group, 'format');
	$arr = explode(";", $format);
	$format_val = intval($arr[0]);
	$format_text = $arr[1];
	
	$body = fromSession($group, 'body');
	$file_only_savename = fromSession($group, 'file_only_savename');
	$file_only_dispname = fromSession($group, 'file_only_dispname');
	$link_only_url = fromSession($group, 'link_only_url');
	
	if(!empty($_POST["regist"]) && isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true)
	{
		//ここで登録
		$con = my_db_connect();
		
		my_db_begin($con);
		
		$serial = fromSession($group, 'serial');
		if($format_val == "1"){
			$file_only_dispname = '';
			$file_only = '';
		}
		else if($format_val == "2"){
			if(!empty($file_only_savename)){
				$file_only_dispname = fromSession($group, 'file_only_dispname');
				$file_only = sprintf("%d.file", $serial);
			}
			else{
				$file_only_dispname = fromSession($group, 'file_only_oldname');
				$file_only = fromSession($group, 'file_only_oldfile');
			}
		}
		else if($format_val == "3"){
			$file_only_dispname = '';
			$file_only = '';
		}

		if($post_hour == ''){
			$post_hour = '0';
		}
		if($post_min == ''){
			$post_min = '0';
		}
		$post_date = sprintf("%s %02d:%02d:00", 
			$post_date,
			$post_hour, $post_min);
			
		if(!empty($s_disp_date)){
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
		else{
			$s_disp_date = null;
		}
		
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
		else{
			$e_disp_date = null;
		}
		
		if($fixed_disp_val == 1 && !empty($fixed_disp_date))
		{
			$fixed_disp_date = sprintf("%s 00:00:00", 
				$fixed_disp_date);
		}
		else{
			$fixed_disp_date = null;
		}
		
		$sql = 
			'update t_wnew set ' .
				'post_date = $1, ' .
				'title = $2, ' .
				's_disp_date = $3, ' .
				'e_disp_date = $4, ' .
				'fixed_disp = $5, ' .
				'fixed_disp_date = $6, ' .
				'format = $7, ' .
				'file_only_dispname = $8, ' .
				'file_only_savename = $9, ' .
				'link_only_url = $10, ' .
				'body = $11, ' .
				'category = $12, ' .
				'udate = LOCALTIMESTAMP ' .
			'where serial = $13';
		$params = array(
			$post_date,
			$title,
			$s_disp_date,
			$e_disp_date,
			$fixed_disp_val,
			$fixed_disp_date,
			$format_val,
			$file_only_dispname,
			$file_only,
			$link_only_url,
			$body,
			$category_val,
			$serial);
		$result = my_db_query_params($con, $sql, $params);
		my_db_free_result($result);
		
		if($format_val != "2"){
			
			if(!empty($file_only_savename)){
				$filename = sprintf("%s/%s", TEMPPATH, $file_only_savename);
				if(file_exists($filename)){
					@unlink($filename);
				}
			}
			
			$file_only_oldfile = fromSession($group, 'file_only_oldfile');
			if(!empty($file_only_oldfile)){
				$filename = sprintf("%s/wnew/%s", OUTPUTPATH, $file_only_oldfile);
				if(file_exists($filename)){
					@unlink($filename);
				}
			}
		}
		else if(!empty($file_only_savename)){
			$new_filename = sprintf("%s/wnew/%s", OUTPUTPATH, $file_only);
			if(file_exists($new_filename)){
				@unlink($new_filename);
			}
			$tmp_filename = sprintf("%s/%s", TEMPPATH, $file_only_savename);
			if(file_exists($tmp_filename)){
				rename($tmp_filename, $new_filename);
			}
		}
		
		my_db_commit($con);
		my_db_close($con);
		
		clearSession($group);
		header("Location: edit_finish.php");
		exit;
	}
	
	$tmpl = new Tmpl2( "templates/edit_confirm.html" ) ;

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
		if($file_only_savename != ""){
			$tmpl->assign("file_only", htmlspecialchars($file_only_dispname));
		}
		else{
			$file_only_oldname = fromSession($group, 'file_only_oldname');
			$tmpl->assign("file_only", htmlspecialchars($file_only_oldname));
		}
	}
	else if($format_val == "3"){
		$tmpl->assign("link_only_url", htmlspecialchars($link_only_url));
	}
	
	$tmpl->flush();
?>
