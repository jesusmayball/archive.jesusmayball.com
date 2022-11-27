<?php

class DummyWebauth {

	public static function ravenAuthenticate() {
		return new DummyWebauth();
	}
	
	function get_cookie_name() {
		return "Ucam-Webauth-Dummy-Cookie";
	}
	
	function principal() {
		return "pc410";
	}
}