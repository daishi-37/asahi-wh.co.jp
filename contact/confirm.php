<?php
	require_once("../lib/common.php");
	
	function error_exit($error){
		unset($_SESSION['contact']['hash']);
		$_SESSION['contact']['error'] = $error;
		header("Location: index.php");
		exit;
	}

	if(isset($_SESSION['contact']['hash']) === false){
		header("Location: index.php");
		exit;
	}
	$hash = $_SESSION['contact']['hash'];
	unset($_SESSION['contact']['hash']);
	$chk = md5(serialize($_SESSION['contact']) . session_id() . "mAgic");
	if(strcmp($hash, $chk)){
		header("Location: index.php");
		exit;
	}
	$_SESSION['contact']['hash'] = $hash;

	if(isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true){

		mb_language("uni");
		mb_internal_encoding('UTF-8');
			
		$mime_boundary=md5(time());
		
		$arr = explode(";", $_SESSION['contact']['category']);
		$to = $g_mail_send_to[$arr[0]];
		$from = CONTACT_MAIL_SEND_FROM;
		$pos = strpos($from, "<");
		if($pos !== false){
			$addr = substr($from, $pos);
			$name = mb_encode_mimeheader(substr($from, 0, $pos));
			$from = $name . $addr;
			if(preg_match("/^<(.+)>$/", $addr, $matches)){
				$sender = $matches[1];
			}
			else{
				$sender = $addr;
			}
		}
		else{
			$sender = $from;
		}
		
		$tmpl = new Tmpl2( "templates/mail_tanto.template" ) ;
		$arr = explode(';', $_SESSION['contact']['category']);
		$tmpl->assign('category', $arr[1]);
		$tmpl->assign('name', $_SESSION['contact']['name']);
		$tmpl->assign('company', $_SESSION['contact']['company']);
		$tmpl->assign('postcode', $_SESSION['contact']['postcode']);
		$arr = explode(';', $_SESSION['contact']['pref']);
		$tmpl->assign('pref', $arr[1]);
		$tmpl->assign('address', $_SESSION['contact']['address']);
		if(!empty($_SESSION['contact']['department'])){
			$tmpl->assign_def('department');
			$tmpl->assign('department', $_SESSION['contact']['department']);
		}
		$tmpl->assign('email', $_SESSION['contact']['email']);
		$tmpl->assign('telephone1', $_SESSION['contact']['telephone1']);
		$tmpl->assign('telephone2', $_SESSION['contact']['telephone2']);
		$tmpl->assign('telephone3', $_SESSION['contact']['telephone3']);
		$tmpl->assign('content', $_SESSION['contact']['content']);
		$body = $tmpl->flush(1);
		unset($tmpl);
		
		$headers = "From: {$from}\nReply-to: {$_SESSION['contact']['email']}\n";
		if(!mb_send_mail($to, CONTACT_MAIL_SUBJECT, $body, $headers)){
			error_exit('メール送信エラーが発生しました。');
		}
		
		unset($_SESSION['contact']);
		
		header('Location: finish.php');
		exit;
	}
	
	$tmpl = new Tmpl2("memory");
	$tmpl->setmem(parseSSI(file_get_contents("templates/confirm.html"), dirname(__FILE__)));

	$tmpl->assign('token', my_make_token($_SERVER['SCRIPT_FILENAME']));

	$arr = explode(';', $_SESSION['contact']['category']);
	$tmpl->assign('category', htmlspecialchars($arr[1]));

	$tmpl->assign('name', htmlspecialchars($_SESSION['contact']['name']));

	$tmpl->assign('company', htmlspecialchars($_SESSION['contact']['company']));

	$tmpl->assign('postcode', htmlspecialchars($_SESSION['contact']['postcode']));

	$arr = explode(';', $_SESSION['contact']['pref']);
	$tmpl->assign('pref', $arr[1]);

	$tmpl->assign('address', htmlspecialchars($_SESSION['contact']['address']));

	$tmpl->assign('department', htmlspecialchars($_SESSION['contact']['department']));

	$tmpl->assign('email', htmlspecialchars($_SESSION['contact']['email']));

	$tmpl->assign('telephone1', htmlspecialchars($_SESSION['contact']['telephone1']));
	$tmpl->assign('telephone2', htmlspecialchars($_SESSION['contact']['telephone2']));
	$tmpl->assign('telephone3', htmlspecialchars($_SESSION['contact']['telephone3']));

	$tmpl->assign('content', nl2br(htmlspecialchars($_SESSION['contact']['content'])));

	$arr = explode(';', $_SESSION['contact']['terms']);
	$tmpl->assign('terms', $arr[1]);
	
	$tmpl->flush();
	exit;
?>
