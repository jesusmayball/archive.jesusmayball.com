<?php
require("glue.php");
global $config;

$db = Database::getInstance();

$charity_yes_phrase = "Yes, I would like to make a charitable donation of £2.00";
$charity_no_phrase = "No, I would not like to make a charitable donation";
$charity_other_phrase = "Yes, I would like to make a charitable donation of £";

global $alumni_categories;
$alumni_categories = array(140, 141, 142);
function removeAlumniTickets($tickets)
{
    // getAllTicketTypes($availableOnly = true, $useClasses = false, $includeRestricted = false, $countValidOnly = false)

    return array_filter($tickets, function ($t) {
        global $alumni_categories;
        return (!in_array($t->ticket_type_id, $alumni_categories));
    });
}

//if more bursary types are added, put IDs in $bursary_categories
$bursary_categories = array(147);
function removeBursaryTickets($tickets)
{
    return array_filter($tickets, function ($t) {
        global $bursary_categories;
        return (!in_array($t->ticket_type_id, $bursary_categories));
    });
}


if ($config['pages']['additionaltickets'] == "disabled") {
    Layout::htmlExit("Additional Tickets", "We're sorry, but name additional tickets purchase is not available");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $errors = "";
    if (!isset($_POST['app_id']) || intval($_POST['app_id']) == 0) {
        $errors .= "<div>Missing app id</div>";
    }
    if (!isset($_POST['title'])) {
        $errors .= "<div>Missing title</div>";
    }
    if (!isset($_POST['first_name'])) {
        $errors .= "<div>Missing first name</div>";
    }
    if (!isset($_POST['last_name'])) {
        $errors .= "<div>Missing last name</div>";
    }
    if (!isset($_POST['email'])) {
        $errors .= "<div>Missing email details</div>";
    }
    if (!isset($_POST['ticket_type'])) {
        $errors .= "<div>Missing ticket type</div>";
    }
    if (!isset($_POST['charity'])) {
        $errors .= "<div>Missing charity details</div>";
    }

    // if (isset($_SESSION['uid'])) {
    //     if ($_SESSION['uid'] != $_POST['captcha']) {
    //         $errors .= "<div>Incorrect human verification</div>";
    //     }
    // } else {
    //     $errors .= "<div>Missing human verification</div>";
    // }

    $ticketType = 0;
    if (!$errors) {
        $ticketType = $_POST["ticket_type"];
        $tickets_all = $db->getAllTicketTypes(false, true, true, false);
        $ticketTypes = removeBursaryTickets(removeAlumniTickets($tickets_all));

        $ticketTypeOrNull = $ticketTypes[$ticketType];

        if ($ticketTypeOrNull == null) {
            $errors .= "<div>Ticket type \"" . $ticketType . "\" doesn't exist</div>";
        }

        if (!$errors) {

            if ($ticketTypeOrNull->available == "0") {
                $errors .= "<div>No tickets of type \"" . $ticketTypeOrNull->ticket_type . "\" are left</div>";
            }

            $max_key = $ticketTypes[$ticketType]->use_other_max;
            $using_other_max = false;
            if ($max_key > 0) {
                $key_available = $ticketTypes[$ticketType]->maximum - $ticketTypes[$ticketType]->ticket_count;
                $key_max_available = $ticketTypes[$max_key]->maximum - $ticketTypes[$max_key]->ticket_count;
                $number_available = min($key_available, $key_max_available);
                $using_other_max = $key_available >= $key_max_available;
            } else {
                $number_available = $ticketTypeOrNull->maximum - $ticketTypeOrNull->ticket_count;
            }

            if ($number_available <= 0) {
                $errors .= "<div>No tickets of type \"" . $ticketTypeOrNull->ticket_type . "\" are left</div>";

                //mark as unavailable
                $ticketTypeRecord = new TicketTypeRecord();
                $ticketTypeRecord->ticket_type_id = $max_key;
                $ticketTypeRecord->available = 0;
                //I don't think an exception can be thrown here
                $db->updateTicketType($ticketTypeRecord);

                //if entire category is sold out, mark all subtypes as unavailable
                if ($using_other_max) {
                    foreach ($tickets_all as $t) {
                        if ($t->use_other_max == $max_key && !$t->restricted) {
                            $ticketTypeRecord->ticket_type_id = $t->ticket_type_id;
                            $db->updateTicketType($ticketTypeRecord);
                        }
                    }
                } else {
                    $ticketTypeRecord->ticket_type_id = $ticketType;
                    $db->updateTicketType($ticketTypeRecord);
                }
            }
        }
    }

    $appID = $_POST["app_id"];
    $title = $_POST["title"];
    $firstName = $_POST["first_name"];
    $lastName = $_POST["last_name"];
    if ($_POST["charity"] == 'other') {
        $charity_amount = $_POST["charity_other"];
        if (is_numeric($charity_amount) && floatval($charity_amount) > 0) {
            $charity_amount = round(floatval($charity_amount), 2);
            $charity = number_format($charity_amount, 2, '.', '');
        } else {
            $charity = "0";
        }
    } else {
        $charity = $_POST["charity"]; //Upgrade here
    }
    $email = $_POST["email"];
    $crsid = isset($_POST["crsid"]) ? $_POST["crsid"] : null;

    if (!$errors) {
        $application = $db->getAppInfo("app_id", $appID);
        if ($application) {
            if (count($application->guests) >= $config["max_guests"]) {
                $errors .= "<div>You have reached your ticket limit</div>";
            }
        } else {
            $errors .= "<div>Unable to get application record '" . $appID . "'</div>";
        }
    }

    if (!$errors) {
        // $ticketID = $db->insertBlankTicket($appID);
        $ticket = new TicketRecord();
        // $ticket->ticket_id = $ticketID;
        $ticket->app_id = intval($appID);
        $ticket->title = preg_replace('/[^A-Za-z+]/', "", $title);
        $ticket->first_name = preg_replace('/[^A-Za-z+]/', "", $firstName);
        $ticket->last_name = preg_replace('/[^A-Za-z+]/', "", $lastName);
        $ticket->ticket_type_id = $ticketType;
        $ticket->price = $ticketTypeOrNull->price;
        $ticket->charity_donation = max(0, floatval($charity));
        $ticket->valid = 0;
        $ticket->email = $email;
        $ticket->college = null;
        $ticket->address = null;
        $ticket->phone = null;
        $ticket->is_ballot = true;
        if (!$crsid || !trim($crsid)) {
            $ticket->crsid = null;
        } else {
            $ticket->crsid = $crsid;
        }

        try {
            $ticket->ticket_id = $db->insertTicket($ticket);
        } catch (DatabaseException $e) {
            $db->deleteTicketSimple($ticketID);
            $errors .= "<div>Error, ticket already exists... Please change the crsid or email address</div>";
        }

        if (!$errors) {
            $ticketTypes = $db->getAllTicketTypes(false, true, true, false);

            $max_key = $ticketTypes[$ticketType]->use_other_max;
            $using_other_max = false;
            if ($max_key > 0) {
                $key_available = $ticketTypes[$ticketType]->maximum - $ticketTypes[$ticketType]->ticket_count;
                $key_max_available = $ticketTypes[$max_key]->maximum - $ticketTypes[$max_key]->ticket_count;
                $number_available = min($key_available, $key_max_available);
                $using_other_max = $key_available >= $key_max_available;
            } else {
                $number_available = $tickets[$key]->maximum - $tickets[$key]->ticket_count;
            }

            try {
                if ($number_available <= 0) {
                    $ticketTypeRecord = new TicketTypeRecord();
                    $ticketTypeRecord->ticket_type_id = $max_key;
                    $ticketTypeRecord->available = 0;
                    //I don't think an exception can be thrown here
                    $db->updateTicketType($ticketTypeRecord);

                    //if entire category is sold out, mark all subtypes as unavailable
                    if ($using_other_max) {
                        foreach ($ticketTypes as $t) {
                            if ($t->use_other_max == $max_key && !$t->restricted) {
                                $ticketTypeRecord->ticket_type_id = $t->ticket_type_id;
                                $db->updateTicketType($ticketTypeRecord);
                            }
                        }
                    } else {
                        $ticketTypeRecord->ticket_type_id = $ticketType;
                        $db->updateTicketType($ticketTypeRecord);
                    }
                }

                $appRecord = $db->getAppRecord($appID);

                if ($appRecord->status != "created") {
                    $appRecord->status = "changed";
                }
                $db->updateApp($appRecord);
            } catch (DatabaseException $e) {
                $errors .= "<div>" . $e->getMessage() . "</div>";
            }
        }
    }

    $warnings = "";
    if (!$errors) {
        //create receipt
        $result = Emails::generateMessage("confirmBooking", $appID, null);

        if (!$result["result"]) {
            $warnings .= " <div>Unable to generate the email.</div>";
        } else {
            //save in receipts folder.
            $app = $db->getAppInfo("app_id", $appID);
            $emailGenerated = $app->principal->crsid == null ? $app->principal->email : $app->principal->crsid . "@cam.ac.uk";
            $emailResult = Emails::sendEmail($emailGenerated, $config['ticket_email'], $config['complete_event_date'], $config['ticket_email'], $result["subject"], $result["body"]);
            $error = file_put_contents("receipts/order_" . ApplicationUtils::appIDZeros($appID) . "_" . time() . "_confirm.txt", $result["body"]);
            if (!$error) {
                $warnings .= "<div>Unable to write receipt.</div>";
            }
            if (!$emailResult) {
                $warnings .= "<div>Unable to send the email.</div>";
            }
        }
    }

    session_destroy();
    if ($errors) {
        Layout::htmlExit("An error occured", $errors);
    } else {
        Layout::htmlExit("Ticket added", $warnings . "<div>Ticket created for " . $title . " " . $firstName . " " . $lastName . ".</div><div><a href=\"\" class=\"btn btn-success\">Add another</a></div>");
    }
} else {
    Layout::htmlTop("Additional Ticket Purchase");

    $ticketTypes = removeAlumniTickets($db->getAllTicketTypes(true));
    $ticketTypes = removeBursaryTickets($ticketTypes);
    if (count($ticketTypes) == 0) {
        Layout::htmlExit("Sold out", "Sorry, all tickets have now sold out. Please see the " . $config['complete_event'] . " website for details of the waiting list.");
    }

    $ticketOptions = "";
    foreach ($ticketTypes as $availableTicket) {
        $ticketOptions .= "<option value=\"" . $availableTicket->ticket_type_id . "\"";
        if ((isset($_SESSION["principal"]["ticket_type_id"]))
            && ($_SESSION["principal"]["ticket_type_id"] == $availableTicket->ticket_type_id)
        ) {
            $ticketOptions .= " selected ";
        }
        $ticketOptions .= ">" . $availableTicket->ticket_type;
        $ticketOptions .= " - &#163;" . $availableTicket->price . "</option>";
    }
?>

    <script type="text/javascript">
        var noError = true;

        $("document").ready(function() {
            $("form#extraTicketForm").submit(function() {
                noError = true;
                var ret = true;

                if ($("form#extraTicketForm select#ticket_title_field").val() == "") {
                    ret = false;
                    $("div#form-group-title").addClass("has-error");
                    $("div#form-group-title .help-inline").show();
                } else {
                    $("div#form-group-title").removeClass("has-error");
                    $("div#form-group-title .help-inline").hide();
                }

                if ($("form#extraTicketForm input#ticket_first_name_field").val() == "") {
                    ret = false;
                    $("div#form-group-first-name").addClass("has-error");
                    $("div#form-group-first-name .help-inline").show();
                } else {
                    $("div#form-group-first-name").removeClass("has-error");
                    $("div#form-group-first-name .help-inline").hide();
                }

                if ($("form#extraTicketForm input#ticket_last_name_field").val() == "") {
                    ret = false;
                    $("div#form-group-last-name").addClass("has-error");
                    $("div#form-group-last-name .help-inline").show();
                } else {
                    $("div#form-group-last-name").removeClass("has-error");
                    $("div#form-group-last-name .help-inline").hide();
                }
                if ($("form#extraTicketForm input#ticket_crsid_field").val() == "" & $("form#extraTicketForm input#ticket_email_field").val() == "") {
                    ret = false;
                    $("div#form-group-crsid").addClass("has-error");
                    $("div#form-group-email").addClass("has-error");
                    $("div#form-group-crsid .help-inline").show();
                } else {
                    $("div#form-group-crsid").removeClass("has-error");
                    $("div#form-group-email").removeClass("has-error");
                    $("div#form-group-crsid .help-inline").hide();
                }

                if (!$("#tac").is(":checked")) {
                    alert("To proceed please agree to the terms and conditions");
                    ret = false;
                }
                if (!ret) {
                    noError = false;
                    return false;
                }
            });
        });
    </script>
    <div class="container purchase-container">
        <div class="page-header text-center">
            <img class="event-logo" src='res/<?php echo Layout::getLogoURL(); ?>' alt="<?php echo $config['complete_event']; ?>" />
        </div>
        <form id="extraTicketForm" class="well form-horizontal" method="post">
            <fieldset>
                <legend>Additional Tickets</legend>
                <p><em>Please fill out the details of your extra guest, not the details of the main applicant. <br>Please ensure that your Application ID is correct.</em></p><br>
                <div class="form-group" id="form-group-first-name">
                    <label class="control-label col-sm-2">App ID:</label>
                    <div class="col-sm-10">
                        <input name="app_id" class="form-control" type="text"><span class="help-inline" style="display: none">Please enter the application ID to update</span>
                    </div>
                </div>
                <div class="form-group" id="form-group-title">
                    <label class="control-label col-sm-2">Title:</label>
                    <div class="col-sm-10">
                        <select id="title_field" name="title" class="form-control">
                            <?php Layout::titlesOptions(isset($_GET["itseaster"])); ?>
                        </select><span class="help-inline" style="display: none">Please
                            select a title</span>
                    </div>
                </div>
                <div class="form-group" id="form-group-first-name">
                    <label class="control-label col-sm-2">First Name:</label>
                    <div class="col-sm-10">
                        <input name="first_name" class="form-control" type="text" />
                        <span class="help-inline" style="display: none">Please enter your first name</span>
                    </div>
                </div>
                <div class="form-group" id="form-group-last-name">
                    <label class="control-label col-sm-2">Last Name:</label>
                    <div class="col-sm-10">
                        <input name="last_name" class="form-control" type="text" />
                        <span class="help-inline" style="display: none">Please enter your last name</span>
                    </div>
                </div>
                <div class="form-group" id="form-group-crsid">
                    <label class="control-label col-sm-2">CRSid:</label>
                    <div class="col-sm-10">
                        <input name="crsid" class="form-control" type="text" />
                        <span class="help-inline" style="display: none">Please enter your csrid or email</span>

                    </div>
                </div>
                <div class="form-group" id="form-group-email">
                    <label class="control-label col-sm-2">or Email:</label>
                    <div class="col-sm-10">
                        <input placeholder="ab123@cam.ac.uk" type="text" name="email" class="form-control" />
                    </div>
                </div>
                <div class="form-group" id="form-group-ticket">
                    <label class="control-label col-sm-2">Ticket:</label>
                    <div class="col-sm-10">
                        <select name="ticket_type" class="form-control"><?php echo $ticketOptions; ?></select>
                        <span class="help-inline" style="display: none">Please select a ticket type</span>
                    </div>
                </div>

                <div class="form-group" id="form-group-charity">
                    <label class="control-label col-sm-2">Charity:</label>
                    <div class="col-sm-9">
                        <label class="radio-inline"><input name="charity" value="2" type="radio">
                            <?php echo $charity_yes_phrase; ?>
                        </label>
                        <label class="radio-inline" style="margin-left:0px;"><input name="charity" value="0" type="radio">
                            <?php echo $charity_no_phrase; ?>
                        </label>
                        <label class="radio-inline" style="margin-left:0px;">
                            <input name="charity" value="other" type="radio">
                            <?php echo $charity_other_phrase; ?>
                            <input type="text" name="charity_other" class="form-control" style="width:64px;display:inline-block;height:22px;" />
                        </label>

                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2">
                        <div>By confirming this selection you are committing to buying these tickets. Payment instructions will be shown on the next screen.</div>
                        <input type="checkbox" id="tac" /><span> I accept the <a href="tc.pdf" target="_blank">Terms and Conditions</a></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-2">
                        <button type="submit" class="btn btn-success">Add </button>
                        <a href="index.php" class="btn btn-danger">Cancel</a>
                    </div>
                </div>
            </fieldset>
        </form>

    </div>
<?php
    Layout::htmlBottom();
}
?>