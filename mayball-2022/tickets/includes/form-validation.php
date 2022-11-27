<?php

class FormValidation {
	static function validateString($input, $min, $max) {
		if ($input == null) {
			return false;
		}
	    if ((strlen($input) < $min) || (strlen($input) > $max)) {
	        return false;
	    } else {
	        return true;
	    }
	}
	
	static function validateCRSid($input) {
		if ($input == null) {
			return false;
		}
	    $crsidPattern = "/^([a-z]+[0-9]+)$/";
	    if (preg_match($crsidPattern, strtolower($input))) {
	        return FormValidation::validateString(strtolower($input), 1, 8);
	    } else {
	        return false;
	    }
	}
	
	static function validateTitle($input) {
		if ($input == null) {
			return false;
		}
	    $titlePattern = "/^(Mr|Mrs|Miss|Ms|Dr.|Prof.|Sir|Admiral|Captain)$/";
	    return preg_match($titlePattern, $input);
	}
	
	static function validateFirstOrLastName($input) {
	    return FormValidation::validateString($input, 1, 50);
	}
	
	static function validateAppID($input) {
		if ($input == null) {
			return false;
		}
	    $appPattern = "/^([0-9]{1,4})$/";
	    if (preg_match($appPattern, $input)) {
	        require_once dirname(__FILE__)."/database.php";
	        $db = Database::getInstance();
	        return $db->getAppInfo("app_id", $input);
	    }
	    else {
	    	return false;
	    }
	}
	
	static function validateHashCode($input) {
		if ($input == null) {
			return false;
		}
	    $hashPattern = "/^([A-F0-9]{5})$/";
	    return preg_match($hashPattern, strtoupper($input));
	}
	
	static function validatePhoneUK($input) {
		if ($input == null) {
			return false;
		}
	    //This reg ex allows for 5, 10 or 11 digit UK or university numbers.
	    //10 digits are allowed because there are a very few UK areas which use 10
	    //digits instead of 11. These always begin with 01, 05 or 08, so 10 digits
	    //only allowed for these starts. As the majority of numbers entered will be
	    //mobiles, this means 07s are forced to have 11 digits, preventing issues of
	    //people leaving off a digit by accident.
	    //$phonePattern = "/^(((\+?44|0)(((1|5|8)[0-9]{3})|[0-9]{5}))?[0-9]{5})$/";
	    //return preg_match($phonePattern, $input);
	    return is_numeric($input) && (strlen($input) > 4 && strlen($input) < 15);
	}
	
	static function validateEmail($input) {
		if ($input == null) {
			return false;
		}
	    $emailPattern = "/\w+@(\w+\.)+\w+/";
	    if (preg_match($emailPattern, $input)) {
	        return FormValidation::validateString($input, 1, 100);
	    } else {
	        return false;
	    }
	}
	
	static function validateAddress($input) {
	    return FormValidation::validateString($input, 1, 65500);
	}
	
	static function validatePostCode($input) {
	    return FormValidation::validateString($input, 1, 10);
	}
}
?>
