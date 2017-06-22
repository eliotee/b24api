<?php

class b24api{
	private $bitrix_domain ;
	private $bitrix_login;
	private $bitrix_password;
	private $bitrix_client_secret;
	private $bitrix_client_id;
	private $bitrix_scope = "department,crm,calendar,user,entity,task,tasks_extended,im,log,sonet_group";
	private $bitrix_token = "";
	private function postCurl($url, $postData, $header = false){
		$ch = curl_init();
     	curl_setopt($ch, CURLOPT_URL, $url);
     	if ($header == true){
     		curl_setopt($ch, CURLOPT_HEADER, TRUE);
     	}
    	
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt($ch,CURLOPT_USERAGENT , "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7");

    	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . "/cookie.txt");  
		curl_setopt($ch, CURLOPT_COOKIEFILE,  __DIR__ . "/cookie.txt"); 
    	$res = curl_exec($ch);
    	curl_close($ch);
    	return $res;
	}
	private function getCurl($url, $header = false){
		$ch = curl_init();
     	curl_setopt($ch, CURLOPT_URL, $url);

    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     	if ($header == true){
     		curl_setopt($ch, CURLOPT_HEADER, TRUE);
     	}
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_COOKIEJAR,  __DIR__ . "/cookie.txt");  
		curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . "/cookie.txt"); 
		$res = curl_exec($ch);
    	curl_close($ch);
    	return $res;




	}
	function getToken(){

		return $this->bitrix_token;
	}
	function __construct($bitrix_domain, $bitrix_login, $bitrix_password, $bitrix_redirect_uri, $bitrix_client_id, $bitrix_client_secret) {
    	$this->bitrix_domain = $bitrix_domain;
    	$this->bitrix_login = $bitrix_login;
    	$this->bitrix_password = $bitrix_password;
    	$this->bitrix_client_id = $bitrix_client_id;
    	$this->bitrix_client_secret = $bitrix_client_secret;
    	$this->postCurl('https://' . $bitrix_domain .  '/stream/index.php?login=yes', "AUTH_FORM=Y&TYPE=AUTH&backurl=%2Fauth%2F&USER_LOGIN=" . $bitrix_login . "&USER_PASSWORD=" . $bitrix_password);

    	$subject = $this->getCurl("https://" . $bitrix_domain . "/oauth/authorize/?client_id=" .
        $bitrix_client_id . "&response_type=code&redirect_uri=" . $bitrix_redirect_uri, true); 

    	$pattern = "#/?code=(.*)&state#";

    	preg_match($pattern, $subject, $matches);
    	$answer = $this->getCurl("https://" . $bitrix_domain . "/oauth/token/?client_id=" .
        $bitrix_client_id . "&grant_type=authorization_code&client_secret=" . $bitrix_client_secret .
        "&redirect_uri=" . $bitrix_redirect_uri . "&scope=" . $this->bitrix_scope . "&code=" . $matches[1]);
    	
    	$jsonanswer = json_decode($answer);

    	if (isset($jsonanswer->access_token)) {
        	$this->bitrix_token = $jsonanswer->access_token;
        	
        	return true;
    	} else {

        	return false;
    	}
}
	function GetList($bitrixName, $fields = array(), $start = 0) {

   		$result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/crm.".$bitrixName.".list?auth=" . $this->bitrix_token."&start=$start" . "&select=". http_build_query($fields));
      	$jsonanswer = json_decode($result);
       	return $jsonanswer;
	}
	function getUserFields($name){
   		$result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/crm." . $name . ".userfield.list?auth=" . $this->bitrix_token);
      	$jsonanswer = json_decode($result);
       	return $jsonanswer;

	}
	function getById($name, $id){
   		$result = $this->getCurl("https://" . $this->bitrix_domain . "/rest/crm." . $name . ".get?auth=" . $this->bitrix_token . '&id=' . $id);
   		
      	$jsonanswer = json_decode($result);

       	return $jsonanswer;

	}






}






?>