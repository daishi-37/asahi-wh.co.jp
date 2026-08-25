<?php
	require_once('../../lib/common.php');
	
	if(!checkAdminSession())
	{
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
	
	$error = '';
	if(!empty($_POST['confirm']) && isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true)
	{
		checkDateValue("post", "掲載日");
		$post_hour = fromPostData("post_hour");
		if($post_hour !== ''){
			$ival = intval($post_hour);
			if($ival < 0 || $ival > 23){
				$error .= "掲載時刻（時）の値が不正です。</br>";
			}
		}
		$post_min = fromPostData("post_min");
		if($post_min !== ''){
			$ival = intval($post_min);
			if($ival < 0 || $ival > 59){
				$error .= "掲載時刻（分）の値が不正です。</br>";
			}
		}

		$title = mb_convert_kana(trim($_POST["title"]), "KV", "UTF-8");
		if($title == ""){
			$error .= "タイトルを入力してください。</br>";
		}
		
		checkDateValue("s_disp", "表示期間（開始）");
		$s_disp_hour = fromPostData("s_disp_hour");
		if($s_disp_hour !== ''){
			$ival = intval($s_disp_hour);
			if($ival < 0 || $ival > 23){
				$error .= "表示期間（開始）の値が不正です。</br>";
			}
		}
		$s_disp_min = fromPostData("s_disp_min");
		if($s_disp_min !== ''){
			$ival = intval($s_disp_min);
			if($ival < 0 || $ival > 59){
				$error .= "表示期間（開始）の値が不正です。</br>";
			}
		}
		
		checkDateValue("e_disp", "表示期間（終了）", false);
		$_disp_hour = fromPostData("_disp_hour");
		if($_disp_hour !== ''){
			$ival = intval($_disp_hour);
			if($ival < 0 || $ival > 23){
				$error .= "表示期間（終了）の値が不正です。</br>";
			}
		}
		$_disp_min = fromPostData("_disp_min");
		if($_disp_min !== ''){
			$ival = intval($_disp_min);
			if($ival < 0 || $ival > 59){
				$error .= "表示期間（終了）の値が不正です。</br>";
			}
		}
		
		if(!empty($s_disp_date) && !empty($e_disp_date)){
			if($s_disp_date > $e_disp_date){
				$error .= "表示期間の値が不正です。</br>";
			}
		}
		else if(empty($s_disp_date) && !empty($e_disp_date)){
			$error .= "表示期間（開始）を入力してください。</br>";
		}

		$category = fromPostData("category");
		$arr_category = explode(";", $category);
		if(count($arr_category) != 2){
			$error .= "カテゴリーを選択してください。</br>";
		}
		elseif(preg_match("/^[0-9]+$/", $arr_category[0]) != 1){
			$error .= "カテゴリーが不正です。</br>";
		}

		checkDateValue("fixed_disp", "固定表示の掲載期限", false);
		
		$fixed_disp = fromPostData("fixed_disp");
		$arr_fixed_disp = explode(";", $fixed_disp);
		if(count($arr_fixed_disp) != 2){
			$error .= "固定表示の値が不正です。</br>";
			$fixed_disp = "0";
		}
		else if($arr_fixed_disp[0] != "0" && $arr_fixed_disp[0] != "1"){
			$error .= "固定表示の値が不正です。</br>";
			$fixed_disp = "0";
		}
		else if($arr_fixed_disp[0] == "1"){
			if(empty($fixed_disp_date)){
				$error .= "固定表示の掲載期限を入力してください。</br>";
			}
		}
		
		$file_only_savename = fromPostData('file_only_savename');
		$file_only_dispname = fromPostData('file_only_dispname');
		$link_only_url = mb_convert_kana(trim(fromPostData('link_only_url')), "KV", "UTF-8");
		$format = fromPostData("format");
		$arr_format = explode(";", $format);
		if(count($arr_format) != 2){
			$error .= "表示形態を選択してください。</br>";
		}
		else if($arr_format[0] == "2"){
			if(empty($file_only_savename)){
				$error .= "表示形態で本文無し（添付ファイル表示）を選択する場合は、添付ファイルを指定する必要があります。</br>";
			}
		}
		else if($arr_format[0] == "3"){
			if($link_only_url == ""){
				$error .= "表示形態で本文無し（リンクのみ）を選択する場合は、URLを入力する必要があります。</br>";
			}
			else if(preg_match('#^(http|https|ftp)://.+#', $link_only_url) == 0){
				$error .= "本文無し（リンクのみ）で指定されたURLが不正です。</br>";
			}
		}
		else if($arr_format[0] != "1"){
			$error .= "表示形態を選択してください。</br>";
			$format = "";
		}
		
		$body = mb_convert_kana(trim($_POST["body"]), "KV", "UTF-8");
		if($arr_format[0] == "1" && $body == ""){
			$error .= "表示形態で本文ありを選択する場合は、本文を入力する必要があります。</br>";
		}
		
		foreach($arr_itemall as $item){
			setSessionFromVal('admin_news', $item, $$item);
		}
		
		setSessionFromVal('admin_news', 'error', $error);
		
		if(empty($error))
		{
			$all_text = "";
			foreach($arr_itemall as $fldname)
			{
				$all_text .= $$fldname;
			}
			$all_text .= md5("magic");
			setSessionFromVal('admin_news', 'checksum', md5($all_text));
			header("Location: confirm.php");
			exit;
		}
		header("Location: new.php");
		exit;
	}
	else if(isset($_SESSION['admin_news']))
	{
		$error = fromSession('admin_news', 'error');
		
		foreach($arr_itemall as $item){
			$$item = fromSession('admin_news', $item);
		}
	}
	else
	{
		clearSession('admin_news');
		$error = '';
		
		foreach($arr_itemall as $item){
			$$item = "";
		}
		
		$today = time();
		$post_date = date("Y-m-d", $today);
		$post_hour = "0";
		$post_min = "00";
		$s_disp_date = date("Y-m-d", $today);
		$s_disp_hour = "0";
		$s_disp_min = "00";
		
		$fixed_disp = "0";
	}
	
	$tmpl = new Tmpl2( 'templates/new.html' );

	$tmpl->assign("token", my_make_token($_SERVER['SCRIPT_FILENAME']));
	
	if(!empty($error))
	{
		$tmpl->assign_def('error');
		$tmpl->assign('error', $error);
	}
	
	$tmpl->assign("post_date", htmlspecialchars($post_date));
	$tmpl->assign("post_hour_selected" . $post_hour, " selected");
	$tmpl->assign("post_min_selected" . $post_min, " selected");
	
	$tmpl->assign("title", htmlspecialchars($title));
	
	$tmpl->assign("s_disp_date", htmlspecialchars($s_disp_date));
	$tmpl->assign("s_disp_hour_selected" . $s_disp_hour, " selected");
	$tmpl->assign("s_disp_min_selected" . $s_disp_min, " selected");
	
	$tmpl->assign("e_disp_date", htmlspecialchars($e_disp_date));
	$tmpl->assign("e_disp_hour_selected" . $e_disp_hour, " selected");
	$tmpl->assign("e_disp_min_selected" . $e_disp_min, " selected");

	$arr = explode(";", $category);
	$tmpl->assign("category_selected" . $arr[0], " selected");
	
	$arr = explode(";", $fixed_disp);
	$tmpl->assign("fixed_disp_checked" . $arr[0], " checked");
	$tmpl->assign("fixed_disp_date", htmlspecialchars($fixed_disp_date));
	
	if($format != ""){
		$arr = explode(";", $format);
		$tmpl->assign("format_checked" . $arr[0], " checked");
	}
	
	$tmpl->assign('file_only_savename', htmlspecialchars($file_only_savename));
	$tmpl->assign('file_only_dispname', htmlspecialchars($file_only_dispname));
	$tmpl->assign("link_only_url", htmlspecialchars($link_only_url));
	$tmpl->assign("body", htmlspecialchars($body));
	
	$tmpl->flush();
	
	exit;
?>