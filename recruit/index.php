<?php
	require_once("../lib/common.php");
	
	$con = my_db_connect();
	
	$sql = "select banner_disp, link_url, publish, json_agg(items) as items, json_agg(ref_url) as ref_url from t_recruit group by banner_disp, link_url, publish";
	$result = my_db_exec($con, $sql);
	$num = my_db_num_rows($result);
	if($num == 0){
		my_db_free_result($result);
		header("Location: ../index.php");
		exit;
	}
	$arr = my_db_fetch_array($result, 0);
	$dec = json_decode($arr['items']);
	if(empty($dec[0])){
		$arr['items'] = array();
	}
	else{
		$arr['items'] = $dec[0];
	}
	$dec = json_decode($arr['ref_url']);
	$arr['ref_url'] = $dec[0];
	my_db_free_result($result);

	$tmpl = new Tmpl2("memory");

	if($arr['publish'] == '0'){
		$tmpl->setmem(parseSSI(file_get_contents("templates/index-none.html"), dirname(__FILE__)));
	}
	else{
		$tmpl->setmem(parseSSI(file_get_contents("templates/index.html"), dirname(__FILE__)));
		if($arr['banner_disp'] != '0'){
			$tmpl->assign_def("banner_disp");
			$tmpl->assign("link_url", htmlspecialchars($arr['link_url']));
		}
		$tmpl->loopset('loop');
		for ($i=0; $i < 19; $i++) { 
			if(empty($arr['items'][$i]->item_name)){
				continue;
			}
			$tmpl->assign('item_name', htmlspecialchars($arr['items'][$i]->item_name));
			$tmpl->assign('item_contents', nl2br(htmlspecialchars($arr['items'][$i]->item_contents)));
			$tmpl->loopnext('loop');
		}
		$tmpl->loopend('loop');
		if(!empty($arr['ref_url']->item_name)){
			$tmpl->assign_def('ref_url');
			$tmpl->assign('ref_url_name', htmlspecialchars($arr['ref_url']->item_name));
			$tmpl->assign('ref_url', htmlspecialchars($arr['ref_url']->item_contents));
		}
	}
	my_db_free_result($result);
	my_db_close($con);
	
	$tmpl->flush();
?>
