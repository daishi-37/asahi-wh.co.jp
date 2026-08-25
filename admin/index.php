<?php
	require("../lib/common.php");
	
	if(checkAdminSession() == false){
		header("Location: login.php");
		exit;
	}elseif(isAdminPasswordChanged() == false){
		header("Location: pwchange.php");
		exit;
	}
	
	clearSession('admin_dl');
	clearSession('admin_news');
	clearSession('admin_news_edit');
	clearSession('admin_recruit');
	$tmpl = new Tmpl2( "templates/index.html" ) ;
	$tmpl->flush();
?>
