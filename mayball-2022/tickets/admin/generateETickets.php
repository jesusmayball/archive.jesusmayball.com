<?php
require("auth.php");

$db = Database::getInstance();

if ($_POST["generate"] == 'true') {
    $tickets = $db->getAllTickets();
    //TODO: this is probs wasteful, can be done better
    $time = sizeof($tickets["tickets"]) / 5;
    set_time_limit($time);
    foreach ($tickets["tickets"] as $ticket) {
        if ($ticket->valid == "1") {
            // $application = $db->getAppInfo("app_id", $ticket->app_id);
            // $ticketInfo = $db->getTicketInfo("hash_code", $ticket->hash_code);
            // $ticketRecord = TicketRecord::buildFromTicket($ticket);

            // if ($application->printed_tickets == 0) {
            $eticket_filename = PDFUtils::getFilename($ticket, $config['eticket_secret']);
            PDFUtils::generateFromTicket($ticket, realpath('../etickets/') . '/' . $eticket_filename);
            // }
        }
    }
    set_time_limit(30);
}
// else if ($_POST["send"] == 'true' && $_GET['sent'] != 'true') {
//     $applications = $db->getAllApplications();
//     foreach ($applications["applications"] as $application) {
//         if ($application->printed_tickets == 0) {
//             $ticketInfo = $db->getTicketInfo('ticket_id', $application["principal_id"]);
//             $ticketRecord = TicketRecord::buildFromTicket($ticketInfo);
//
//             $result = Emails::generateMessage("eTickets", $application["app_id"], null);
//             if (!$result["result"]) {
//                 echo "<p><strong>Something went wrong. Please contact the webmaster.</strong></p>";
//             }
//             else {
//                 $address = $ticketRecord->crsid == null ? $ticketRecord->email : $ticketRecord->crsid . "@cam.ac.uk";
//                 $emailResult = Emails::sendEmail($address, $config['ticket_email'], $config['complete_event_date'], null, $result["subject"], $result["body"]);
//
//                 if (!$emailResult) {
//                     echo "<p><strong>Something went wrong while sending to " . $address . ". Please contact the webmaster.</strong></p>";
//                 }
//             }
//         }
//     }
//     $url = $config['tickets_website'] . 'admin/generateETickets.php?sent=true';
//     header('Location: ' . $url, true, 302);
//     die();
// }

Layout::htmlAdminTop("E-Tickets");
?>

<div class="container">
    <div class="page-header">
        <h1>E-Tickets</h1>
    </div>
</div>

<div class="container">
    <div class="row">
        <h2>Generate</h2>
        <p>Please ensure you have set an E-Ticket secret in Global Settings. Do not change that after sending the E-Ticket emails out, as the links will break.</p>
        <p>Each E-Ticket should be automatically generated when a new ticket is added or edited, so no action should be required.</p>
        <p>Only if something is broken (eg. secret has been changed after there are tickets present), use the button below to regenerate all tickets. Or use it before sending, just to be safe.</p>
        <form id="generate_button" method="post" action="generateETickets.php">
            <input name="generate" value="true" style="display: none"></input>
            <button type="submit" class="btn btn-success">Re-generate <span class="glyphicon glyphicon-ok"></span></button>
            <span class="help-inline" <?php if ($_POST["generate"] != 'true') { ?> style="display: none" <?php } ?>>Done!</span>
        </form>
    </div>
    <!-- <div class="row">
        <h2>Send E-Tickets</h2>
        <p>This will send out the E-Ticket email. Before sending this, click generate above (just to be safe).</p>
        <form id="send_button" method="post" action="generateETickets.php" onsubmit="return confirm('Do you really want to send?');">
            <input name="send" value="true" style="display: none"></input>
            <button type="submit" class="btn btn-success">Send E-Tickets <span class="glyphicon glyphicon-ok"></span></button>
            <span class="help-inline"
            <?php
             //if ($_GET["sent"] != 'true') {
             ?>
             style="display: none"
             <?php
             //}
             ?>
             >Sent!</span>
        </form>
    </div> -->
</div>

<?php
Layout::htmlAdminBottom("admin");
?>
