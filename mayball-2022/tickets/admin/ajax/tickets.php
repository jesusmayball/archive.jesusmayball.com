<?php

function console_log($data)
{
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}

require("auth.php");

if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else if (isset($_POST['action'])) {
    $action = $_POST['action'];
} else {
    AjaxUtils::errorMessage("No action parameter specified");
}
if (isset($_GET['action']) || $_POST['action']) {
    switch ($action) {
        case "listTickets":
            listTickets();
            break;
        case "getTicket":
            if (!isset($_GET['ticket_id'])) {
                AjaxUtils::errorMessage("Missing 'ticket_id' parameter");
            } else {
                getTicket($_GET['ticket_id']);
            }
            break;
        case "stats":
            getStats();
            break;
        default;
            switch ($_POST['action']) {
                case "deleteTicket":
                    deleteTicket();
                    break;
                case "searchTickets":
                    if (isset($_POST['field']) && isset($_POST['term'])) {
                        if (isset($_POST['paginate'])) {
                            search($_POST["field"], $_POST["term"], true);
                        } else {
                            search($_POST["field"], $_POST["term"], false);
                        }
                    } else {
                        AjaxUtils::errorMessage("No field or term specified");
                    }
                    break;
                case "editTicket":
                    if (!isset($_POST['ticket_id']) || !isset($_POST['title']) || !isset($_POST['first_name']) || !isset($_POST['last_name'])) {
                        AjaxUtils::errorMessage("Missing essential parameters");
                    } else {
                        editTicket($_POST['ticket_id'], $_POST['title'], $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['crsid'], $_POST['college'], $_POST['phone'], $_POST['address'], $_POST['ticket_type_id'], $_POST['diet'], $_POST['printed_program'], $_POST['is_refunded']);
                    }
                    break;
                default:
                    AjaxUtils::errorMessage("Invalid action parameter specified");
                    break;
            }
            break;
    }
}

function listTickets()
{
    global $resultsOnPage;
    if (isset($_GET['page'])) {
        $page = intval($_GET['page']);
        $offset = ($page - 1) * $resultsOnPage;
        if ($page < 0) {
            $page = 1;
            $offset = 0;
        }
    } else {
        $page = 1;
        $offset = 0;
    }
    $db = Database::getInstance();
    $tickets = $db->getAllTickets();
    $ticketCount = count($tickets["tickets"]);
    if ($offset > $ticketCount) {
        $page = 1;
        $offset = 0;
    }
    $subsetTickets = array_slice($tickets["tickets"], $offset, $resultsOnPage, true);
    echo json_encode(array("tickets" => $subsetTickets, "count" => $ticketCount, "page" => $page));
}

function search($field, $term, $paginate)
{
    global $resultsOnPage;
    if (isset($_POST['page'])) {
        $page = intval($_POST['page']);
        $offset = ($page - 1) * $resultsOnPage;
        if ($page < 0) {
            $page = 1;
            $offset = 0;
        }
    } else {
        $page = 1;
        $offset = 0;
    }
    $db = Database::getInstance();
    if ($field != "app_id" && $field != "ticket_id" && $field != "name" && $field != "email" && $field != "phone" && $field != "hash_code") {
        AjaxUtils::errorMessage("Search field not supported");
        return;
    }
    $tickets = $db->ticketSearch($field, $term);
    if ($paginate) {
        $ticketCount = count($tickets["tickets"]);
        if ($offset > $ticketCount) {
            $page = 1;
            $offset = 0;
        }
        $subsetTickets = array_slice($tickets["tickets"], $offset, $resultsOnPage, true);
        echo json_encode(array("tickets" => $subsetTickets, "count" => $ticketCount, "page" => $page));
    } else {
        echo json_encode($tickets);
    }
}

function deleteTicket()
{
    if (isset($_POST["ticket_id"])) {
        $ticketID = $_POST["ticket_id"];
        $db = Database::getInstance();
        $application = $db->getAppInfo("ticket_id", $ticketID);
        if ($application) {
            if ($application->principal_id == $ticketID) {
                AjaxUtils::errorMessage("This ticket is the primary ticket holder of the application. Please adjust the application to delete it.");
            } else {
                $db->deleteTicketSimple($ticketID);
                AjaxUtils::successMessage("Ticket " . $ticketID . " has been removed.");
            }
        } else {
            AjaxUtils::errorMessage("Ticket doesn't exist.");
        }
    } else {
        AjaxUtils::errorMessage("No ticket ID specified");
    }
}

function getTicket($ticketID)
{
    $ticketID = intval($ticketID);
    if ($ticketID == 0) {
        AjaxUtils::errorMessage("Invalid ticket id");
        return;
    }
    $db = Database::getInstance();
    $ticket = $db->ticketSearch("ticket_id", $ticketID);
    if (count($ticket["tickets"]) == 0) {
        AjaxUtils::errorMessage("No ticket found with id " . $ticketID);
    } else {
        $deref = array_values($ticket["tickets"]);
        echo json_encode(array("ticket" => array_shift($deref)));
    }
}

function alert($msg)
{
    echo "<script type='text/javascript'>alert('$msg');</script>";
}

function editTicket($ticketID, $title, $firstName, $lastName, $email, $crsid, $college, $phone, $address, $ticket_type_id, $diet, $printed_program, $is_refunded)
{
    $ticketID = intval($ticketID);
    if ($ticketID == 0) {
        AjaxUtils::errorMessage("Invalid ticket ID");
        return;
    }

    $db = Database::getInstance();
    $ticket = $db->getTicketInfo("ticket_id", $ticketID);

    if (!$ticket) {
        AjaxUtils::errorMessage("No ticket was found with ID " . $ticketID);
        return;
    }
    $application = $db->getAppRecord($ticket->app_id);

    if ($application == null) {
        AjaxUtils::errorMessage("Weird... no application was found with ticket ID " . $ticketID . "'s specified application ID.");
        return;
    }

    if ($application->principal_id == $ticket->ticket_id) {
        if ($phone == null || trim($phone) == "") {
            AjaxUtils::errorMessage("You've not specified a phone number for a primary ticket holder ticket");
            return;
        }
        if ($email == null || trim($email) == "") {
            if ($crsid == null || trim($crsid) == "") {
                AjaxUtils::errorMessage("You've not specified an email or crsid for a primary ticket holder ticket");
                return;
            }
        }
    }
    // $eticket_filename = null;
    // if ($application->printed_tickets == 0 && $ticket->valid == "1") {
    // do it for all tickets, not just ones that chose etickets
    // if ($ticket->valid == "1") {
    //     $eticket_filename = PDFUtils::getFilename($ticket);
    // }

    $ticketRecord = TicketRecord::buildFromTicket($ticket);
    $ticketRecord->title = $title;
    $ticketRecord->first_name = $firstName;
    $ticketRecord->last_name = $lastName;
    $ticketRecord->crsid = $crsid;
    $ticketRecord->email = $email;
    $ticketRecord->college = $college;
    $ticketRecord->phone = $phone;
    $ticketRecord->address = $address;
    $ticketRecord->ticket_type_id = $ticket_type_id;
    $ticketRecord->diet = $diet;
    $ticketRecord->printed_program = $printed_program;
    $ticketRecord->is_refunded = $is_refunded;

    $ticketTypes = $db->getAllTicketTypes(false, false, true, false);
    if (!$ticketTypes[$ticket_type_id]) {
        AjaxUtils::errorMessage("You've not specified valid ticket type id: " . $ticket_type_id);
        return;
    }

    try {
        $db->updateTicket($ticketRecord, true);
        // if ($eticket_filename != null) {
        //     PDFUtils::generateFromTicket($ticketRecord, realpath('../../etickets/') . '/' . $eticket_filename);
        // }
    } catch (DatabaseException $e) {
        AjaxUtils::errorMessage($e->getMessage());
        return;
    }
    echo json_encode(array("ticket" => $db->getTicketInfo("ticket_id", $ticketID)));
}


function getStats()
{
    $db = Database::getInstance();
    $returnArray =     array();
    $returnArray["ticketCounts"] = $db->getAllTicketTypes(false, false, true, false);
    usort($returnArray["ticketCounts"], function ($a, $b) {
        return ($b->ticket_count - $a->ticket_count);
    });

    $returnArray["ticketCountsValid"] = $db->getAllTicketTypes(false, false, true, true);
    usort($returnArray["ticketCountsValid"], function ($a, $b) {
        return ($b->ticket_count - $a->ticket_count);
    });

    $tickets = $db->ticketTotals();
    $returnArray["totalBought"] = $tickets['total-bought'];
    $returnArray["totalPaid"] = $tickets['total-paid'];
    $returnArray["revenueBought"] = $tickets['revenue-bought'] == null ? "0" : $tickets['revenue-bought'];
    $returnArray["revenuePaid"] = $tickets['revenue-paid'] == null ? "0" : $tickets['revenue-paid'];
    $returnArray["charityBought"] = $tickets['charity-bought'] == null ? "0" : $tickets['charity-bought'];
    $returnArray["charityPaid"] = $tickets['charity-paid'] == null ? "0" : $tickets['charity-paid'];
    $returnArray["printedTickets"] = $tickets['printed-tickets'] == null ? "0" : $tickets['printed-tickets'];
    $returnArray["printedPrograms"] = $tickets['printed-programs'] == null ? "0" : $tickets['printed-programs'];

    $nameChangeRevenue = 0;
    $nameChanges = $db->getAllNameChanges();
    foreach ($nameChanges["name_changes"] as $value) {
        if ($value->complete == "1") {
            $nameChangeRevenue += round(floatval($value->cost), 2);
        }
    }
    $returnArray["nameChangeRevenue"] = $nameChangeRevenue;
    echo json_encode($returnArray);
}
