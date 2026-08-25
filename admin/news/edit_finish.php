<?php
	require_once('../../lib/common.php');
	
	if(!checkAdminSession())
	{
		header("Location: ../login.php");
		exit;
	}
	
	$tmpl = new Tmpl2( "templates/edit_finish.html" ) ;
	$tmpl->flush();
	
	exit;
?>
