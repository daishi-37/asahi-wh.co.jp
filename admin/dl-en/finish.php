<?php
	require("../../lib/common.php");
	
	if(checkAdminSession() == false){
		header("Location: ../login.php");
		exit;
	}elseif(isAdminPasswordChanged() == false){
		header("Location: ../pwchange.php");
		exit;
	}
	$tmpl = new Tmpl2( "templates/finish.html" ) ;
	$tmpl->flush();
?>
