<?php

if (isset($_POST["ldap_crsid"])) {
	echo json_encode(LDAP::getLookupInfo($_POST["ldap_crsid"]));
}

class LDAP
{
	static function isInstalled()
	{
		return function_exists("ldap_connect");
	}

	static function getLookupInfo($uid)
	{

		if (LDAP::isInstalled()) {
			$ds = ldap_connect("ldaps://ldap.lookup.cam.ac.uk");  // must be a valid LDAP server!

			if (!$ds) {
				return array("error" => "unable to connect to ldap server");
			}

			$r = ldap_bind($ds);
			$user_lookup = addslashes($uid);
			$sr = ldap_search($ds, "ou=people, o=University Of Cambridge,dc=cam, dc=ac, dc=uk", "uid=$user_lookup");
			$info = ldap_get_entries($ds, $sr);
			ldap_close($ds);


			if (!$info[0]) {
				return array("error" => "no ldap results");
			}

			$college = $info[0]["ou"];
			if ($college["count"] > 1) {
				for ($i = 0; $i < $college["count"]; $i++) {
					if (strpos($college[$i], "College") !== false) {
						$college = explode(" -", $college[$i]);
						$college = $college[0];
					}
				}
			} else {
				$college = explode(" -", $college[0]);
				$college = $college[0];
			}
			$last_name = $info[0]["sn"][0];
			$name = explode(" ", $info[0]["cn"][0]);
			if (preg_match('/(Dr|Dr\.|Prof\.|Sir|Lady|Rev\.)/i', $name[0])) {
				$first_name = $name[1];
				$title = explode(".", $name[0]);
				$title = $title[0];
			} else {
				$first_name = $name[0];
				$title = "Mr";
			}


			return array("title" => $title, "first_name" => $first_name, "last_name" => $last_name, "college" => $college);
		} else {
			return array("error" => "ldap functions unavailable");
		}
	}
}
