<?php
	require_once("../../lib/common.php");
	
	if(checkAdminSession() == false){
		header("Location: ../login.php");
		exit;
	}
	
	$con = my_db_connect();
	
	$error = "";
	$arr_itemall = array("post_date","post_hour","post_min",
		"title",
		"s_disp_date","s_disp_hour","s_disp_min",
		"e_disp_date","e_disp_hour","e_disp_min",
		"category",
		"fixed_disp","fixed_disp_date",
		"format","file_only_savename","file_only_dispname","link_only_url","body");
	
	
	$group = 'admin_news_edit';
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
		$e_disp_hour = fromPostData("e_disp_hour");
		if($e_disp_hour !== ''){
			$ival = intval($e_disp_hour);
			if($ival < 0 || $ival > 23){
				$error .= "表示期間（終了）の値が不正です。</br>";
			}
		}
		$e_disp_min = fromPostData("e_disp_min");
		if($e_disp_min !== ''){
			$ival = intval($e_disp_min);
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
		
		$fixed_disp = $_POST["fixed_disp"];
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
			if(empty($file_only_savename) && fromSession($group, 'file_only_oldfile') == ''){
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
		
		$data = array();
		foreach($arr_itemall as $item){
			$data[$item] = $$item;
		}
		$data['serial'] = fromSession($group, 'serial');
		
		$data['file_only_oldname'] = fromSession($group, 'file_only_oldname');
		$data['file_only_oldfile'] = fromSession($group, 'file_only_oldfile');
		
		$_SESSION[$group] = $data;
		
		setSessionFromVal($group, 'error', $error);
		
		if($error == ""){
			$all_text = "";
			foreach ($arr_itemall as $fldname) {
				$all_text .= fromSession($group, $fldname);
			}
			$all_text .= md5("magic");
			setSessionFromVal($group, 'checksum', md5($all_text));
			header("Location: edit_confirm.php");
			exit;
		}
		
		header("Location: edit.php");
		exit;
	}
	else if(!isset($_SESSION[$group])){	//初回
		clearSession($group);
		
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
			intval($_GET["id"]));
		$result = my_db_exec($con, $sql);
		$num = my_db_num_rows($result);
		if($num == 0){
			my_db_free_result($result);
			header("Location: list.php");
			exit;
		}
		$_SESSION[$group] = my_db_fetch_array($result, 0);
		setSessionFromVal($group , 'file_only_oldname', fromSession($group, 'file_only_dispname'));
		setSessionFromVal($group , 'file_only_oldfile', fromSession($group, 'file_only_savename'));
		clearSession($group, 'file_only_dispname');
		clearSession($group, 'file_only_savename');
		
		$category = fromSession($group, 'category');
		setSessionFromVal($group, 'category', $category . ";" . getItemName(WNEW_CATEGORY, $category));

		$fixed_disp = fromSession($group, 'fixed_disp');
		if(strcmp($fixed_disp, "0") == 0)
			$fixed_disp .= ";しない";
		else
			$fixed_disp .= ";する";
		setSessionFromVal($group, 'fixed_disp', $fixed_disp);
		
		$format = fromSession($group, 'format');
		if(strcmp($format, "1") == 0)
			$format .= ";本文あり";
		else if(strcmp($format, "2") == 0)
			$format .= ";本文無し（添付ファイル表示）";
		else
			$format .= ";本文無し（リンクのみ）";
			
		setSessionFromVal($group, 'format', $format);
		
		my_db_free_result($result);
	}
	
	$tmpl = new Tmpl2( "templates/edit.html" ) ;

	$tmpl->assign("token", my_make_token($_SERVER['SCRIPT_FILENAME']));
	
	$error = fromSession($group, 'error');
	if(!empty($error))
	{
		$tmpl->assign_def('error');
		$tmpl->assign('error', $error);
	}
	
	$tmpl->assign("post_date", htmlspecialchars(fromSession($group, 'post_date')));
	$tmpl->assign("post_hour_selected" . htmlspecialchars(fromSession($group, 'post_hour')), " selected");
	$tmpl->assign("post_min_selected" . htmlspecialchars(fromSession($group, 'post_min')), " selected");
	
	$tmpl->assign("title", htmlspecialchars(fromSession($group, 'title')));
	
	$tmpl->assign("s_disp_date", htmlspecialchars(fromSession($group, 's_disp_date')));
	$tmpl->assign("s_disp_hour_selected" . htmlspecialchars(fromSession($group, 's_disp_hour')), " selected");
	$tmpl->assign("s_disp_min_selected" . htmlspecialchars(fromSession($group, 's_disp_min')), " selected");
	
	$tmpl->assign("e_disp_date", htmlspecialchars(fromSession($group, 'e_disp_date')));
	$tmpl->assign("e_disp_hour_selected" . htmlspecialchars(fromSession($group, 'e_disp_hour')), " selected");
	$tmpl->assign("e_disp_min_selected" . htmlspecialchars(fromSession($group, 'e_disp_min')), " selected");

	$arr = explode(";", fromSession($group, 'category'));
	$tmpl->assign("category_selected" . htmlspecialchars($arr[0]), " selected");
	
	$arr = explode(";", fromSession($group, 'fixed_disp'));
	$fixed_disp = $arr[0];
	$tmpl->assign("fixed_disp_checked" . htmlspecialchars($fixed_disp), " checked");
	
	$tmpl->assign("fixed_disp_date", htmlspecialchars(fromSession($group, 'fixed_disp_date')));
	
	$arr = explode(";", fromSession($group, 'format'));
	$format = $arr[0];
	$tmpl->assign("format_checked" . htmlspecialchars($format), " checked");
	
	$file_only_oldname = fromSession($group, 'file_only_oldname');
	if(!empty($file_only_oldname)){
		$tmpl->assign_def("file_only_exists");
		$tmpl->assign("file_only_oldname", htmlspecialchars(fromSession($group, 'file_only_oldname')));
	}
	$tmpl->assign("file_only_savename", htmlspecialchars(fromSession($group, 'file_only_savename')));
	$tmpl->assign("file_only_dispname", htmlspecialchars(fromSession($group, 'file_only_dispname')));
	$tmpl->assign("link_only_url", htmlspecialchars(fromSession($group, 'link_only_url')));
	$tmpl->assign("body", fromSession($group, 'body'));
	
	$tmpl->flush();
?>
