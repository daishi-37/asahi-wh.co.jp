<?php
	require_once('../../lib/common.php');
	
	if(!checkAdminSession())
	{
		header("Location: ../login.php");
		exit;
	}
	
	$tmpl = new Tmpl2( "templates/finish.html" ) ;
	$tmpl->flush();
	
	exit;
?>
