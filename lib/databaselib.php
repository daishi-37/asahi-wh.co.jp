<?php
	function my_db_connect() {
		$con = pg_connect(DBSTRING);
		if($con == false){
			my_db_exit("データベースに接続できません。");
		}
		pg_exec($con, "set client_encoding to '" . ENCODE . "'");
		return $con;
	}
	function my_db_exec($con, $sql, $exit_on_error=true){
		$result = pg_exec($con, $sql);
		if($result === false){
			if($exit_on_error == true){
				my_db_exit( "システムエラー");
			}
		}
		return $result;
	}
	function my_db_query_params($con, $sql, $params, $exit_on_error=true){
		$result = pg_query_params($con, $sql, $params);
		if($result === false){
			if($exit_on_error == true){
				my_db_exit( "システムエラー");
			}
		}
		return $result;
	}
	function my_db_begin($con){
		pg_free_result(pg_exec($con, "begin"));
	}
	function my_db_commit($con){
		pg_free_result(pg_exec($con, "commit"));
	}
	function my_db_rollback($con){
		pg_free_result(pg_exec($con, "rollback"));
	}
	function my_db_fetch_array($result,$row=NULL,$attr=PGSQL_ASSOC){
		$arr =@pg_fetch_array($result,$row,$attr);
		if($arr == false){
			return false;
		}
		foreach($arr as $key => $value){
			$arr[$key] = $value;
		}
		return $arr;
	}
	function my_db_fetch_result($result, $row, $field){
		$ret = pg_fetch_result($result, $row, $field);
		return $ret;
	}
	function my_db_close($con)
	{
		pg_close($con);
	}
	function my_db_num_rows($result){
		return pg_num_rows($result);
	}
	function my_db_free_result($result){
		return pg_free_result($result);
	}
	function my_db_escape_string($str){
		return pg_escape_string($str);
	}
	function my_db_affected_rows($result)
	{
		return pg_affected_rows($result);
	}
	function my_db_exit($str)
	{
echo <<<END
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
{$str}
</body>
</html>
END;
		exit;
	}
?>
