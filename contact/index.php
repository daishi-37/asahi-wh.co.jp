<?php
	require_once("../lib/common.php");
	
	function add_error(&$error, $mess){
		if(empty($error)){
			$error = $mess;
		}
		else{
			$error = $error . "<br>" . $mess;
		}
	}

	if(isset($_POST['token']) && my_check_token($_SERVER['SCRIPT_FILENAME']) == true){

		$error = '';

		//お問い合わせ種別
		$category = htmlspecialchars($_POST["category"]);
		$category_arr = explode(";", $category);
		if($category == "" || count($category_arr) != 2 || (int)$category_arr[0] < 1 || (int)$category_arr[0] > 6){
			add_error($error, "お問い合わせ種別を選択してください。");
		}
		$_SESSION['contact']['category'] = $category;

		//ご氏名
		$name = htmlspecialchars(trim($_POST["name"]));
		$name = mb_convert_kana($name, "KV");
		if(empty($name)){
			add_error($error, "ご氏名を入力してください。");
		}
		$_SESSION['contact']['name'] = $name;

		//会社名
		$company = htmlspecialchars(trim($_POST["company"]));
		$company = mb_convert_kana($company, "KV");
		$_SESSION['contact']['company'] = $company;

		//郵便番号
		$postcode = htmlspecialchars(trim($_POST["postcode"]));
		$postcode = mb_convert_kana($postcode, "n");
		if(empty($postcode)){
			add_error($error, "郵便番号を入力してください。");
		}
		elseif(preg_match("/^\d{3}-\d{4}$/", $postcode) != 1){
			add_error($error, "郵便番号をxxx-xxxxの形式で入力してください。");
		}
		$_SESSION['contact']['postcode'] = $postcode;
		
		//都道府県
		$pref = htmlspecialchars($_POST["pref"]);
		$pref_arr = explode(";", $pref);
		if(empty($pref)){
			add_error($error, "都道府県を選択してください。");
		}
		elseif(count($pref_arr) != 2 || $pref_arr[0] < 1 || $pref_arr[0] > 48){
			add_error($error, "都道府県の値が不正です。");
		}
		$_SESSION['contact']['pref'] = $pref;
	
		//市町村・番地・ビル名
		$address = htmlspecialchars(trim($_POST["address"]));
		$address = mb_convert_kana($address, "KV");
		if(empty($address)){
			add_error($error, "市町村・番地・ビル名を入力してください。");
		}
		$_SESSION['contact']['address'] = $address;
	
		//ご担当部署
		$department = htmlspecialchars(trim($_POST["department"]));
		$department = mb_convert_kana($department, "KV");
		$_SESSION['contact']['department'] = $department;

		//メールアドレス
		$email = mb_convert_kana(trim($_POST["email"]), "a");
		$email = htmlspecialchars($email);
		if(empty($email)){
			add_error($error, "メールアドレスを入力してください。");
		}
		elseif(mb_detect_encoding($email, "auto") != "ASCII"){
			add_error($error, "メールアドレスは半角で入力してください。");
		}
		elseif(preg_match("/^[^@]+@[^.@]+\..+/", $email) == 0 || strstr($email, ',') != false){
			add_error($error, "メールアドレスに誤りがあります。");
		}
		$_SESSION['contact']['email'] = $email;

		//電話番号
		$telephone1 = htmlspecialchars(trim($_POST["telephone1"]));
		$telephone2 = htmlspecialchars(trim($_POST["telephone2"]));
		$telephone3 = htmlspecialchars(trim($_POST["telephone3"]));
		$telephone1 = mb_convert_kana($telephone1, "n");
		$telephone2 = mb_convert_kana($telephone2, "n");
		$telephone3 = mb_convert_kana($telephone3, "n");
		if(empty($telephone1) || empty($telephone2) || empty($telephone3)){
			add_error($error, "電話番号を入力してください。");
		}
		elseif(preg_match("/^0[0-9]{1,4}$/", $telephone1) != 1
		|| preg_match("/^[0-9]{1,4}$/", $telephone2) != 1
		|| preg_match("/^[0-9]{3,4}$/", $telephone3) != 1
		){
			add_error($error, "電話番号を半角数字で入力してください。");
		}
		$_SESSION['contact']['telephone1'] = $telephone1;
		$_SESSION['contact']['telephone2'] = $telephone2;
		$_SESSION['contact']['telephone3'] = $telephone3;
	
		//問い合わせ内容
		$content = htmlspecialchars(trim($_POST["content"]));
		$content = mb_convert_kana($content, "KV");
		if(empty($content)){
			add_error($error, "問い合わせ内容を入力してください。");
		}
		elseif(mb_strlen($content) > 1000){
			add_error($error, "問い合わせ内容は１０００文字以内で入力してください。");
		}
		$_SESSION['contact']['content'] = $content;

		//個人情報について
		$terms = htmlspecialchars($_POST["terms"]);
		$terms_arr = explode(";", $terms);
		if($terms == "" || count($terms_arr) != 2 || (int)$terms_arr[0] != 1){
			add_error($error, "「個人情報の取扱いについて」 を必ず確認いただき「同意する」にチェックをいれてください。");
		}
		$_SESSION['contact']['terms'] = $terms;

		if(!empty($error)){
			$_SESSION['contact']['error'] = $error;
			header('Location: index.php');
			exit;
		}
		unset($_SESSION['contact']['error']);
		unset($_SESSION['contact']['hash']);
		$_SESSION['contact']['hash'] = md5(serialize($_SESSION['contact']) . session_id() . "mAgic");
		header('Location: confirm.php');
		exit;
	}
	if(isset($_SESSION['contact']) == false){	//登録開始？
		$_SESSION['contact']['category'] = '';	//お問い合わせ種別
		$_SESSION['contact']['name'] = '';	//ご氏名
		$_SESSION['contact']['company'] = '';	//会社名
		$_SESSION['contact']['postcode'] = '';	//郵便番号
		$_SESSION['contact']['pref'] = '';	//都道府県
		$_SESSION['contact']['address'] = '';	//市町村・番地・ビル名
		$_SESSION['contact']['department'] = '';	//ご担当部署
		$_SESSION['contact']['email'] = '';	//メールアドレス
		$_SESSION['contact']['telephone1'] = '';	//ご連絡先
		$_SESSION['contact']['telephone2'] = '';	//ご連絡先
		$_SESSION['contact']['telephone3'] = '';	//ご連絡先
		$_SESSION['contact']['content'] = '';	//問い合わせ内容
		$_SESSION['contact']['terms'] = '';	//ご連絡先
		$_SESSION['contact']['error'] = '';	//エラーメッセージ
	}

	$tmpl = new Tmpl2("memory");
	$tmpl->setmem(parseSSI(file_get_contents("templates/index.html"), dirname(__FILE__)));

	$tmpl->assign("token", my_make_token($_SERVER['SCRIPT_FILENAME']));

	if(!empty($_SESSION['contact']['error'])){
		$tmpl->assign_def("error");
		$tmpl->assign("error", $_SESSION['contact']['error']);
	}

	$warr = explode(";", $_SESSION['contact']['category']);
	$tmpl->assign("category{$warr[0]}_checked", " checked");

	$tmpl->assign("name", $_SESSION['contact']['name']);
	$tmpl->assign("company", $_SESSION['contact']["company"]);
	$tmpl->assign("postcode", $_SESSION['contact']["postcode"]);

	$warr = explode(";", $_SESSION['contact']['pref']);
	$tmpl->assign("pref{$warr[0]}_selected", " selected");
	
	$tmpl->assign("address", $_SESSION['contact']["address"]);
	$tmpl->assign("department", $_SESSION['contact']["department"]);
	$tmpl->assign("email", $_SESSION['contact']["email"]);
	$tmpl->assign("telephone1", $_SESSION['contact']["telephone1"]);
	$tmpl->assign("telephone2", $_SESSION['contact']["telephone2"]);
	$tmpl->assign("telephone3", $_SESSION['contact']["telephone3"]);
	$tmpl->assign("content", $_SESSION['contact']["content"]);
	
	$warr = explode(";", $_SESSION['contact']['terms']);
	$tmpl->assign("terms{$warr[0]}_checked", " checked");

	$tmpl->flush();
	exit;
?>
