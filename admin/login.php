<?php
	require("../lib/common.php");
	
	$err = false;
	if(isset($_POST["id"]) && isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true)
	{
		$id = mb_convert_kana(trim($_POST["id"]), "a", "utf-8");
		$pw = mb_convert_kana(trim($_POST["pass"]), "a", "utf-8");
		if(mb_detect_encoding($id, "ASCII,UTF-8", true) != "ASCII"
		|| mb_detect_encoding($pw, "ASCII,UTF-8", true) != "ASCII")
		{
			$err = true;
		}
		else
		{
			$session = md5($_COOKIE["PHPSESSIDA"]);
			
			$con = my_db_connect();
			
			$sql = sprintf("select count(*) as counter from t_login_cache where sid = '%s' and derror is not null", my_db_escape_string($id));
			$result = my_db_exec($con, $sql);
			$arr = my_db_fetch_array($result);
			if(intval($arr['counter']) >= intval(RETRY_MAX)){
				$sql = sprintf("select derror, case when age(CURRENT_TIMESTAMP, derror) > interval '%d minute' then 'on' else 'off' end as status from t_login_cache where sid = '%s' and derror is not null order by derror desc", RETRY_GUARD, my_db_escape_string($id));
				$result = my_db_exec($con, $sql);
				$arr = my_db_fetch_array($result);
				if($arr['status'] == "off"){
					my_db_close($con);
					$_SESSION['userdata'] = array();
					return false;
				}
				my_db_begin($con);
				$sql = sprintf("delete from t_login_cache where sid = '%s'", my_db_escape_string($id));
				$result = my_db_exec($con, $sql);
				my_db_commit($con);
			}
			
			$pw = hash("sha512", $pw . md5(MAGIC) . $id);
			
			$sql = "SELECT ";
			$sql .= " sId, ";
			$sql .= " sChangePassword ";
			$sql .= "FROM t_login ";
			$sql .= "WHERE sId='" . my_db_escape_string($id) . "' ";
			$sql .= "  AND sPassword='" . my_db_escape_string($pw) . "' ";
			$sql .= "  AND sPower='0' ";
			$result = my_db_exec($con, $sql);
			$rows = my_db_num_rows($result);
			if($rows > 0){
				$arr = my_db_fetch_array($result);
				$data['id'] = $arr['sid'];
				$data['changepassword'] = $arr['schangepassword'];
				$data['power'] = '0';
				my_db_begin($con);
				$sql = sprintf("delete from t_login_cache where sid = '%s'", my_db_escape_string($id));
				$result = my_db_exec($con, $sql);

				session_regenerate_id();
				$session_id = session_id();
				$session = md5($session_id);
				$data['session'] = $session;

				$sql = sprintf("insert into t_login_cache (sid, dlogin) values ('%s',CURRENT_TIMESTAMP)", my_db_escape_string($id));
				$result = my_db_exec($con, $sql);
				my_db_commit($con);
				
				$_SESSION["userdata"] = $data;

				if($data['changepassword'] != '1'){
					header("Location: pwchange.php");
					exit;
				}
			}
			else{
				$err = true;
				$sql = sprintf("select count(*) as counter from t_login where sid = '%s' and spower = '0'", my_db_escape_string($id));
				$result = my_db_exec($con, $sql);
				$arr = my_db_fetch_array($result);
				if($arr['counter'] != 0){
					my_db_begin($con);
					$sql = sprintf("insert into t_login_cache (sid, derror) values ('%s',CURRENT_TIMESTAMP)",
						my_db_escape_string($id));
					$result = my_db_exec($con, $sql);
					my_db_commit($con);
				}
			}
			my_db_close($con);
			
			if($err == false){
				header("Location: index.php");
				exit;
			}
		}
	}
	$tmpl = new Tmpl2( "templates/login.html" ) ;

	$tmpl->assign("token", my_make_token($_SERVER['SCRIPT_FILENAME']));

	if($err){
		$tmpl->assign_def("error");
	}
	$tmpl->flush();
?>
