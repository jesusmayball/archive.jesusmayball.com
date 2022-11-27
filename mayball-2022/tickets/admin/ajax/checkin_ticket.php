<?php
require("auth.php");


if (!isset($_POST["hash"]) || !isset($_POST["ticket_id"])) {
    AjaxUtils::errorMessage("Invalid ticket");
}
$db = Database::getInstance();
$ticket = $db->getTicketInfo("ticket_id", $_POST["ticket_id"]);
$ticket_secret = "1JBLKPHsCS19MWLs2oGx";
if (password_verify($ticket->hash_code . $ticket_secret, $_POST["hash"])) {
    if ($ticket->entered_via == "true") {
        $response = array(
            "status" => "rescan",
            "name" => $ticket->first_name . " " . $ticket->last_name,
            "diet" => $ticket->diet,
            "program" => $ticket->printed_program,
        );
    } else {
        $db->markTicketCheckedIn($ticket->ticket_id);
        $response = array(
            "status" => "ok",
            "name" => $ticket->first_name . " " . $ticket->last_name,
            "diet" => $ticket->diet,
            "program" => $ticket->printed_program,
        );
    }
} else {
    $response = array(
        "status" => "bad",
    );
}
AjaxUtils::successMessageWithArray("Success", $response);
