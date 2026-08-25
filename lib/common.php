<?php
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
	ini_set("display_errors", "0");
	if(function_exists("date_default_timezone_set")){
		date_default_timezone_set("Asia/Tokyo");
	}
	if(defined("NO_SESSION") == false)
	{
		ini_set("session.use_cookies", "1");
		ini_set("session.use_only_cookies", "1");
		if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "")
		{
			session_set_cookie_params(0, "/", null, true);
		}
		else
		{
			session_set_cookie_params(0, "/", null, false);
		}
		if(defined("SESSION_PRIVATE") == false)
		{
			session_cache_limiter("nocache");
		}
		else
		{
			session_cache_limiter("private_no_expire");
		}
		if(preg_match('/\/admin\//', $_SERVER['PHP_SELF']) == 1)
		{
			session_name("PHPSESSIDA");
			$admin = true;
		}
		else
		{
			session_name("PHPSESSIDU");
			$admin = false;
		}
		session_start();
	}
	else
	{
		header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");
	}
	require_once("tmpl2.class.php");
	require_once("const.php");
	require_once("databaselib.php");
	if(defined("NO_TYPE") == false){
		header("Content-type: text/html;charset=utf-8");
	}
function parseSSI($html, $base)
{
	while(preg_match("/<!--#include virtual=\"([^\"]*)\" -->/",$html, $matches)){
		$path = $matches[1];
		if(substr($path, 0, 1) != '/'){
			$path = '/' . $path;
		}
		$contents = file_get_contents($base . $path);
		if($contents === false){
			printf("ERROR file_get_contents(%s)", $base . $path);
			exit;
		}
		$html = str_replace($matches[0], $contents, $html);
	}
	
	return $html;
}
function checkURL($address)
{
	global $error;
	if(preg_match('#^(http|https|ftp)://.+#', $address) != 1){
		$error .= "URLが不正です。</br>";
	}
}
function checkMailAddress($vname, $jname)
{
	$email = $vname;
	global $$email, $error;
	$$email = mb_convert_kana(trim($_POST[$email]), "a", "UTF-8");
	if($$email != ""){
		$$email = strtolower($$email);
		if(mb_detect_encoding($$email, "ASCII,UTF-8", true) != "ASCII"){
			$error .= "{$jname}は半角で入力してください。</br>";
		}
		else if(preg_match("/^[^@]+@[^.@]+\..+/", $$email) == false || strstr($$email, ',') != 1){
			$error .= "{$jname}に誤りがあります。</br>";
		}
	}
}
function checkDateValue($prefix, $name, $required = true)
{
	$date = $prefix . "_date";
	global $$date, $error;
	$$date = mb_convert_kana(trim($_POST[$date]), "n", "UTF-8");
	if($$date == ""){
		if($required){
			$error .= "{$name}を入力してください。</br>";
		}
		return;
	}
	else if(preg_match("/^(\d{4})-(\d{2})-(\d{2})/", $$date, $matches) != 1){
		$error .= "{$name}が不正です。</br>";
	}
	else{
		$chk = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
		if($chk === false || $chk === -1){
			$error .= "{$name}が不正です。</br>";
		}
		else if(date("Y", $chk) != $matches[1]
		|| date("n", $chk) != $matches[2]
		|| date("j", $chk) != $matches[3]){
			$error .= "{$name}が不正です。</br>";
		}
	}
}
function getItemName($list, $item, $idx=1)
{
	$arr = explode(":", $list);
	foreach($arr as $pair){
		$arr_pair = explode(";", $pair);
		if($arr_pair[0] == $item){
			return $arr_pair[$idx];
		}
	}
	return "";
}
function checkAdminSession($checkChanged = true)
{
	if(isset($_COOKIE["PHPSESSIDA"]))
	{
		if(isset($_SESSION["userdata"]) == false)
			return false;
		if($_SESSION["userdata"]["power"] != "0")
			return false;
		if($_SESSION["userdata"]["session"] != md5($_COOKIE["PHPSESSIDA"]))
			return false;
	}
	else
		return false;
	if($checkChanged) return isAdminPasswordChanged();
	return true;
}
function isAdminPasswordChanged(){
	if(strcmp($_SESSION['userdata']['changepassword'], '1') == 0){
		return true;
	}
	return false;
}
function my_make_token($seed = '')
{
	return hash('sha256', session_id() . 't0ken#' . $seed);
}
function my_check_token($seed = '')
{
	return hash_equals(my_make_token($seed), $_POST['token']);
}

if(function_exists("hash_equals") === false){
	function hash_equals($s1, $s2)
	{
		$len = strlen($s1);
		$ret = 0;
		for($i=0; $i<$len; $i++)
		{
			$ret |= ord(substr($s1, $i, 1)) ^ ord(substr($s2, $i, 1));
		}
		return $ret == 0;
	}
}
function fromPostData($name)
{
	return (isset($_POST[$name]))? trim($_POST[$name]) : '';
}
function fromPostDataInt($name)
{
	return (isset($_POST[$name]))? intval(trim($_POST[$name])) : '';
}
function fromPostDataArray($name)
{
	return (isset($_POST[$name]))? $_POST[$name] : array();
}
function fromSession($group, $name)
{
	return (isset($_SESSION[$group][$name]))? $_SESSION[$group][$name] : '';
}
function fromSessionArray($group, $name)
{
	return (isset($_SESSION[$group][$name]))? $_SESSION[$group][$name] : array();
}
function setSessionFromVal($group, $name, $val)
{
	$_SESSION[$group][$name] = (isset($val))? $val : '';
}
function clearSession($group, $name = '')
{
	if($name == '')
		unset($_SESSION[$group]);
	else
		unset($_SESSION[$group][$name]);
}
function get_mime_content_type($path)
{
	$mime = mime_content_type($path);
	if($mime == "application/pdf"){
		return array($mime, ".pdf");
	}
	elseif($mime == "image/png"){
		return array("image/png", ".png");
	}
	elseif($mime == "image/jpeg"){
		return array("image/jpeg", ".jpg");
	}
	elseif($mime == "image/gif"){
		return array("image/gif", ".gif");
	}
	return array('','');
}
