<?php

class curl{

	/* Username:password format string */
	private $credentials;

	/* Contains the last HTTP status code returned */
	private $http_status;

	/* Contains the last API call */
	private $last_api_call;

	protected $headers;

	public $data;
	public $json;

	public function auth($username, $password){
		$this->credentials = sprintf("%s:%s", $username, $password);
	}

	protected function APICall($api_url, $require_credentials = false, $http_post = false){
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $api_url);

		if ($require_credentials) {
			curl_setopt($curl_handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl_handle, CURLOPT_USERPWD, $this->credentials);
		}

		if ($http_post) {
			curl_setopt($curl_handle, CURLOPT_POST, true);
		}

		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl_handle, CURLOPT_HEADERFUNCTION, array(&$this,'readHeader'));
		//curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'HEAD');
		curl_setopt($curl_handle, CURLOPT_COOKIEFILE, 'cookie.txt');
		curl_setopt($curl_handle, CURLOPT_COOKIEJAR, 'cookie.txt');

		$this->data = curl_exec($curl_handle);
		$this->json = $this->getHeaders('X-Json');
		$this->json = is_array($this->json) ? $this->json[0] : $this->json;
		$this->http_status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
		$this->last_api_call = $api_url;

		curl_close($curl_handle);
		return $this->data;
	}

	private function readHeader($ch,$header){
		$this->count++;
		//debug($header);
		if (strlen($header) > 2) {
			list($key, $value) = explode(" ", rtrim($header, "\r\n"), 2);
			$key = rtrim($key, ':');
			if (!empty($this->headers[$key])) {
				if (is_array($this->headers[$key])){
					$this->headers[$key][] = $value;
				} else {
					$tmp = $this->headers[$key];
					$this->headers[$key] = array();
					$this->headers[$key][] = $tmp;
					$this->headers[$key][] = $value;
				}
			} else {
				$this->headers[$key] = $value;
			}
		}
		return strlen($header);
	}

	public function getHeaders($key=false){
		if(is_array($this->headers) && !empty($key)){
			return key_exists($key,$this->headers) ? $this->headers[$key] : null;
		}else{
			return $this->headers;
		}
	}

	public function lastStatusCode(){
		return $this->http_status;
	}

	public function lastAPICall(){
		return $this->last_api_call;
	}

	public function lastResponse(){
		return $this->data;
	}
}

?>