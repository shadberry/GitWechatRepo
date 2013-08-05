<?php
/**
 * wechat php test
 */

//define your token
define("TOKEN", "teddypublicstaticfinal");

class Wechat extends CI_Controller
{

	protected $service;

	function __construct()
	{
		parent::__construct();
		//init wechat model
		$this->load->model('Wechat_model');
	}

	public function receiveMsg()
	{
		log_message('debug', 'receiveMsg Called.');
		$echoStr = $_GET["echostr"];
		//valid signature , option
		if($this->checkSignature()){
			if (empty($echoStr)) {
				$this->responseMsg();
			} else {
				//return wechat token
				echo $echoStr;
				exit;
			}
		}
	}

	/**
	 * check signature is right or not
	 */
	private function checkSignature()
	{
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$token = TOKEN;
		log_message('debug', $signature);
		log_message('debug', $timestamp);
		log_message('debug', $nonce);
		log_message('debug', $token);
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		log_message('debug', $tmpStr);

		if( $tmpStr == $signature ){
			log_message('debug', 'return true');
			return true;
		}else{
			return false;
		}
	}

	/**
	 * record msg and response it
	 */
	private function responseMsg()
	{
		log_message('debug', 'responseMsg Called...');
		
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		log_message('debug', 'postStr is ' . $postStr);
//		$postStr = '<xml><ToUserName><![CDATA[gh_8ed4b3e6a9e6]]></ToUserName>
//<FromUserName><![CDATA[obB97jnP1KzHzrz3wSJI0tpC_3UY]]></FromUserName>
//<CreateTime>1375368362</CreateTime>
//<MsgType><![CDATA[text]]></MsgType>
//<Content><![CDATA[1]]></Content>
//<MsgId>5907162134743089232</MsgId>
//</xml>';
		//extract post data
		if (!empty($postStr)){
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			//获取微信消息的所有信息
			$msgData = array(
				'toUserName' => strval($postObj->ToUserName),
				'fromUserName' => strval($postObj->FromUserName),
				'createTime' => strval($postObj->CreateTime),
				'msgType' => strval($postObj->MsgType),
				'content' => strval($postObj->Content),
				'msgId' => strval($postObj->MsgId),
				'picUrl' => strval($postObj->PicUrl),
				'location_x' => strval($postObj->Location_x),
				'location_y' => strval($postObj->Location_y),
				'scale' => strval($postObj->Scale),
				'label' => strval($postObj->Label),
				'title' => strval($postObj->Title),
				'description' => strval($postObj->Description),
				'url' => strval($postObj->Url),
				'picUrl' => strval($postObj->PicUrl),
				'event' => strval($postObj->Event),
				'event' => strval($postObj->EventKey)
			);
			
			$this->Wechat_model->saveMsg($msgData);
			
			
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$msgType = $postObj->MsgType;
			$content = trim($postObj->Content);
			
			log_message('debug', 'toUserName is ' . $toUsername);
			
			
			$time = time();
			$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";             
			if(!empty( $content ))
			{
				$msgType = "text";
				$contentStr = "博主还在疯狂加班中...再等等...";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
				echo $resultStr;
			}else{
				echo "Input something...";
			}

		}
	}
}

?>