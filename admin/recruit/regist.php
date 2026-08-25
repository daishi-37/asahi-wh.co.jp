<?php
	require("../../lib/common.php");
	
	if(checkAdminSession() == false){
		header("Location: ../login.php");
		exit;
	}elseif(isAdminPasswordChanged() == false){
		header("Location: ../pwchange.php");
		exit;
	}

	$group = 'admin_recruit';
	$error = '';
	if(!empty($_POST['confirm']) && isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true)
	{
		//バナー表示する／しない
		$banner_disp = fromPostData("banner_disp");
		$arr = explode(";", $banner_disp);
		if(count($arr) != 2){
			$error .= "バナーを表示するかどうか選択してください。</br>";
			$banner_disp = "0";
		}
		else if($arr[0] != "0" && $arr[0] != "1"){
			$error .= "バナー表示の値が不正です。</br>";
			$banner_disp = "0";
		}
		setSessionFromVal($group, 'banner_disp', $banner_disp);

		//リンク先URL
		$link_url = mb_convert_kana(trim(fromPostData('link_url')), "KV", "UTF-8");
		if(!empty($link_url) && preg_match('#^(http|https)://.+#', $link_url) == 0){
			$error .= "リンク先URLが不正です。</br>";
		}
		elseif(substr($banner_disp, 0, 1) == '1' && empty($link_url)){
			$error .= "リンク先URLが入力されていません。</br>";
		}
		setSessionFromVal($group, 'link_url', $link_url);

		//募集内容掲載中／非掲載
		$publish = fromPostData("publish");
		$arr = explode(";", $publish);
		if(count($arr) != 2){
			$error .= "募集内容を掲載するかどうか選択してください。</br>";
			$publish = "0";
		}
		else if($arr[0] != "0" && $arr[0] != "1"){
			$error .= "募集内容掲載の値が不正です。</br>";
			$publish = "0";
		}
		setSessionFromVal($group, 'publish', $publish);

		//募集内容
		$items_exists = false;
		for ($i=0; $i < 19; $i++) { 
			$item_name = fromPostData(sprintf("item_name%02d", $i+1));
			$item_contents = fromPostData(sprintf("item_contents%02d", $i+1));
			if((!empty($item_name) && empty($item_contents)) || (empty($item_name) && !empty($item_contents))){
				$error .= "募集内容の入力が不完全です。</br>";
			}
			elseif(!empty($item_name) && !empty($item_contents)){
				$items_exists = true;
			}
			$_SESSION[$group]['items'][$i] ->item_name = $item_name;
			$_SESSION[$group]['items'][$i] ->item_contents = $item_contents;
		}
		if(substr($publish, 0, 1) == '1' && $items_exists == false){
			$error .= "募集内容が入力されていません。</br>";
		}

		//参照URL
		$ref_url_name = fromPostData('ref_url_name');
		$ref_url = mb_convert_kana(trim(fromPostData('ref_url')), "KV", "UTF-8");
		if(!empty($ref_url) && preg_match('#^(http|https)://.+#', $ref_url) == 0){
			$error .= "参照先URLが不正です。</br>";
		}
		$_SESSION[$group]['ref_url']->item_name = $ref_url_name;
		$_SESSION[$group]['ref_url']->item_contents = $ref_url;

		if(empty($error)){
			$all_text = "";
			$all_text .= $banner_disp;
			$all_text .= $link_url;
			$all_text .= $publish;
			for ($i=0; $i < 19; $i++) { 
				$all_text .= $_SESSION[$group]['items'][$i] ->item_name;
				$all_text .= $_SESSION[$group]['items'][$i] ->item_contents;
			}
			$all_text .= $_SESSION[$group]['ref_url']->item_name;
			$all_text .= $_SESSION[$group]['ref_url']->item_contents;
			
			$all_text .= md5("magic");
			setSessionFromVal($group, 'checksum', md5($all_text));
			header("Location: confirm.php");
			exit;
		}
		header("Location: regist.php");
		exit;
	}
	else if(!isset($_SESSION[$group])){	//初回
		
		$con = my_db_connect();
		$sql = "select banner_disp, link_url, publish, json_agg(items) as items, json_agg(ref_url) as ref_url from t_recruit group by banner_disp, link_url, publish";
		$result = my_db_exec($con, $sql);
		$num = my_db_num_rows($result);
		if($num == 0){
			my_db_free_result($result);
			header("Location: ../index.php");
			exit;
		}
		$_SESSION[$group] = my_db_fetch_array($result, 0);
		$dec = json_decode($_SESSION[$group]['items']);
		if(empty($dec[0])){
			$_SESSION[$group]['items'] = array();
		}
		else{
			$_SESSION[$group]['items'] = $dec[0];
		}
		$dec = json_decode($_SESSION[$group]['ref_url']);
		$_SESSION[$group]['ref_url'] = $dec[0];
		my_db_free_result($result);
	}

	$tmpl = new Tmpl2( "templates/regist.html" ) ;

	$tmpl->assign("token", my_make_token($_SERVER['SCRIPT_FILENAME']));
	
	if(!empty($error))
	{
		$tmpl->assign_def('error');
		$tmpl->assign('error', $error);
	}

	$arr = explode(";", fromSession($group, 'banner_disp'));
	$tmpl->assign("banner_disp_checked" . $arr[0], " checked");
	
	$tmpl->assign("link_url", htmlspecialchars(fromSession($group, 'link_url')));

	$arr = explode(";", fromSession($group, 'publish'));
	$tmpl->assign("publish_checked" . $arr[0], " checked");

	$arr = fromSessionArray($group, 'items');
	$tmpl->loopset('loop');
	for ($i=0; $i < 19; $i++) { 
		$tmpl->assign('seq', sprintf("%02d", $i+1));
		if(empty($arr[$i])){
			$tmpl->assign('item_name', '');
			$tmpl->assign('item_contents', '');
		}
		else{
			$tmpl->assign('item_name', htmlspecialchars($arr[$i]->item_name));
			$tmpl->assign('item_contents', htmlspecialchars($arr[$i]->item_contents));
		}
		$tmpl->loopnext('loop');
	}
	$tmpl->loopend('loop');
	$ref_url = fromSession($group, 'ref_url');
	$tmpl->assign('ref_url_name', htmlspecialchars($ref_url->item_name));
	$tmpl->assign('ref_url', htmlspecialchars($ref_url->item_contents));
	
	$tmpl->flush();
?>
