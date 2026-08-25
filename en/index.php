<?php
	require("../lib/common.php");
	
	$tmpl = new Tmpl2("memory");
	$tmpl->setmem(parseSSI(file_get_contents("templates/index.html"), dirname(__FILE__)));

	$con = my_db_connect();
	
	$wnew_count = 7;
	
	$sql = "select serial, category, format, to_char(post_date, 'YYYY.FMMM.FMDD') as d_post_date, title from t_wnew where coalesce(s_disp_date, post_date) <= LOCALTIMESTAMP and (e_disp_date is null or e_disp_date > LOCALTIMESTAMP) and lang = 'en' and fixed_disp = '1' and date_trunc('day', fixed_disp_date + '1 day') > LOCALTIMESTAMP order by post_date desc, serial desc limit {$wnew_count}";
	$result_fixed = my_db_exec($con, $sql);
	$num_fixed = my_db_num_rows($result_fixed);
	
	$sql = "select serial, category, format, to_char(post_date, 'YYYY.FMMM.FMDD') as d_post_date, title from t_wnew where coalesce(s_disp_date, post_date) <= LOCALTIMESTAMP and (e_disp_date is null or e_disp_date > LOCALTIMESTAMP) and lang = 'en' and fixed_disp != '1' order by post_date desc, serial desc limit {$wnew_count}";
	$result = my_db_exec($con, $sql);
	$num = my_db_num_rows($result);
	if(($num_fixed + $num) == 0){
		$tmpl->assign_def("wnew_nothing");
	}
	else{
		$last_num = $num_fixed + $num;
		if($last_num > $wnew_count){
			$last_num = $wnew_count;
		}
		$tmpl->loopset("wnew_loop");
		for($i=0; $i<$num_fixed; $i++){
			$arr = my_db_fetch_array($result_fixed, $i);
			
			$tmpl->assign("wnew_category", htmlspecialchars(getItemName(WNEW_CATEGORY_EN, $arr['category'])));

			$tmpl->assign("wnew_post_date", htmlspecialchars($arr['d_post_date']));
			
			$tmpl->assign("wnew_serial", htmlspecialchars($arr['serial']));
			if($arr['format'] != 1){
				$tmpl->assign("wnew_target", " target=_blank");
			}
			
			$tmpl->assign("wnew_title", htmlspecialchars($arr['title']));
			$tmpl->loopnext("wnew_loop");
		}
		for($j=0; $j<$num && $i<$wnew_count; $i++, $j++){
			$arr = my_db_fetch_array($result, $j);
			
			$tmpl->assign("wnew_category", htmlspecialchars(getItemName(WNEW_CATEGORY_EN, $arr['category'])));

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
