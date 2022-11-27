<?php
require("auth.php");

$db = Database::getInstance();

session_start();
if (!isset($_SESSION["active"]) || isset($_POST["newsession"])) {
	prepareSession();
}
else {
	sendNextEmail();
}

function prepareSession() {
	global $db;
	global $config;
// 	if (!isset($_POST["guard"]) || trim($_POST["guard"]) === "") {
// 		AjaxUtils::errorMessage("No guard value set");
// 	}
// 	else if ($_COOKIE["guard"] == trim($_POST["guard"])) {
// 		AjaxUtils::errorMessage("Session over");
// 	}
// 	else {
		$email = false;
		if (!isset($_POST["template"])) {
			AjaxUtils::errorMessage("No email template specified");
			return;
		}
		else {
			$email = $db->getEmailByName($_POST["template"]);
			if (!$email) {
				AjaxUtils::errorMessage("No email template exists named \"" . $_POST["template"] . "\"");
				return;
			}

			$subjectResult = Emails::staticValidation($email->subject, $email->appOrTicket);
			if (!$subjectResult["result"]) {
				AjaxUtils::errorMessage("Static validation of subject failed");
				return;
			}
			$bodyResult = Emails::staticValidation($email->body, $email->appOrTicket);
			if (!$bodyResult["result"]) {
				return array("result" => false, "message" => "Static validation of body failed");
			}


			$_SESSION["subjectTokens"] = $subjectResult["tokens"];
			$_SESSION["bodyTokens"] = $bodyResult["tokens"];
			$_SESSION["appOrTicket"] = $email->appOrTicket;
		}
		if (isset($_POST["bcc"])) {
			$_SESSION["bcc"] = $_POST["bcc"];
		}
		if (isset($_POST["sender"])) {
			$_SESSION["sender"] = $_POST["sender"];
		}
		else {
			$_SESSION["sender"] = $config["ticket_email"];
		}
		if (isset($_POST["sendas"])) {
			$_SESSION["sendas"] = $_POST["sendas"];
		}
		else {
			$_SESSION["sendas"] = $config["complete_event_date"];
		}
		$_SESSION["emails"] = array();
		if (isset($_POST["emails"])) {
			$emailsbelong = isset($_POST["emailsbelong"]) ? $_POST["emailsbelong"] : "false";
			$emailsArray = explode(" ", $_POST["emails"]);
			foreach ($emailsArray as $email) {
				if ($_SESSION["appOrTicket"] == 1) {
					$app = $db->getAppInfo("email", $email);
					if (!$app) {
						if ($emailsbelong != "true" && (trim($email) !== "")) {
							array_push($_SESSION["emails"], array("email" => $email, "id" => 0));
						}
					}
					else {
						array_push($_SESSION["emails"], array("email" => $email, "id" => $app->app_id));
					}
				} else { //ticket or guest only
                    $ticket = $db->getTicketInfo("email", $email);
                    if (!$ticket) {
                        if ($emailsbelong !== "true" && (trim($email) !== "")) {
                            array_push($_SESSION["emails"], array("email" => $email, "id" => 0));
                        }
                    }
                    else {
                        array_push($_SESSION["emails"], array("email" => $email, "id" => $ticket->ticket_id));
                    }
                }
			}
		}
		else if (isset($_POST["ids"])) {
			$idsArray = explode(" ", $_POST["ids"]);
			foreach ($idsArray as $id) {
				if ($_SESSION["appOrTicket"] == 1) {
					$app = $db->getAppInfo("app_id", $id);
					if ($app) {
						array_push($_SESSION["emails"], array("email" => $app->principal->email, "id" => $id));
					}
				}
                else if ($_SESSION["appOrTicket"] == 2) {
                    $tickets = $db->ticketSearch("app_id", $id);
                    $app = $db->getAppInfo("app_id", $id);
                    foreach ($tickets["tickets"] as $ticket) {
                        if ($ticket && $ticket->email != null && $ticket->email != "" && $ticket->ticket_id != $app->principal_id) {
                            array_push($_SESSION["emails"], array("email" => $ticket->email, "id" => $ticket->ticket_id));
                        }
                    }
				}
                else {
					$ticket = $db->getTicketInfo("ticket_id", $id);
					if ($ticket) {
						if ($ticket->email != null && $ticket->email != "") {
							array_push($_SESSION["emails"], array("email" => $ticket->email, "id" => $id));
						}
					}
				}
			}
		}
		else {
            $payment = isset($_POST["payment"]) ? $_POST["payment"] : "both";
            $printed = isset($_POST["printed"]) ? $_POST["printed"] : "both";
			$includetickets = isset($_POST["includetickets"]) ? $_POST["includetickets"] : "all";
			$tickettypes = isset($_POST["tickettypes"]) ? $_POST["tickettypes"] : array();
            if ($_SESSION["appOrTicket"] == 1) {
				$applications = $db->getAllApplications();
				foreach ($applications["applications"] as $application) {
					$trec = array("email" => $application["principalEmail"], "id" => $application["app_id"]);

					$p1 = ($payment == "both" || $payment == "paid") && ($application["status"] === "processed");
					$p2 = ($payment == "both" || $payment == "unpaid") && ($application["status"] !== "processed");
					$t1 = ($includetickets == "all" || $includetickets == "include") && in_array($application["principalTicketTypeID"], $tickettypes);
					$t2 = ($includetickets == "all" || $includetickets == "exclude") && !in_array($application["principalTicketTypeID"], $tickettypes);
                    $print1 = ($printed == "both" || $printed == "printed") && ($application["printed_tickets"] === "1");
                    $print2 = ($printed == "both" || $printed == "etickets") && ($application["printed_tickets"] === "0");

                    if (($p1 || $p2) && ($t1 || $t2) && ($print1 || $print2)) {
						array_push($_SESSION["emails"], $trec);
					}
				}
			}
            else if ($_SESSION["appOrTicket"] == 2) { // Guest only
				$applications = $db->getAllApplications();
                $tickets = $db->getAllTickets();
				foreach ($tickets["tickets"] as $ticket) {
                    if ($applications["applications"][$ticket->app_id]["principal_id"] != $ticket->ticket_id
                        && $ticket->email != null && $ticket->email != "") {
                        $trec = array("email" => $ticket->email, "id" => $ticket->ticket_id);
                        $p1 = ($payment == "both" || $payment == "paid") && ($ticket->valid == 1);
                        $p2 = ($payment == "both" || $payment == "unpaid") && ($ticket->valid == 0);
                        $t1 = ($includetickets == "all" || $includetickets == "include") && in_array($ticket->ticket_type_id, $tickettypes);
                        $t2 = ($includetickets == "all" || $includetickets == "exclude") && !in_array($ticket->ticket_type_id, $tickettypes);

                        $print1 = ($printed == "both" || $printed == "printed") && $ticket->printed == 1;
                        $print2 = ($printed == "both" || $printed == "etickets") && $ticket->printed != 1;

                        if (($p1 || $p2) && ($t1 || $t2) && ($print1 || $print2)) {
                            array_push($_SESSION["emails"], $trec);
                        }
                    }
				}
			} else { //Ticket
				$tickets = $db->getAllTickets();
				foreach ($tickets["tickets"] as $ticket) {
                    if ($ticket->email != null && $ticket->email != "") {
                        $trec = array("email" => $ticket->email, "id" => $ticket->ticket_id);
                        $p1 = ($payment == "both" || $payment == "paid") && ($ticket->valid == 1);
                        $p2 = ($payment == "both" || $payment == "unpaid") && ($ticket->valid == 0);
                        $t1 = ($includetickets == "all" || $includetickets == "include") && in_array($ticket->ticket_type_id, $tickettypes);
                        $t2 = ($includetickets == "all" || $includetickets == "exclude") && !in_array($ticket->ticket_type_id, $tickettypes);

                        $print1 = ($printed == "both" || $printed == "printed") && $ticket->printed == 1;
                        $print2 = ($printed == "both" || $printed == "etickets") && $ticket->printed != 1;

                        if (($p1 || $p2) && ($t1 || $t2) && ($print1 || $print2)) {
                            array_push($_SESSION["emails"], $trec);
                        }
                    }
				}

			}
		}
		$_SESSION["progress"] = 0;
		$_SESSION["active"] = true;
		AjaxUtils::successMessageWithArray("Session prepared", array("total" => sizeof($_SESSION["emails"]), "emails" => $_SESSION["emails"]));
//	}
}

function sendNextEmail() {
	if (!$_SESSION["active"]) {
		AjaxUtils::successMessageWithArray("Session complete", array("complete" => true));
		session_destroy();
	}
	if ($_SESSION["progress"] <= sizeof($_SESSION["emails"]) - 1) {
		$result = Emails::generateMessageFromTokens($_SESSION["subjectTokens"], $_SESSION["bodyTokens"], $_SESSION["emails"][$_SESSION["progress"]]["id"], $_SESSION["appOrTicket"], array());
		if (!$result["result"]) {
			AjaxUtils::errorMessageWithArray($result["message"], array("email" => $_SESSION["emails"][$_SESSION["progress"]]["email"], "progress" => ++$_SESSION["progress"]));
			return;
		}
		if (Emails::sendEmail($_SESSION["emails"][$_SESSION["progress"]]["email"], $_SESSION["sender"], $_SESSION["sendas"], $_SESSION["bcc"], $result["subject"], $result["body"])) {
			AjaxUtils::successMessageWithArray("Email sent", array("email" => $_SESSION["emails"][$_SESSION["progress"]]["email"], "progress" => ++$_SESSION["progress"]));
		}
		else {
			AjaxUtils::errorMessageWithArray("Email didn't send", array("email" => $_SESSION["emails"][$_SESSION["progress"]]["email"], "progress" => ++$_SESSION["progress"]));
		}
	}
	else {
		$_SESSION["active"] = false;
		AjaxUtils::successMessageWithArray("Session complete", array("complete" => true));
		session_destroy();
	}
}
