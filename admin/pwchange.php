<?php
	require("../lib/common.php");
	
	if(checkAdminSession()){
		header("Location: index.php");
		exit;
	}

	$err = false;
	if(isset($_POST["pass-new"]) && isset($_POST["pass-cnf"]) && isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true)
	{
		$pass_new = mb_convert_kana(trim($_POST["pass-new"]), "a", "utf-8");
		$pass_cnf = mb_convert_kana(trim($_POST["pass-cnf"]), "a", "utf-8");
		if(mb_detect_encoding($pass_new, "ASCII,UTF-8", true) != "ASCII"
		|| mb_detect_encoding($pass_cnf, "ASCII,UTF-8", true) != "ASCII"
		|| preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[`~!@#$%^&*()_+\-={}[\]\\\\|:;\x22\x27<>,.?\/])[a-zA-Z0-9`~!@#$%^&*()_+\-={}[\]\\\\|:;\x22\x27<>,.?\/]{8,40}$/", $pass_new) != 1
		|| strcmp($pass_new, $pass_cnf) != 0)
		{
			$err = true;
		}
		else
		{
			$con = my_db_connect();
			
			my_db_begin($con);

			$sql = 'UPDATE t_login SET sChangePassword = $1, sPassword = $2 WHERE sId = $3';
			$pw = hash("sha512", $pass_new . md5("W5Ip3!di") . $_SESSION['userdata']['id']);
			$params = array('1', $pw, $_SESSION['userdata']['id']);
			$result = my_db_query_params($con, $sql, $params);
			
			my_db_commit($con);

			my_db_close($con);

			$_SESSION['userdata']['changepassword'] = '1';
			
			header("Location: index.php");
			exit;
		}
	}
	$tmpl = new Tmpl2( "templates/pwchange.html" ) ;

	$tmpl->assign("token", my_make_token($_SERVER['SCRIPT_FILENAME']));
	
	if($err){
		$tmpl->assign_def("error");
	}
	$tmpl->flush();
?>
