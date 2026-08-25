<?php
	require_once("../lib/common.php");
	
	$con = my_db_connect();
	
	$tmpl = new Tmpl2("memory");
	$tmpl->setmem(parseSSI(file_get_contents("templates/index.html"), dirname(__FILE__)));
	
	$sql = "select serial, category, format, to_char(post_date, 'YYYY.FMMM.FMDD') as d_post_date, title from t_wnew where coalesce(s_disp_date, post_date) <= LOCALTIMESTAMP and (e_disp_date is null or e_disp_date > LOCALTIMESTAMP) and lang = 'ja' order by post_date desc, serial desc";
	$result = my_db_exec($con, $sql);
	$num = my_db_num_rows($result);
	if($num == 0){
		$tmpl->assign_def("wnew_nothing");
	}
	else{
		$tmpl->loopset("wnew_loop");
		for($i=0; $i<$num; $i++){
			$arr = my_db_fetch_array($result, $i);
			$tmpl->assign("wnew_category", htmlspecialchars(getItemName(WNEW_CATEGORY, $arr['category'])));
			$tmpl->assign("wnew_post_date", htmlspecialchars($arr['d_post_date']));
			
			$tmpl->assign("wnew_serial", htmlspecialchars($arr['serial']));
			if($arr['format'] != 1){
				$tmpl->assign("wnew_target", " target=_blank");
			}
			
			$tmpl->assign("wnew_title", htmlspecialchars($arr['title']));
			$tmpl->loopnext("wnew_loop");
		}
		$tmpl->loopend("wnew_loop");
	}
	my_db_free_result($result);
	my_db_close($con);
	
	$tmpl->flush();
?>
