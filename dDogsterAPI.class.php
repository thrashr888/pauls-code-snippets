<?php

class dDogsterAPI extends curl {

	function getPet($format, $id = NULL, $since = NULL) {
		$api_call = sprintf("/pets/%s.%s", $id, $format);
		return $this->call($api_call);
	}

	function getPets($format='json', $since = NULL) {
		$api_call = sprintf("/pets.%s", $format);
		return $this->call($api_call);
	}

	private function call($api_call){
		$api_call = sfConfig::get('app_dogster_api_url').$api_call.sprintf("?api_key=%s", urlencode(sfConfig::get('app_dogster_api_key')));
		$this->APICall($api_call,1);
		return json_decode($this->json);
	}

}
