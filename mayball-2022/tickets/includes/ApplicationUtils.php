<?php

class ApplicationUtils {
	//Adding preceding 0s to an app ID less than 4 digits long
	static function appIDZeros($appID) {
	    $appIDstring = strval($appID);
	    while (strlen($appIDstring) < 4) {
	        $appIDstring = "0".$appIDstring;
	    }
	    return $appIDstring;
	}
	
	//According to guidelines on payee references, they must
	//be no longer than 18 characters,
	//contain only upper case characters,
	//and contain no spaces, slashes (/) or hyphens (-).
	//So this function creates one conforming to those guidelines:
	//Format: "A", followed by app ID, then last name and first name.
	//E.g. A0125JOHNSMITH
	static function makePaymentReference($appID, $firstName, $lastName) {
	    $appIDzeroed = ApplicationUtils::appIDZeros($appID);
	    $paymentReference = "A" . $appIDzeroed;
	    
	    $strippedFirstName = strtoupper(preg_replace('/[^a-zA-Z]/','',$firstName));
	    $strippedLastName = strtoupper(preg_replace('/[^a-zA-Z]/','',$lastName));
	    $charsLeft = 18 - strlen($paymentReference);
	    if (strlen($strippedLastName) > ($charsLeft - 1)) {
	        $strippedLastName = substr($strippedLastName, 0, $charsLeft-1);
	    }
	    $paymentReference .= $strippedLastName;
	    $charsLeft = 18 - strlen($paymentReference);
	    
	    if (strlen($strippedFirstName) > $charsLeft) {
	        $strippedFirstName = substr($strippedFirstName, 0, $charsLeft);
	    }
	    $paymentReference .= $strippedFirstName;
	    return $paymentReference;
	}
	
	static function generateHash() {
	    $hash = hash("sha512", time());
	    $start = rand(0, 122);
	    $code = substr(strtoupper($hash), $start, 5);
	    return $code;
	}
	
	static function uuid() {
    	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	        // 32 bits for "time_low"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
	
	        // 16 bits for "time_mid"
	        mt_rand( 0, 0xffff ),
	
	        // 16 bits for "time_hi_and_version",
	        // four most significant bits holds version number 4
	        mt_rand( 0, 0x0fff ) | 0x4000,
	
	        // 16 bits, 8 bits for "clk_seq_hi_res",
	        // 8 bits for "clk_seq_low",
	        // two most significant bits holds zero and one for variant DCE1.1
	        mt_rand( 0, 0x3fff ) | 0x8000,
	
	        // 48 bits for "node"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	    );
}
}

?>
