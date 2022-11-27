<?php
class Auth {
	
	var $users = array();
	var $anyValidUser = false;
	var $allowAll = false;
	var $auth;
	var $authenticated = false;
	
	function __construct($authFile) {
		$this->auth = UcamWebauth::getConfiguredAuth();
		
		$fileContents = @file_get_contents(dirname(__FILE__) . "/../permissions/" . $authFile);
		
		if ($fileContents) {
			foreach (explode(" ", $fileContents) as $user) {
				$this->users[] = trim($user);
			}
			if (sizeof($this->users) === 1) {
				if ($this->users[0] == "#valid-user") {
					$this->anyValidUser = true;
				}
				
				if ($this->users[0] == "#no-auth") {
					$this->allowAll = true;
				}
			} 
		}
	}
	
	/**
	 * Determine if authentication has been performed, triggering it if it hasn't.
	 * If false, determine error from getMessage().
	 * 
	 * @return boolean Indicates if authentication was completed and if it completed successfully.
	 */
	function authenticate() {
		if (!$this->allowAll) {
			//$this->auth->authenticate() returns true if the raven process is complete or not
			//irrespective of success hence the requirement to and with success
			return $this->auth->authenticate() && $this->auth->success();
		}
		return true;
	}
	

	/**
	 * Determine if authentication has been performed successfully.
	 * If false, determine error from getMessage().
	 *
	 * @return boolean Indicates if authentication was completed and if it completed successfully.
	 */
	function authenticated() {
		if (!$this->allowAll) {
			$result = $this->auth->authenticate();
			if (!headers_sent()) {
				header_remove("Location");
				header("HTTP/1.1 200 OK");
			}
			return $result;
		}
		return true;
	}
	
	function isUserPermitted() {
		if ($this->allowAll) {
			return true;
		}
		if ($this->authenticated()) {
			if ($this->anyValidUser) {
				return true;
			}
			$principal = $this->getPrincipal();
			foreach ($this->users as $user) {
				if ($user == $principal) {
					return true;
				}
			}
			return false;
		}
		return false;
	}
	
	function getMessage() {
		return $this->auth->msg();
	}
	
	// function getCookieName() {
	// 	return $this->auth->get_cookie_name();
	// }
	
	function getPrincipal() {
		if ($this->allowAll) {
			return "";
		}
		else {
			return $this->auth->principal();
		}
	}
}
