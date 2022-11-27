<?php

class Emails
{
	private static $db = null;
	private static $ticketTypes = null;

	static function init()
	{
		self::$db = Database::getInstance();
		self::$ticketTypes = self::$db->getAllTicketTypes(false, false, true, false);
	}

	static function generateMessageFromTokens($subjectTokens, $bodyTokens, $appOrTicketId, $appOrTicket, $additionalVariables)
	{
		$variableScope = array(self::getGlobalVariables());
		if ($additionalVariables != null) {
			foreach ($additionalVariables as $variableSet) {
				if ($variableSet != null) {
					array_push($variableScope, $variableSet);
				}
			}
		}
		if ($appOrTicketId !== 0) {
			if ($appOrTicket == 1) {
				$applicationVariables = self::getApplicationVariables($appOrTicketId);

				if (!$applicationVariables) {
					return array("result" => false, "message" => "Unable to generate application variables");
				}
				$primaryID = $applicationVariables["\$application->principalID"];

				$primaryVariables = self::getTicketVariablesWithPrefix("primary", $primaryID, null);

				if (!$primaryVariables) {
					return array("result" => false, "message" => "Unable to generate primary ticket variables");
				}
				array_push($variableScope, $applicationVariables);
				array_push($variableScope, $primaryVariables);
			} else {
				$ticketVariables = self::getTicketVariablesWithPrefix("ticket", $appOrTicketId, null);
				if (!$ticketVariables) {
					return array("result" => false, "message" => "Unable to generate ticket variables");
				}
				array_push($variableScope, $ticketVariables);
			}
		}
		$subjectMessage = self::populateMessage($subjectTokens, $variableScope, $appOrTicketId);
		if (!$subjectMessage["result"]) {
			return array("result" => false, "message" => "Fatal error with subject, " . $subjectMessage["message"]);
		}
		$bodyMessage = self::populateMessage($bodyTokens, $variableScope, $appOrTicketId);
		if (!$bodyMessage["result"]) {
			return array("result" => false, "message" => "Fatal error with body, " . $bodyMessage["message"]);
		}
		return array("result" => true, "subject" => $subjectMessage["message"], "body" => $bodyMessage["message"]);
	}

	static function generateMessage($messageName, $appOrTicketId, $additionalVariables)
	{
		$email = self::$db->getEmailByName($messageName);
		$appOrTicket = $email->appOrTicket;
		if (!$email) {
			//provide error
			return array("result" => false, "message" => "No email found by that name");
		}
		$subjectResult = self::staticValidation($email->subject, $appOrTicket);
		if (!$subjectResult["result"]) {
			//provide error
			return array("result" => false, "message" => "Static validation of subject failed");
		}
		$bodyResult = self::staticValidation($email->body, $appOrTicket);
		if (!$bodyResult["result"]) {
			//provide error
			return array("result" => false, "message" => "Static validation of body failed");
		}

		return self::generateMessageFromTokens($subjectResult["tokens"], $bodyResult["tokens"], $appOrTicketId, $appOrTicket, $additionalVariables);
	}

	static function sendEmail($recipient, $fromEmail, $fromTextAlias, $bcc, $subject, $body)
	{
		global $config;
		$mail = new PHPMailer();
		if ($config["email"]["smtp"]) {
			$mail->IsSMTP();
			$mail->Host = $config["email"]["smtp_host"];
			$mail->Port = $config["email"]["smtp_port"];
			if ($config["email"]["smtp_user"]) {
				$mail->SMTPAuth = true;
				$mail->Username = $config["email"]["smtp_user"];
				$mail->Password = $config["email"]["smtp_password"];
			}
			if ($config["email"]["smtp_security"]) {
				$mail->SMTPSecure = $config["email"]["smtp_security"];
			}
		}

		//TODO: clear this up pls
		$html_prefix = '<!doctype html>
                    <html>
                    <head>
                        <meta name="viewport" content="width=device-width">
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                        <title>' . $subject . '</title>
                        <style>
                        p.totals {
                            font-size: 1.1em;
                        }
                        h2 {
                            margin-top: 1.5em;
                        }
                    </style>
                    </head>

                    <body>';
		$html_suffix = '</body></html>';

		$mail->Body = $html_prefix . $body . $html_suffix;
		$mail->Subject = $subject;
		$mail->AddAddress($recipient);
		$mail->AddReplyTo($fromEmail);
		$mail->SetFrom($fromEmail, $fromTextAlias);
		$mail->IsHTML(true);
		if ($bcc != null) {
			error_log("BCC: " . $bcc . "\n", 3, "/var/tmp/my-errors.log");
			$mail->AddBCC($bcc);
		}
		$res = $mail->Send();
		if (!$res) {
			error_log($mail->ErrorInfo);
		}
		return $res;
	}

	/**
	 * Must only be called with tokens known to be valid.
	 * @param Countable|array $tokens
	 * @param array $initialScope
	 * @param unknown $appId
	 * @return Ambigous <string, unknown>
	 */
	private static function populateMessage($tokens, &$initialScope, $appId)
	{
		//Note, the appId will be the ticket ID
		$messageString = "";
		for ($i = 0; $i < sizeof($tokens); $i++) {
			$type = $tokens[$i]["type"];
			$value = $tokens[$i]["value"];
			if ($type == "text") {
				$messageString .= $value;
			} else if ($type == "variable") {
				$messageString .= self::getVariable($value, $initialScope);
			} else if ($type == "starteach") {
				$value = preg_replace('/\s+/xms', ' ', trim($value));
				$startToken = $i + 1;
				$j = $startToken;
				$depth = 0;
				$stillSearching = true;
				while ($stillSearching) {
					$typeOfJ = $tokens[$j]["type"];
					if ($typeOfJ == "starteach") {
						$depth++;
						$j++;
					} else if ($typeOfJ == "endeach") {
						if ($depth > 0) {
							$depth--;
							$j++;
						} else {
							$stillSearching = false;
						}
					} else {
						$j++;
					}
				}
				$length = $j - $startToken + 1;
				$i = $j;
				$application = self::$db->getAppInfo("app_id", $appId);
				if (!$application) {
					return array("result" => false, "message" => "Application with id $appId doesn't exist");
				}
				//If length is 1 then there was no content
				if ($length > 1) {
					$explodedValue = explode(" ", $value);
					$variableToBind = substr($explodedValue[3], 1);

					//process principal ticket
					array_push($initialScope, self::getTicketVariablesWithPrefix($variableToBind, 0, $application->principal));
					$res = self::populateMessage(array_slice($tokens, $startToken, $length), $initialScope, $appId);
					if (!$res["result"]) {
						//error
						return $res;
					}
					$messageString .= $res["message"];
					array_pop($initialScope);

					//process each other ticket
					foreach ($application->guests as $guest) {
						array_push($initialScope, self::getTicketVariablesWithPrefix($variableToBind, 0, $guest));
						$res = self::populateMessage(array_slice($tokens, $startToken, $length), $initialScope, $appId);
						if (!$res["result"]) {
							//error
							return $res;
						}
						$messageString .= $res["message"];
						array_pop($initialScope);
					}
				}
			}
		}
		return array("result" => true, "message" => $messageString);
	}

	private static function getVariable($variable, $scope)
	{
		foreach ($scope as $variableArray) {
			if (array_key_exists($variable, $variableArray)) {
				return $variableArray[$variable];
			}
		}
		return "";
	}

	static function staticValidation($string, $appOrTicket)
	{
		$stringChar = str_split($string);
		$len = sizeof($stringChar);

		$brackets = 0;
		$segmentStart = 0;
		$segmentEnd = 0;

		$processingInstructions = array();
		$openBrackets = false;
		$j = 0;
		for ($i = 0; $i < $len; $i++) {
			switch ($stringChar[$i]) {
				case '{':
					if ($i != $len - 1 && $stringChar[$i + 1] === '{') {
						if ($i > $segmentStart) {
							$processingInstructions[$j++] = array("value" => substr($string, $segmentStart, $i - $segmentStart), "start" => $segmentStart, "end" => $i, "type" => "text");
						}
						$openBrackets = true;
						$brackets++;
						$i++;
						$segmentStart = $i + 1;
					}
					break;
				case '}':
					if ($i != $len - 1 && $stringChar[$i + 1] === '}') {
						$brackets--;
						$segmentEnd = $i - 1;
						if ($openBrackets) {
							if ($segmentEnd > $segmentStart) {
								$processingInstructions[$j++] = array("value" => substr($string, $segmentStart, $segmentEnd - $segmentStart + 1), "start" => $segmentStart, "end" => $segmentEnd);
							}
						}
						$openBrackets = false;
						$i++;
						$segmentStart = $i + 1;
					}
					break;
				default:
					$segmentEnd = $i;
					break;
			}
		}

		if ($segmentStart < sizeof($stringChar) && !$openBrackets) {
			$processingInstructions[$j++] = array("value" => substr($string, $segmentStart, sizeof($stringChar) - $segmentStart + 1), "start" => $segmentStart, "end" => sizeof($stringChar), "type" => "text");
		}
		if ($brackets != 0) {
			return array("result" => false, "message" => "Invalid - Unclosed processing instruction");
		}

		$nestedLoops = 0;

		$variablesInScope = array("__HELPERARRAY__" => array());
		$variablesInScope["global"] = self::getGlobalVariableNames();
		if ($appOrTicket == 1) {
			$variablesInScope["application"] = self::getApplicationVariableNames();
			$variablesInScope["primary"] = self::getTicketVariablesNames("primary");
			$variablesInScope["nameChange"] = self::getNameChangeVariableNames();
			$variablesInScope["waitingList"] = self::getWaitingListVariableNames();
		} else {
			$variablesInScope["ticket"] = self::getTicketVariablesNames("ticket");
		}
		foreach ($processingInstructions as &$value) {
			if (!isset($value["type"])) {
				if (self::startsWith($value["value"], "\$")) {
					if (!self::variableInScope($variablesInScope, $value["value"])) {
						return array("result" => false, "message" => "Attempt to use unbound variable \"" . $value["value"] . "\"");
					}
					$value["type"] = "variable";
				} else {
					$ret = self::parseNonVariableProcessingInstruction($value["value"], $variablesInScope);
					if ($ret['result'] == 0) {
						return array("result" => false, "message" => $ret["message"]);
					} else {
						if ($ret['result'] == -1) {
							$value["type"] = "endeach";
						} else {
							$value["type"] = "starteach";
						}
						$nestedLoops += $ret['result'];
					}
				}
			}
		}
		if ($nestedLoops != 0) {
			return array("result" => false, "message" => "Invalid - Unclosed foreach instruction");
		}
		return array("result" => true, "message" => "Valid", "tokens" => $processingInstructions);
	}

	private static function startsWith($content, $searchTerm)
	{
		return !strncmp($content, $searchTerm, strlen($searchTerm));
	}

	private static function getApplicationVariableNames()
	{
		return array(
			"\$application->appID",
			"\$application->principalID",
			"\$application->tickets",
			"\$application->paymentReference",
			"\$application->totalCost",
			"\$application->totalToPay",
			"\$application->charity"
		);
	}

	private static function getApplicationVariables($appID)
	{
		if (intval($appID) == 0) {
			return false;
		}
		$app = self::$db->getAppInfo("app_id", $appID);
		if (!$app) {
			return false;
		}
		$appVars = array();
		$appVars["\$application->appID"] = ApplicationUtils::appIDZeros($app->app_id);
		$appVars["\$application->principalID"] = $app->principal_id;
		$appVars["\$application->tickets"] = sizeof($app->guests) + 1;
		$appVars["\$application->paymentReference"] = ApplicationUtils::makePaymentReference($app->app_id, $app->principal->first_name, $app->principal->last_name);
		$appVars["\$application->totalCost"] = $app->totalCost;
		$appVars["\$application->totalToPay"] = $app->totalPaid > $app->totalCost ? 0 : $app->totalCost - $app->totalPaid;
		$appVars["\$application->charity"] = $app->charity;
		return $appVars;
	}

	private static function getNameChangeVariableNames()
	{
		return array(
			"\$nameChange->changeID",
			"\$nameChange->appID",
			"\$nameChange->ticketHash",
			"\$nameChange->oldName",
			"\$nameChange->newName",
			"\$nameChange->ticketHash",
			"\$nameChange->newCRSid",
			"\$nameChange->newCollege",
			"\$nameChange->newPhone",
			"\$nameChange->submissionEmail",
			"\$nameChange->secret"
		);
	}

	private static function getWaitingListVariableNames()
	{
		return array(
			"\$waitingList->name",
			"\$waitingList->waitingListID",
			"\$waitingList->name",
			"\$waitingList->crsid",
			"\$waitingList->email",
			"\$waitingList->college",
			"\$waitingList->desiredTicketType",
			"\$waitingList->noOfTickets",
			"\$waitingList->phone"
		);
	}

	private static function getTicketVariablesNames($prefix)
	{
		$prefix = "\$" . $prefix;
		return array(
			$prefix . "->ticketID",
			$prefix . "->appID",
			$prefix . "->name",
			$prefix . "->crsid",
			$prefix . "->email",
			$prefix . "->college",
			$prefix . "->phone",
			$prefix . "->address",
			$prefix . "->hashCode",
			$prefix . "->ticketType",
			$prefix . "->price",
			$prefix . "->valid",
			$prefix . "->charity",
			$prefix . "->eticketURL",
			$prefix . "->appURL"
		);
	}

	private static function getTicketVariables($ticketID, $ticketOrNull)
	{
		global $config;
		if (intval($ticketID) == 0 && $ticketOrNull == null) {
			return false;
		}
		if ($ticketOrNull == null) {
			$ticket = self::$db->getTicketInfo("ticket_id", $ticketID);
		} else {
			$ticket = $ticketOrNull;
		}
		if (!$ticket) {
			return false;
		}

		$application = self::$db->getAppInfo("app_id", $ticket->app_id);

		$ticketVars = array();
		$ticketVars["->ticketID"] = $ticket->ticket_id;
		$ticketVars["->appID"] = $ticket->app_id;
		$fullname = $ticket->first_name . " " . $ticket->last_name;
		$ticketVars["->name"] = $fullname;
		$ticketVars["->crsid"] = $ticket->crsid;
		$ticketVars["->email"] = $ticket->email;
		$ticketVars["->college"] = $ticket->college;
		$ticketVars["->phone"] = $ticket->phone;
		$ticketVars["->address"] = $ticket->address;
		$ticketVars["->hashCode"] = $ticket->hash_code;
		$ticketVars["->ticketType"] = self::$ticketTypes[$ticket->ticket_type_id]->ticket_type;
		$ticketVars["->price"] = $ticket->price;
		$ticketVars["->valid"] = $ticket->valid == 1 ? "valid" : "invalid";
		$charity = "";
		if ($ticket->charity_donation != 0) {
			$charity = "(with £" . $ticket->charity_donation . " charitable donation)";
		}
		$ticketVars["->charity"] = $charity;

		$eticket_filename = PDFUtils::getFilename($ticket);
		$eticket_url = $config['tickets_website'] . 'etickets/' . $eticket_filename;

		$ticketVars["->eticketURL"] = $eticket_url;

		$message = $fullname . ':' . self::$ticketTypes[$ticket->ticket_type_id]->ticket_type . ':' . $ticket->hash_code;
		$ticketVars["->appURL"] = self::getAppURL($message);

		return $ticketVars;
	}

	// David's function
	private static function getAppURL($message)
	{
		$encrypt_method = "AES-256-CBC";
		$key = "71c6c1a27e93b35cd08696de9ec81b468b426eded2b6a7cc314f9b44ca6587d9";
		$iv = "6d517c70f593355b";
		$link_id = base64_encode(openssl_encrypt($message, $encrypt_method, $key, 0, $iv));
		return "https://app.jesusmayball.com/ticket/" . $link_id;
	}

	private static function getTicketVariablesWithPrefix($prefix, $ticketID, $ticketIdOrNull)
	{
		$prefix = "\$" . $prefix;
		$ticketVarsNoPrefix = self::getTicketVariables($ticketID, $ticketIdOrNull);
		$ticketVarsWithPrefix = array();
		if ($ticketVarsNoPrefix) {
			foreach ($ticketVarsNoPrefix as $key => $value) {
				$ticketVarsWithPrefix[$prefix . $key] = $value;
			}
		}
		return $ticketVarsWithPrefix;
	}

	private static function getGlobalVariableNames()
	{
		return array(
			"\$global->eventName",
			"\$global->eventYear",
			"\$global->eventFullDate",
			"\$global->eventWebsite",
			"\$global->eventWebsiteTickets",
			"\$global->ticketingEmail",
			"\$global->paymentEmail",
			"\$global->bankAccountName",
			"\$global->bankAccountNumber",
			"\$global->bankSortCode",
			"\$global->pound",
			"\$global->nameChangeCost"
		);
	}

	private static function getGlobalVariables()
	{
		global $config;
		$globals = array();
		$globals["\$global->eventName"] = $config["complete_event"];
		$globals["\$global->eventYear"] = date("Y", $config['dates']['ballnight']);
		$globals["\$global->eventFullDate"] = $config['full_date'];
		$globals["\$global->eventWebsite"] = $config['event_website'];
		$globals["\$global->eventWebsiteTickets"] = $config['tickets_website'];
		$globals["\$global->ticketingEmail"] = $config['ticket_email'];
		$globals["\$global->paymentEmail"] = $config['payments_email'];
		$globals["\$global->bankAccountName"] = $config['bank_account_name'];
		$globals["\$global->bankAccountNumber"] = $config['bank_account_num'];
		$globals["\$global->bankSortCode"] = $config['bank_sort_code'];
		$globals["\$global->pound"] = "£";
		$globals["\$global->nameChangeCost"] = $config['name_change_cost'];
		return $globals;
	}

	private static function variableInScope($variablesInScope, $variableName)
	{
		foreach ($variablesInScope as $variableSetName => $variableSet) {
			if (in_array($variableName, $variableSet)) {
				return true;
			}
		}
		return false;
	}

	private static function parseNonVariableProcessingInstruction($processingInstruction, &$variableScope)
	{
		$processingInstruction = preg_replace('/\s+/xms', ' ', trim($processingInstruction));
		$elements = explode(" ", $processingInstruction);
		switch ($elements[0]) {
			case "foreach":
				if (sizeof($elements) < 4) {
					return array("result" => 0, "message" => "Incorrect use of foreach instruction");
				}
				if (!self::variableInScope($variableScope, $elements[1])) {
					//TODO: Change error code?
					return array("result" => 0, "message" => "Attempt to use unbound variable \"" . $elements[1] . "\"");
				}
				if ($elements[1] != "\$application->tickets") {
					return array("result" => 0, "message" => "Attempting to use \"" . $elements[1] . "\" as a collection");
				}
				if ($elements[2] != "as") {
					return array("result" => 0, "message" => "Incorrect use of foreach instruction");
				}
				$elements3 = substr($elements[3], 1);
				if (!preg_match('/[^a-z0-9]/i', $elements3)) {
					if (isset($variableScope[$elements3])) {
						return array("result" => 0, "message" => "Attempting to override bound variable \"\$" . $elements3 . "\"");
					} else {
						$variableScope[$elements3] = self::getTicketVariablesNames($elements3);
						array_push($variableScope["__HELPERARRAY__"], $elements3);
						return array("result" => 1, "message" => "Variable $elements3 has been bound");
					}
				} else {
					return array("result" => 0, "message" => $elements3 . " is an invalid form for variable");
				}
				break;
			case "endeach":
				$varSetToRemove = array_pop($variableScope["__HELPERARRAY__"]);
				unset($variableScope[$varSetToRemove]);
				return array("result" => -1, "message" => "Variable $varSetToRemove has been unbound");
				break;
			default:
				return array("result" => 0, "message" => "Unknown processing instruction \"" . $processingInstruction . "\"");
				break;
		}
	}
}
Emails::init();
