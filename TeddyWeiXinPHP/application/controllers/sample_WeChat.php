<?php
define("TOKEN", "΢����Կ");
define("ACCOUNT", "���΢�Ź����ʺ�");
define("PASSWORD", "���΢�Ź�������");
define("METHOD", "redis����file");

class weChatApi
{
	// ���캯��
	public function __construct(){
		// ��ȡcookie
		if(METHOD == 'redis'){
			$this->cookie = $this->redisCookie();
		}else{
			$this->cookie = $this->read('cookie.log');
		}
	}

	/**
	 * ����Ƿ��Ǻ��������(�ٷ�����)
	 * @return boolean
	 */
	public function checkSignature()
	{
		if($_GET){
			$signature = $_GET["signature"];
			$timestamp = $_GET["timestamp"];
			$nonce = $_GET["nonce"];

			$token = TOKEN;
			$tmpArr = array($token, $timestamp, $nonce);
			sort($tmpArr);
			$tmpStr = implode( $tmpArr );
			$tmpStr = sha1( $tmpStr );
			if( $tmpStr == $signature ){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	 * ��������Ϣ
	 * @param string $id �û���fakeid
	 * @param string $content ���͵�����
	 * @return [type] [description]
	 */
	public function send($id,$content)
	{
		$send_snoopy = new Snoopy;
		$post = array();
		$post['tofakeid'] = $id;
		$post['type'] = 1;
		$post['content'] = $content;
		$post['ajax'] = 1;
		$send_snoopy->referer = "http://mp.weixin.qq.com/cgi-bin/singlemsgpage?fromfakeid={$id}&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN";
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$submit = "http://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response";
		$send_snoopy->submit($submit,$post);
		return $send_snoopy->results;
	}


	/**
	 * ��������(������Ҫ���ó�ʱ)
	 * @param [type] $ids �û���fakeid����,���ŷָ�
	 * @param [type] $content [description]
	 * @return [type] [description]
	 */
	public function batSend($ids,$content)
	{
		$ids_array = explode(",", $ids);
		$result = array();
		foreach ($ids_array as $key => $value) {
			$send_snoopy = new Snoopy;
			$post = array();
			$post['type'] = 1;
			$post['content'] = $content;
			$post['ajax'] = 1;
			$send_snoopy->referer = "http://mp.weixin.qq.com/cgi-bin/singlemsgpage?fromfakeid={$value}&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN";
			$send_snoopy->rawheaders['Cookie']= $this->cookie;
			$submit = "http://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response";
			$post['tofakeid'] = $value;
			$send_snoopy->submit($submit,$post);
			$tmp = $send_snoopy->results;
			array_push($result, $tmp);
		}
		return $result;
	}

	/**
	 * ��ȡ�û�����Ϣ
	 * @param string $id �û���fakeid
	 * @return [type] [description]
	 */
	public function getInfo($id)
	{
		$send_snoopy = new Snoopy;
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$submit = "http://mp.weixin.qq.com/cgi-bin/getcontactinfo?t=ajax-getcontactinfo&lang=zh_CN&fakeid=".$id;
		$send_snoopy->submit($submit,array());
		$result = json_decode($send_snoopy->results,1);
		if(!$result){
			$this->login();
		}
		return $result;
	}

	/**
	 * ������������
	 * @param [type] $fromUsername [description]
	 * @param [type] $toUsername [description]
	 * @param [type] $msgType [description]
	 * @param [type] $content [description]
	 * @return [type] [description]
	 */
	public function sendText($fromUsername,$toUsername,$msgType,$content)
	{
		$textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<Content><![CDATA[%s]]></Content>
<FuncFlag>0</FuncFlag>
</xml>";
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $content);
		echo $resultStr;
	}

	/**
	 * ��������
	 * @return [type] [description]
	 */
	public function parseData(){
		$return = array();
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)){
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$postObj = json_encode($postObj);
			$postObj = json_decode($postObj,1);
			return $postObj;
		}else {
			return $return;
		}
	}

	/**
	 * ģ���¼��ȡcookie
	 * @return [type] [description]
	 */
	public function login($locate="file"){
		$snoopy = new Snoopy;
		$submit = "http://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN";
		$post["username"] = ACCOUNT;
		$post["pwd"] = md5(PASSWORD);
		$post["f"] = "json";
		$snoopy->submit($submit,$post);
		$cookie = '';
		foreach ($snoopy->headers as $key => $value) {
			$value = trim($value);
			if(strpos($value,'Set-Cookie: ') || strpos($value,'Set-Cookie: ')===0){
				$tmp = str_replace("Set-Cookie: ","",$value);
				$tmp = str_replace("Path=/","",$tmp);
				$cookie.=$tmp;
			}
		}
		if($locate == 'file'){
			$this->write("cookie.log",$cookie);
		}
		return $cookie;
	}

	public function redisCookie(){
		$redis = new Redis();
		$redis->pconnect('127.0.0.1', 6379);
		if ($redis->exists('cookie')) {
			return $redis->get('cookie');
		}else{
			$cookie = $this->login();
			$redis->setex('cookie', 600, $cookie);
			return $cookie;
		}
	}


	/**
	 * ������д���ļ�
	 * @param string $filename �ļ���
	 * @param string $content �ļ�����
	 * @return [type] [description]
	 */
	public function write($filename,$content){
		$fp= fopen("./data/".$filename,"w");
		fwrite($fp,$content);
		fclose($fp);
	}

	/**
	 * ��ȡ�ļ�����
	 * @param string $filename �ļ���
	 * @return [type] [description]
	 */
	public function read($filename){
		if(file_exists("./data/".$filename)){
			$data = '';
			$handle=fopen("./data/".$filename,'r');
			while (!feof($handle)){
				$data.=fgets($handle);
			}
			fclose($handle);
			if($data){
				$send_snoopy = new Snoopy;
				$send_snoopy->rawheaders['Cookie']= $data;
				$submit = "http://mp.weixin.qq.com/cgi-bin/getcontactinfo?t=ajax-getcontactinfo&lang=zh_CN&fakeid=";
				$send_snoopy->submit($submit,array());
				$result = json_decode($send_snoopy->results,1);
				if(!$result){
					return $this->login();
				}else{
					return $data;
				}
			}else{
				return $this->login();
			}
		}else{
			return $this->login();
		}
	}

	/**
	 * ��֤cookie����Ч��
	 * @return [type] [description]
	 */
	public function checkValid()
	{
		$send_snoopy = new Snoopy;
		$post = array();
		$submit = "http://mp.weixin.qq.com/cgi-bin/getregions?id=1017&t=ajax-getregions&lang=zh_CN";
		$send_snoopy->rawheaders['Cookie']= $this->cookie;
		$send_snoopy->submit($submit,$post);
		$result = $send_snoopy->results;
		if(json_decode($result,1)){
			return true;
		}else{
			return false;
		}
	}

}