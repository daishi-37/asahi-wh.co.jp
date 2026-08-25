<?php
	require("../../lib/common.php");
	
	class _catalog {
		public $catalog_id;
		public $contents_dispname;
		public $contents_savename;
		public $old_contents_dispname;
		public $old_contents_savename;
		public $cover_dispname;
		public $cover_savename;
		public $old_cover_dispname;
		public $old_cover_savename;
		public $title;
		public $disp_order;
		public $delete_this;
		function __construct(){
			$this->contents_dispname = '';
			$this->contents_savename = '';
			$this->old_contents_dispname = '';
			$this->old_contents_savename = '';
			$this->cover_dispname = '';
			$this->cover_savename = '';
			$this->old_cover_dispname = '';
			$this->old_cover_savename = '';
			$this->title = '';
			$this->delete_this = false;
		} 
	}
	if(checkAdminSession() == false){
		header("Location: ../login.php");
		exit;
	}elseif(isAdminPasswordChanged() == false){
		header("Location: ../pwchange.php");
		exit;
	}
	
	$group = 'admin_dl';
	$error = '';
	if(!empty($_POST['regist']) && isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true)
	{
		for ($i=0; $i < 12; $i++) {
			$seq = sprintf("%02d", $i+1);
			$catalog_del = fromPostData("catalog_del{$seq}");
			if(!empty($catalog_del)){
				$_SESSION[$group]['records'][$i]->delete_this = true;
			}
			else{
				$_SESSION[$group]['records'][$i]->delete_this = false;
				$_SESSION[$group]['records'][$i]->contents_dispname = fromPostData("contents_dispname{$seq}");
				$_SESSION[$group]['records'][$i]->contents_savename = fromPostData("contents_savename{$seq}");
				$_SESSION[$group]['records'][$i]->cover_dispname = fromPostData("cover_dispname{$seq}");
				$_SESSION[$group]['records'][$i]->cover_savename = fromPostData("cover_savename{$seq}");
				$_SESSION[$group]['records'][$i]->title = fromPostData("title{$seq}");

				$contents = !empty($_SESSION[$group]['records'][$i]->contents_dispname) || !empty($_SESSION[$group]['records'][$i]->old_contents_dispname);
				$cover = !empty($_SESSION[$group]['records'][$i]->cover_dispname) || !empty($_SESSION[$group]['records'][$i]->old_cover_dispname);
				if($contents || $cover || !empty($_SESSION[$group]['records'][$i]->title)){
					if (!$contents || !$cover || empty($_SESSION[$group]['records'][$i]->title)) {
						$error .= "資料{$seq}の入力が不完全です。</br>";
					}
				}
			}
		}
		setSessionFromVal($group, 'error', $error);
		if($error == ""){
			$con = my_db_connect();

			my_db_begin($con);

			$arr_del_files = array();
			$arr_copy_files = array();
			$sql = 'update t_catalog set ' .
			'contents_dispname = $1, contents_savename = $2, contents_mimetype = $3, ' .
			'cover_dispname = $4, cover_savename = $5, cover_mimetype = $6, ' .
			'title = $7, disp_order = $8 ' .
			'where catalog_id = $9';
			for ($i=0; $i < 12; $i++) { 
				$rec = $_SESSION[$group]['records'][$i];
				$params = array();
				if($rec->delete_this){
					$arr_del_files[] = $rec->old_contents_savename;
					$arr_del_files[] = $rec->old_cover_savename;
					$params[] = null;	//contents_dispname
					$params[] = null;	//contents_savename
					$params[] = null;	//contents_mimetype
					$params[] = null;	//cover_dispname
					$params[] = null;	//cover_savename
					$params[] = null;	//cover_mimetype
					$params[] = null;	//title
					$params[] = $rec->disp_order;	//disp_order
				}
				else{
					$dispname = (!empty($rec->contents_dispname))? $rec->contents_dispname : $rec->old_contents_dispname;
					if(empty($dispname)){
						$dispname = null;
						$savename = null;
					}
					else{
						$savename = sprintf("contents%02d", $rec->catalog_id);
					}
					$params[] = $dispname;
					$params[] = $savename;
					if(!empty($rec->contents_dispname)){
						$arr_copy_files[] = array($rec->contents_savename, $savename);
						$mime_type = mime_content_type(TEMPPATH . "/" . $rec->contents_savename);
					}
					elseif(!empty($rec->old_contents_dispname)){
						$mime_type = mime_content_type(OUTPUTPATH . "/dl/" . $rec->old_contents_savename);
					}
					else{
						$mime_type = null;
					}
					$params[] = $mime_type;

					$dispname = (!empty($rec->cover_dispname))? $rec->cover_dispname : $rec->old_cover_dispname;
					if(empty($dispname)){
						$dispname = null;
						$savename = null;
					}
					else{
						$savename = sprintf("cover%02d", $rec->catalog_id);
					}
					$params[] = $dispname;
					$params[] = $savename;
					if(!empty($rec->cover_dispname)){
						$arr_copy_files[] = array($rec->cover_savename, $savename);
						$mime_type = mime_content_type(TEMPPATH . "/" . $rec->cover_savename);
					}
					elseif(!empty($rec->old_cover_dispname)){
						$mime_type = mime_content_type(OUTPUTPATH . "/dl/" . $rec->old_cover_savename);
					}
					else{
						$mime_type = null;
					}
					$params[] = $mime_type;

					$params[] = $rec->title;	//title
					$params[] = $rec->disp_order;	//disp_order
				}
				$params[] = $rec->catalog_id;
				$result = my_db_query_params($con, $sql, $params);
				my_db_free_result($result);
			}

			my_db_commit($con);

			foreach($arr_del_files as $file){
				unlink(OUTPUTPATH . "/dl/" . $file);
			}

			foreach($arr_copy_files as $pair){
				list($from, $to) = $pair;
				$from = TEMPPATH . "/" . $from;
				$to = OUTPUTPATH . "/dl/" . $to;
				if(file_exists($to)){
					unlink($to);
				}
				copy($from, $to);
			}
			clearSession($group);
			header("Location: finish.php");
			exit;
		}
		
		header("Location: regist.php");
		exit;
	}
	else if(!isset($_SESSION[$group])){	//初回
		
		clearSession($group);

		$con = my_db_connect();

		$sql = 
			"select " .
				"catalog_id, " .
				"contents_dispname, " .
				"contents_savename, " .
				"cover_dispname, " .
				"cover_savename, " .
				"title, " .
				"disp_order " .
			"from t_catalog " .
			"where lang = 'ja' " .
			"order by disp_order";
		$result = my_db_exec($con, $sql);
		$num = my_db_num_rows($result);
		if($num == 0){
			my_db_free_result($result);
			header("Location: ../index.php");
			exit;
		}
		$_SESSION[$group]['records'] = array();
		while(($arr = my_db_fetch_array($result)) !== false){
			$rec = new _catalog();
			$rec->catalog_id = $arr['catalog_id'];
			$rec->contents_dispname = '';
			$rec->contents_savename = '';
			$rec->old_contents_dispname = $arr['contents_dispname'];
			$rec->old_contents_savename = $arr['contents_savename'];
			$rec->cover_dispname = '';
			$rec->cover_savename = '';
			$rec->old_cover_dispname = $arr['cover_dispname'];
			$rec->old_cover_savename = $arr['cover_savename'];
			$rec->title = $arr['title'];
			$rec->disp_order = $arr['disp_order'];
			$_SESSION[$group]['records'][] = $rec;
		}
		
		my_db_free_result($result);
	}

	$tmpl = new Tmpl2( "templates/regist.html" ) ;

	$tmpl->assign("token", my_make_token($_SERVER['SCRIPT_FILENAME']));
	
	$error = fromSession($group, 'error');
	if(!empty($error))
	{
		$tmpl->assign_def('error');
		$tmpl->assign('error', $error);
	}

	$tmpl->loopset('loop');
	for ($i=0; $i < 12; $i++) {
		$rec = $_SESSION[$group]['records'][$i];
		$tmpl->assign('seq', sprintf("%02d", $i+1));

		if(!empty($rec->old_contents_dispname)){
			$tmpl->assign_def('contents_exists');
			$tmpl->assign('old_contents_dispname', htmlspecialchars($rec->old_contents_dispname));
		}
		$tmpl->assign('contents_dispname', htmlspecialchars($rec->contents_dispname));
		$tmpl->assign('contents_savename', htmlspecialchars($rec->contents_savename));

		if(!empty($rec->old_cover_dispname)){
			$tmpl->assign_def('cover_exists');
			$tmpl->assign('old_cover_savename', htmlspecialchars($rec->old_cover_savename));
		}
		$tmpl->assign('cover_dispname', htmlspecialchars($rec->cover_dispname));
		$tmpl->assign('cover_savename', htmlspecialchars($rec->cover_savename));

		$tmpl->assign('title', htmlspecialchars($rec->title));

		if(!empty($rec->old_contents_dispname)){
			$tmpl->assign_def('data_exists');
			if($rec->delete_this){
				$tmpl->assign('catalog_del_checked', ' checked');
			}
		}

		$tmpl->loopnext('loop');
	}
	$tmpl->loopend('loop');

	$tmpl->flush();
?>
