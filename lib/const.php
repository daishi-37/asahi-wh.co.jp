<?php
////////////////////////////////////////////////////////////////////////////////
//	
//	定数定義
	//************************
	// ＤＢ情報
	//************************
	if(strpos($_SERVER['SERVER_NAME'], 'test') !== false){
		define("DBSTRING",		"dbname=asahi-wh.test user=apache");		// DB接続文字列
		define("OUTPUTPATH",		"/home/homepage/data/test/output");			// アウトプットファイル
		define("TEMPPATH",		"/home/homepage/data/test/temp");			// 一時ファイルディレクトリ
	}
	else{
		define("DBSTRING",		"dbname=asahi-wh user=apache");		// DB接続文字列
		define("OUTPUTPATH",		"/home/homepage/data/prod/output");			// アウトプットファイル
		define("TEMPPATH",		"/home/homepage/data/prod/temp");			// 一時ファイルディレクトリ
	}
	define("ENCODE",		"UTF-8");											// クライアントエンコーディング
	define("CHARASET",		"UTF-8");										// キャラクターセット
	define("RETRY_MAX", "20");
	define("RETRY_GUARD", "30");
	define("MAGIC", "W5Ip3!di");
	define("CONTACT_MAIL_SEND_FROM", "inquiry@www.asahi-wh.co.jp");
	define("CONTACT_MAIL_SUBJECT", "お問い合わせ（公式サイトより）");
	define("WNEW_CATEGORY", "1;文書保管:2;営業倉庫:3;不動産賃貸:4;電子化:5;採用:6;お知らせ:7;システム:8;その他:9;NEWS:101;トランクルーム:102;松戸営業所:103;川崎営業所:104;板橋営業所:105;足立営業所:106;柏事業所:107;月島事業所");
	define("WNEW_CATEGORY_EN", "1;Document Storage:2;Public Warehouse:3;Real Estate Leasing:4;Digitization:5;Careers:6;Announcements:7;System:8;Other:9;NEWS:101;Self-Storage:102;Matsudo Sales Office:103;Kawasaki Sales Office:104;Itabashi Sales Office:105;Adachi Sales Office:106;Kashiwa Office:107;Tsukishima Office");
	define("WNEW_FIXED", "0;しない:1;する");
	define("WNEW_FORMAT", "");
	define("RECRUIT_BANNER", "0;非表示:1;表示");
	define("RECRUIT_PUBLISH", "0;非掲載:1;掲載中");
	$g_mail_send_to = array(
		1 => "eigyou-g@asahi-wh.co.jp",
		2 => "homelocker@asahi-wh.co.jp",
		3 => "fudousanbu1@asahi-wh.co.jp",
		4 => "fudousanbu1@asahi-wh.co.jp",
		5 => "privacy@asahi-wh.co.jp",
		6 => "webmaster@asahi-wh.co.jp");
	class lcrypt {
		var $proc;
		var $spec;
		var $pipes;
		var $dir;
		function lcrypt(){
			$this->spec = array(
				0 => array("pipe", "r"),
				1 => array("pipe", "w"),
				2 => array("pipe", "w")
			);
			$this->proc = false;
			$this->dir = 1;
		}
		function setEncrypt(){
			$this->dir = 1;
		}
		function setDecrypt(){
			$this->dir = 2;
		}
		function open(){
/*			if($this->dir == 1){
				$this->proc = proc_open(PWENCCMD, $this->spec, $this->pipes);
			}
			else{
				$this->proc = proc_open(PWDECCMD, $this->spec, $this->pipes);
			}
			if(is_resource($this->proc)){
				stream_set_write_buffer($this->pipes[0], 0);
				fputs($this->pipes[0], PWPHRASE . "\n");
			}
			else{
				error_log("cannot create the pipe with lcrypt!", 0);
				exit;
			}*/
		}
		function close(){
			if($this->proc !== false){
				fclose($this->pipes[0]);
				fclose($this->pipes[1]);
				fclose($this->pipes[2]);
				proc_close($this->proc);
			}
		}
		function doit($in){
/*			fputs($this->pipes[0], $in . "\n");
			$out = fgets($this->pipes[1], 1024);
			$out = rtrim($out);
			return $out;*/
			return $in;
		}
	}
?>
