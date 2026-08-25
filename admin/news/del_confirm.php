<?php
	require_once('../../lib/common.php');
	
	if(!checkAdminSession())
	{
		header("Location: ../login.php");
		exit;
	}
	
	$group = 'admin_news_edit';
	$serial = fromSession($group, 'serial');

	if(!empty($_POST["delete"]) && isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true)
	{
		$con = my_db_connect();
		
		my_db_begin($con);
		
		$sql = 'delete from t_wnew where serial = $1';
		$result = my_db_query_params($con, $sql, array($serial));
		my_db_free_result($result);
		
		my_db_commit($con);
		
		if(!empty($file_only_savename))
			@unlink(OUTPUTPATH . '/wnew/' . $file_only_savename);
			
		
		clearSession($group);
		header("Location: del_finish.php");
		exit;
	}
	
	$con = my_db_connect();

	$sql = sprintf(
		"select " .
			"serial, " .
			"to_char(post_date, 'YYYY-MM-DD') as post_date, " .
			"to_char(post_date, 'FMHH24') as post_hour, " .
			"to_char(post_date, 'MI') as post_min, " .
			"title, category, " .
			"to_char(s_disp_date, 'YYYY-MM-DD') as s_disp_date, " .
			"to_char(s_disp_date, 'FMHH24') as s_disp_hour, " .
			"to_char(s_disp_date, 'MI') as s_disp_min, " .
			"to_char(e_disp_date, 'YYYY-MM-DD') as e_disp_date, " .
			"to_char(e_disp_date, 'FMHH24') as e_disp_hour, " .
			"to_char(e_disp_date, 'MI') as e_disp_min, " .
			"fixed_disp, " .
			"to_char(fixed_disp_date, 'YYYY-MM-DD') as fixed_disp_date, " .
			"format, " .
			"file_only_dispname, " .
			"file_only_savename, " .
			"link_only_url, " .
			"body " .
		"from t_wnew " .
		"where serial = %d",
		intval($serial));
	$result = my_db_exec($con, $sql);
	$num = my_db_num_rows($result);
	if($num == 0){
		my_db_free_result($result);
		clearSession($group);
		header("Location: list.php");
		exit;
	}
	$rec = my_db_fetch_array($result, 0);

	$tmpl = new Tmpl2( "templates/del_confirm.html" ) ;

	$tmpl->assign("token", my_make_token($_SERVER['SCRIPT_FILENAME']));
	
	$post_date = $rec['post_date'];
	$dt = date_parse($post_date);
	$tmpl->assign("post_year", $dt['year']);
	$tmpl->assign("post_month", $dt['month']);
	$tmpl->assign("post_day", $dt['day']);
	$tmpl->assign("post_hour", sprintf("%d", intval($rec['post_hour'])));
	$tmpl->assign("post_min", sprintf("%02d", intval($rec['post_min'])));
	$tmpl->assign("title", htmlspecialchars($rec['title']));
	
	$s_disp_date = $rec['s_disp_date'];
	if(!empty($s_disp_date)){
		$tmpl->assign_def("s_disp_date_exists");
		$dt = date_parse($s_disp_date);
		$tmpl->assign("s_disp_year", $dt['year']);
		$tmpl->assign("s_disp_month", $dt['month']);
		$tmpl->assign("s_disp_day", $dt['day']);
		$tmpl->assign("s_disp_hour", htmlspecialchars($rec['s_disp_hour']));
		$tmpl->assign("s_disp_min", htmlspecialchars($rec['s_disp_min']));
	}
	
	$e_disp_date = $rec['e_disp_date'];
	if(!empty($e_disp_date)){
		$tmpl->assign_def("e_disp_date_exists");
		$dt = date_parse($e_disp_date);
		$tmpl->assign("e_disp_year", $dt['year']);
		$tmpl->assign("e_disp_month", $dt['month']);
		$tmpl->assign("e_disp_day", $dt['day']);
		$tmpl->assign("e_disp_hour", htmlspecialchars($rec['e_disp_hour']));
		$tmpl->assign("e_disp_min", htmlspecialchars($rec['e_disp_min']));
	}

	$category = $rec['category'];
	$tmpl->assign("category", htmlspecialchars(getItemName(WNEW_CATEGORY, $category)));

	$fixed_disp = $rec['fixed_disp'];
	$tmpl->assign("fixed_disp", htmlspecialchars(getItemName(WNEW_FIXED, $fixed_disp)));
	$tmpl->assign_def("fixed_disp" . $fixed_disp);
	if($fixed_disp == "1"){
		$fixed_disp_date = $rec['fixed_disp_date'];
		$dt = date_parse($fixed_disp_date);
		$tmpl->assign("fixed_disp_year", $dt['year']);
		$tmpl->assign("fixed_disp_month", $dt['month']);
		$tmpl->assign("fixed_disp_day", $dt['day']);
	}
	
	$format = $rec['format'];
	$tmpl->assign_def("format" . $format);
	if($format == "1"){
		$tmpl->assign("body", $rec['body']);
	}
	else if($format == "2"){
		$tmpl->assign("file_only", htmlspecialchars($rec['file_only_dispname']));
	}
	else if($format == "3"){
		$tmpl->assign("link_only_url", htmlspecialchars($rec['link_only_url']));
	}
	
	$tmpl->flush();
	
	exit;
?>
