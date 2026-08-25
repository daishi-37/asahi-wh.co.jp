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
	$all_text = "";
	$all_text .= fromSession($group, 'banner_disp');
	$all_text .= fromSession($group, 'link_url');
	$all_text .= fromSession($group, 'publish');
	for ($i=0; $i < 19; $i++) { 
		$all_text .= $_SESSION[$group]['items'][$i] ->item_name;
		$all_text .= $_SESSION[$group]['items'][$i] ->item_contents;
	}
	$all_text .= $_SESSION[$group]['ref_url']->item_name;
	$all_text .= $_SESSION[$group]['ref_url']->item_contents;
	$all_text .= md5("magic");
	if(strcmp(fromSession($group, 'checksum'), md5($all_text)) != 0)
	{
		clearSession($group);
		header("Location: ../index.php");
		exit;
	}

		
	if(!empty($_POST["regist"]) && isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true)
	{
		//ここで登録
		$con = my_db_connect();
		
		my_db_begin($con);
		
		$params = array();
		$sql = 'update t_recruit set ';

		$arr = explode(";", fromSession($group, 'banner_disp'));
		$params[] = intval($arr[0]);
		$sql .= sprintf("banner_disp = \$%d", count($params));

		$params[] = fromSession($group, 'link_url');
		$sql .= sprintf(", link_url = \$%d", count($params));

		$arr = explode(";", fromSession($group, 'publish'));
		$params[] = intval($arr[0]);
		$sql .= sprintf(", publish = \$%d", count($params));

		$sql .= ", items = null";

		$ref_url = fromSession($group, 'ref_url');
		if(!empty($ref_url->item_name)){
			$params[] = $ref_url->item_name;
			$sql .= sprintf(", ref_url = ROW(\$%d,", count($params));
			$params[] = $ref_url->item_contents;
			$sql .= sprintf("\$%d)", count($params));
		}
	
		$result = my_db_query_params($con, $sql, $params);
		my_db_free_result($result);

		$sql = 'update t_recruit set ';
		$set = 0;
		$params = array();
		for ($i=0; $i < 19; $i++) { 
			if(!empty($_SESSION[$group]['items'][$i]->item_name)){
				$params[] = $_SESSION[$group]['items'][$i]->item_name;
				if($set > 0){
					$sql .= ", ";
				}
				$sql .= sprintf("items[%d] = ROW(\$%d,", $set++, count($params));
				$params[] = $_SESSION[$group]['items'][$i]->item_contents;
				$sql .= sprintf("\$%d)", count($params));
			}
		}
		$result = my_db_query_params($con, $sql, $params);
		my_db_free_result($result);

		my_db_commit($con);
		my_db_close($con);
		
		clearSession($group);
		header("Location: finish.php");
		exit;

	}

	$tmpl = new Tmpl2( "templates/confirm.html" ) ;

	$tmpl->assign("token", my_make_token($_SERVER['SCRIPT_FILENAME']));

	$arr = explode(";", fromSession($group, 'banner_disp'));
	$tmpl->assign("banner_disp", htmlspecialchars($arr[1]));
	
	$tmpl->assign("link_url", htmlspecialchars(fromSession($group, 'link_url')));

	$arr = explode(";", fromSession($group, 'publish'));
	$tmpl->assign("publish", htmlspecialchars($arr[1]));

	$arr = fromSessionArray($group, 'items');
	$tmpl->loopset('loop');
	for ($i=0; $i < 19; $i++) { 
		if(empty($arr[$i]->item_name)){
			continue;
		}
		else{
			$tmpl->assign('item_name', htmlspecialchars($arr[$i]->item_name));
			$tmpl->assign('item_contents', nl2br(htmlspecialchars($arr[$i]->item_contents)));
		}
		$tmpl->loopnext('loop');
	}
	$tmpl->loopend('loop');

	$ref_url = fromSession($group, 'ref_url');
	if(!empty($ref_url->item_name)){
		$tmpl->assign_def('ref_url');
		$tmpl->assign('ref_url_name', htmlspecialchars($ref_url->item_name));
		$tmpl->assign('ref_url', htmlspecialchars($ref_url->item_contents));
	}
	$tmpl->flush();
?>
