<?php
    require("../lib/common.php");

    class _news {
        public $serial;
        public $category;
        public $target;
        public $post_date;
        public $title;
    }

    $ret = array();

	$con = my_db_connect();
	
	$wnew_count = 10;
	if(!empty($_POST['category'])){
        $where_category = ' and category = ' . intval($_POST['category']);
    }
    else{
        $where_category = '';
    }

    if(!empty($_POST['lang']) && $_POST['lang'] == 'en'){
        $lang = 'en';
    }
    else{
        $lang = 'ja';
    }
	$sql = "select serial, category, format, to_char(post_date, 'YYYY.FMMM.FMDD') as post_date, title from t_wnew where coalesce(s_disp_date, post_date) <= LOCALTIMESTAMP and (e_disp_date is null or e_disp_date > LOCALTIMESTAMP){$where_category} and lang = '{$lang}' and fixed_disp = '1' and date_trunc('day', fixed_disp_date + '1 day') > LOCALTIMESTAMP order by post_date desc, serial desc limit {$wnew_count}";
	$result_fixed = my_db_exec($con, $sql);
	$num_fixed = my_db_num_rows($result_fixed);
	
	$sql = "select serial, category, format, to_char(post_date, 'YYYY.FMMM.FMDD') as post_date, title from t_wnew where coalesce(s_disp_date, post_date) <= LOCALTIMESTAMP and (e_disp_date is null or e_disp_date > LOCALTIMESTAMP){$where_category} and lang = '{$lang}' and fixed_disp != '1' order by post_date desc, serial desc limit {$wnew_count}";
	$result = my_db_exec($con, $sql);
	$num = my_db_num_rows($result);
	if(($num_fixed + $num) > 0){
		$last_num = $num_fixed + $num;
		if($last_num > $wnew_count){
			$last_num = $wnew_count;
		}
		for($i=0; $i<$num_fixed; $i++){
			$arr = my_db_fetch_array($result_fixed, $i);
			
            $news = new _news();

            $news->serial = htmlspecialchars($arr['serial']);
            if($lang == 'ja'){
                $news->category = htmlspecialchars(getItemName(WNEW_CATEGORY, $arr['category']));
            }
            else{
                $news->category = htmlspecialchars(getItemName(WNEW_CATEGORY_EN, $arr['category']));
            }
            $news->post_date = htmlspecialchars($arr['post_date']);
			if($arr['format'] != 1){
                $news->target = "_blank";
			}
            else{
                $news->target = "_self";
            }
			$news->title = htmlspecialchars($arr['title']);
            $ret[] = $news;
		}
		for($j=0; $j<$num && $i<$wnew_count; $i++, $j++){
			$arr = my_db_fetch_array($result, $j);
			
            $news = new _news();

            $news->serial = htmlspecialchars($arr['serial']);
            if($lang == 'ja'){
                $news->category = htmlspecialchars(getItemName(WNEW_CATEGORY, $arr['category']));
            }
            else{
                $news->category = htmlspecialchars(getItemName(WNEW_CATEGORY_EN, $arr['category']));
            }
            $news->post_date = htmlspecialchars($arr['post_date']);
			if($arr['format'] != 1){
                $news->target = "_blank";
			}
            else{
                $news->target = "_self";
            }
			$news->title = htmlspecialchars($arr['title']);
            $ret[] = $news;
		}
	}
	my_db_free_result($result);
	my_db_close($con);
    header_remove();
    header('Content-Type: application/json;charset=utf-8');
    echo json_encode($ret);
?>