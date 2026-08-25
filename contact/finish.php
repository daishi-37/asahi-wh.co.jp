<?php
	require_once("../lib/common.php");

	$tmpl = new Tmpl2("memory");
	$tmpl->setmem(parseSSI(file_get_contents("templates/finish.html"), dirname(__FILE__)));
	$tmpl->flush();
	exit;
?>