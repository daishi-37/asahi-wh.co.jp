<?php
	require("../lib/common.php");
	
	if(isset($_COOKIE["PHPSESSIDA"])){
		if(isset($_SESSION["userdata"]) == false){
			header("Location: login.php");
			exit;
		}
		
		$con = my_db_connect();
		$sql = sprintf("delete from t_login_cache where sid = '%s'", my_db_escape_string($_SESSION["userdata"]["id"]));
		$result = my_db_exec($con, $sql);
		my_db_free_result($result);
		my_db_close($con);
		
		// セッション変数を全て解除する
		$_SESSION = array();
		
		// セッションを切断するにはセッションクッキーも削除する。
		// Note: セッション情報だけでなくセッションを破壊する。
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
		
		// 最終的に、セッションを破壊する
		session_destroy();
	}
	header("Location: login.php");
	exit;
?>
