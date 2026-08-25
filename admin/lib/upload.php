<?php
	define("NO_TYPE", true);
	define("NO_REFERER", true);
	require_once("../../lib/common.php");
	
	if(checkAdminSession() == false){
		exit;
	}
	
//error_log(print_r($_FILES, true));
	if(empty($_FILES["file"]["name"])) exit;
	
	$result = array();
	
	if($_FILES["file"]["error"] != UPLOAD_ERR_OK)
	{
		$result['status'] = 'fail';
		switch($_FILES["file"]["error"]) {
			case UPLOAD_ERR_INI_SIZE:
				$size = ini_get('upload_max_filesize');
				if(is_numeric($size))
					$size = number_format($size) . "バイト";
				$result['error'] = "アップロードされたファイルは、${size}を超えています。";
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$size = number_format(htmlspecialchars($_POST['MAX_FILE_SIZE'], ENT_QUOTES | ENT_XHTML, 'UTF-8'));
				$result['error'] = "アップロードされたファイルは、${size}バイトを超えています。";
				break;
			case UPLOAD_ERR_PARTIAL:
				$result['error'] = "アップロードされたファイルは一部のみしかアップロードされていません。";
				break;
			case UPLOAD_ERR_NO_FILE:
				$result['error'] = "ファイルはアップロードされませんでした。";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$result['error'] = "テンポラリフォルダがありません。";
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$result['error'] = "ディスクへの書き込みに失敗しました。";
				break;
			case UPLOAD_ERR_EXTENSION:
				$result['error'] = "ファイルのアップロードが中止されました。";
				break;
			default:
				$result['error'] = "未定義エラーが発生しました。CODE:" . $_FILES["file"]["error"];
				break;
		}
		$result['file_name'] = '';
	}
	else {
		$tempfile = TEMPPATH . "/" . basename($_FILES['file']["tmp_name"]);
		if(move_uploaded_file($_FILES['file']["tmp_name"], $tempfile)){
			$result['status'] = 'success';
			$result['error'] = '';
			$result['file_name'] = basename($tempfile);
		}
		else{
			$result['status'] = 'fail';
			$result['error'] = "リモートでファイルの移動に失敗しました。";
			$result['file_name'] = '';
		}
	}
	echo json_encode($result);
	exit;
?>
