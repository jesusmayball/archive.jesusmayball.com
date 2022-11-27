<?php

class Vulnerabilities {

	static function healthcheck() {
		$health = array();
		$res = Vulnerabilities::databasePresent();
		if ($res) {
			$health[] = $res;
		}
		$res = Vulnerabilities::databaseSecurity();
		if ($res) {
			$health[] = $res;
		}
		return $health;
	} 

	private static function databaseSecurity() {
		if (file_exists(dirname(__FILE__) . "/database_config.php")) {
			$perms = fileperms(dirname(__FILE__) . "/database_config.php");
			if (($perms & 0x0004)) {
				return "Database configuration is world readable. Remove <code>o+r</code> permissions on <code>includes/database_config.php</code> and consider changing database credentials";
			}
		}
		return false;
	}
	
	private static function databasePresent() {
		$var = dirname(__FILE__) . "/database_config.php";
		if (!file_exists(dirname(__FILE__) . "/database_config.php")) {
			return "Database configuration <code>includes/database_config.php</code> is missing";
		}
		return false;
	}
}
