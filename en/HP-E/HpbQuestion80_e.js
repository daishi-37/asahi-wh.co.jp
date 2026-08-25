//
//  Licensed Materials - Property of IBM
//  5724D51
//  (C) Copyright IBM Corp. 1995, 2003 All Rights Reserved.
//
/*
■ イベントで呼び出す関数

HpbELQInit()	- BODY の onload で呼び出す
HpbELQCheck()	- 送信ボタンで呼び出す
HpbELQReset()	- リセットボタンで呼び出す

*/

//
// GLOBAL DEFINITIONS
//
var HpbELQObj = null; // アンケート処理オブジェクトのインスタンス
var HpbELQ_FormName  = "HPB_ELQ_QUESTION"; // アンケートFORMの名前
var HpbELQ_SubmitBtn = "HPB_ELQ_SUBMIT";   // 「送信」INPUTボタン名
var HpbELQMsg_NoData = "アンケート用のデータが定義されていません。\n回答は送信できません。";
var HpbELQMsg_OK            = "";
var HpbELQMsg_AnsNotDefined = "回答が定義されていません。";
var HpbELQMsg_AnsMissing    = "Please fill the necessary item.";

//
// EXPORT FUNCTIONS
//
function HpbELQInit()
{
	if(typeof HpbELQData == "object"){
		HpbELQObj = new ELQuestionnaire(HpbELQData);
	} else {
		alert(HpbELQMsg_NoData);	// No Data
	}
}

function HpbELQCheck()
{
	if(HpbELQObj != null){
		return HpbELQObj.doCheck();
	} else {
		alert(HpbELQMsg_NoData);	// No Data
		return false;
	}
}

function HpbELQReset()
{
	if(HpbELQObj != null){
		return HpbELQObj.doReset();
	}
}

//
// OBJECT
//
function ELQuestionnaire(data)
{
	var missingElm;
	// Attributes
	this.data = data;

	this.doCheck = function()
	{
		var err_msg = HpbELQMsg_OK;
		var nQ = this.data.questions.length;
		missingElm = null;
		for(var i=0; i<nQ; i++){
			var objQ = this.data.questions[i];
			if (objQ.mandatory == true) {
				var id = this.data.idPrefix + (i+1).toString();
				var func = eval("this.checkEach" + objQ.type);
				if(typeof func == "function"){
					var msg_each = func(this, objQ, id);
					if (msg_each != HpbELQMsg_OK) {
						var item = this.getQ(id + "_head");
						if (item != null && typeof item.innerHTML != "undefined")
							item = item.innerHTML;
						else
							item = id;
						err_msg += (msg_each + ": " + item + "\n");
					}
				} else {
					alert("回答を検査する関数がありません：checkEach" + objQ.type);
					return false;
				}
			}
		}
		if (err_msg == HpbELQMsg_OK) {
			// 送信ボタンを無効に
			this.enableFormItem(HpbELQ_SubmitBtn, false);
			return true;
		} else {
			alert(err_msg);
			// 最初の未回答項目にフォーカスをあてる
			if (missingElm != null) missingElm.focus();
			return false;
		}
	}

	this.doReset = function()
	{
		this.enableFormItem(HpbELQ_SubmitBtn, true);
	}

	this.getQ = function(id)
	{
		if( eval("typeof "+id) == "undefined" )
			return null;
		return eval(id);
	}

	this.getFormItem = function(id)
	{
		return this.getQ(HpbELQ_FormName + "." + id);
	}

	this.enableFormItem = function(name, f)
	{
		var item = this.getFormItem(name);
		if (item != null)
			item.disabled = !f;
	}

	//-------------------
	// check mandatories
	this.checkEachSelection = function(doc, obj, id)
	{
		if(obj.single){
			// Single Selection
			var ansObj = doc.getFormItem(id);
			if (ansObj != null) {
				var count = 0;
				for(var i=0; i<ansObj.length; i++) {
					if(ansObj[i].checked)
						count++;
				}
				if (count == 1)
					return HpbELQMsg_OK;	//ok
				else {
					if (missingElm == null) missingElm = ansObj[0];
					return HpbELQMsg_AnsMissing;
				}
			} else
				return HpbELQMsg_AnsMissing;
		} else {
			// Multiple Selection
			var count = 0;
			for(var i=1; i<=obj.n_selection; i++){
				var ans = doc.getFormItem(id + "_" + i);
				if (ans != null) {
					if (ans.checked)
						count++;
				} else
					return HpbELQMsg_AnsNotDefined;
			}
			if (count > 0)
				return HpbELQMsg_OK;	//ok
			else {
				if (missingElm == null) missingElm = doc.getFormItem(id + "_1");
				return HpbELQMsg_AnsMissing;
			}
		}
	}

	this.checkEachDescription = function(doc, obj, id)
	{
		var ans = doc.getFormItem(id);
		if (ans != null) {
			if (ans.value != "")
				return HpbELQMsg_OK;	//ok
			else {
				if (missingElm == null) missingElm = ans;
				return HpbELQMsg_AnsMissing;
			}
		} else
			return HpbELQMsg_AnsNotDefined;
	}

}

