<?php
	require_once('../../lib/common.php');
	
	if(!checkAdminSession())
	{
		header("Location: ../login.php");
		exit;
	}

	clearSession('admin_news_edit');

	$con = my_db_connect();
	
	$sql = "select serial, " .
		"to_char(post_date, 'YYYY') as post_year, " .
		"to_char(post_date, 'MM') as post_month, " .
		"to_char(post_date, 'DD') as post_day, " .
		"to_char(post_date, 'FMHH24') as post_hour, " .
		"to_char(post_date, 'MI') as post_min, " .
		"title " .
		"from t_wnew where lang = 'en' " .
		"order by post_date desc, serial desc";
	$result = my_db_exec($con, $sql);
	
	$tmpl = new Tmpl2( "templates/list.html" ) ;
	
	$tmpl->loopset("loop");
	while(($arr = my_db_fetch_array($result)) !== false)
	{
		$tmpl->assign("post_year", htmlspecialchars($arr["post_year"]));
		$tmpl->assign("post_month", htmlspecialchars($arr["post_month"]));
		$tmpl->assign("post_day", htmlspecialchars($arr["post_day"]));
		$tmpl->assign("post_hour", htmlspecialchars($arr["post_hour"]));
		$tmpl->assign("post_min", htmlspecialchars($arr["post_min"]));
		$tmpl->assign("serial", htmlspecialchars($arr["serial"]));
		$tmpl->assign("title", htmlspecialchars($arr["title"]));
		$tmpl->loopnext("loop");
	}
	$tmpl->loopend("loop");
	
	$tmpl->flush();
	
	exit;
?>
