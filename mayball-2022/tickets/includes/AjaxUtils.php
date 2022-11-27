<?php

class AjaxUtils {
	static function errorMessage($message) {
		$errorArray = array("error" => array("message" => $message));
		echo json_encode($errorArray);
	}

	static function errorMessageWithArray($message, $array) {
		$errorArray = array("error" => array("message" => $message));
		foreach($array as $key => $value) {
			$errorArray["error"][$key] = $value;
		}
		echo json_encode($errorArray);
	}

	static function successMessage($message) {
		$successArray = array("success" => array("message" => $message));
		echo json_encode($successArray);
	}

	static function successMessageWithArray($message, $array) {
		$successArray = array("success" => array("message" => $message));
		foreach($array as $key => $value) {
			$successArray["success"][$key] = $value;
		}
		echo json_encode($successArray);
	}
}
?>