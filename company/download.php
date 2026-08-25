<?php
	require_once("../lib/common.php");
	
	$con = my_db_connect();
	
	$tmpl = new Tmpl2("memory");
	$tmpl->setmem(parseSSI(file_get_contents("templates/download.html"), dirname(__FILE__)));
	
	$sql = "select catalog_id, contents_dispname, cover_savename, title from t_catalog where lang = 'ja' and cover_savename is not null order by disp_order";
	$result = my_db_exec($con, $sql);
	$num = my_db_num_rows($result);
	$tmpl->loopset("loop");
	while(($arr = my_db_fetch_array($result)) !== false){
		$tmpl->assign("catalog_id", htmlspecialchars($arr['catalog_id']));
		$tmpl->assign("contents_dispname", htmlspecialchars($arr['contents_dispname']));
		$tmpl->assign("cover_savename", htmlspecialchars($arr['cover_savename']));
		$tmpl->assign("title", htmlspecialchars($arr['title']));
		$tmpl->loopnext("loop");
	}
	$tmpl->loopend("loop");
	my_db_free_result($result);
	my_db_close($con);
	
	$tmpl->flush();
?>
