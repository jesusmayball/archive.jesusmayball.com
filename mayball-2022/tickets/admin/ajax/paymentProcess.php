<?php
require("auth.php");

if (count($_FILES) == 1) {
    $filename = array_keys($_FILES)[0];

    $file_err_code = $_FILES[$filename]["error"];
    if ($file_err_code == 1 || $file_err_code == 2) {
        return AjaxUtils::errorMessage("The uploaded file exceeds the maximum file size.");
    } else if ($file_err_code == 3 || $file_err_code == 7) {
        return AjaxUtils::errorMessage("Something went wrong uploading, please try again");
    } else if ($file_err_code == 4) {
        return AjaxUtils::errorMessage("No file uploaded.");
    } else if ($file_err_code == 6) {
        return AjaxUtils::errorMessage("No temporary folder exists to upload the file to");
    } else if ($file_err_code != 0) {
        return AjaxUtils::errorMessage("Something went wrong, please refresh the page and try again.");
    }
    processBankCSVFile($_FILES[$filename]['tmp_name']);
} else {
    AjaxUtils::errorMessage("No file uploaded");
}

function processBankCSVFile($filename)
{
    $fh = fopen($filename, 'r');
    $succesfullyProcessed = array();
    $failedtoProcess = array();

    fgets($fh); //Removes column name row
    while ($line = fgets($fh)) {
        $parts = explode(",", $line);
        if (count($parts) != 6) {
            continue;
            // return AjaxUtils::errorMessage("Invalid bank statement CSV uploaded");
        }

        $details = $parts[1];
        $in = $parts[3];
        $hash = md5($line);
        if (!is_numeric($in)) {
            $failedArray = array(
                "reference" => $details,
                "message" => "Non mayball payment"
            );
            array_push($failedtoProcess, $failedArray);
            continue;
        }
        $in = (float) $in;

        //Matches the instructions for creating payment references

        $success = false;

        preg_match("/A(\d+)(\w+)/i", $details, $appMatches);
        if (count($appMatches) === 3 && is_numeric($appMatches[1])) {
            $success = true;

            $appID = (int) $appMatches[1];
            $result = processPayment($appID, $in, $hash);
            if ($result["success"]) {
                $successArray = array(
                    "reference" => $details,
                    "message" => $result["message"]
                );
                array_push($succesfullyProcessed, $successArray);
            } else {
                $failedArray = array(
                    "reference" => $details,
                    "message" => $result["message"]
                );
                array_push($failedtoProcess, $failedArray);
                continue;
            }
        }

        preg_match("/NameChange(\d+)/i", $details, $ncMatches);
        if (!$success && count($ncMatches) === 2 && is_numeric($ncMatches[1])) {
            $success = true;

            $ncID = (int) $ncMatches[1];
            $result = payNameChange($ncID, $hash);
            if ($result["success"]) {
                $successArray = array(
                    "reference" => $details,
                    "message" => $result["message"]
                );
                array_push($succesfullyProcessed, $successArray);
            } else {
                $failedArray = array(
                    "reference" => $details,
                    "message" => $result["message"]
                );
                array_push($failedtoProcess, $failedArray);
                continue;
            }
        }

        if (!$success) {
            $failedArray = array(
                "reference" => $details,
                "message" => "Misformatted payment reference"
            );
            array_push($failedtoProcess, $failedArray);
            continue;
        }
    }
    fclose($fh);

    AjaxUtils::successMessageWithArray("Succesfully uploaded file", array(
        "successful" => $succesfullyProcessed,
        "failed" => $failedtoProcess
    ));
}

function processPayment($appID, $amount, $hash)
{

    global $config;
    $db = Database::getInstance();

    if ($db->paymentIsDuplicate($hash)) {
        return array(
            "success" => false,
            "message" => "Payment already processed"
        );
    }

    $app = $db->getAppInfo("app_id", $appID);

    if (!$app) {
        return array(
            "success" => false,
            "message" => "Application ID was not found"
        );
    }

    $amount = floatval($amount);
    $db->insertPayment($appID, $amount, "bacs", true, "Added through admin interface autoprocessing", $hash);

    $app = $db->getAppInfo("app_id", $appID);
    if ($app->totalPaid >= $app->totalCost) {
        $db->setTicketsValid($appID);
        $applicationRecord = ApplicationRecord::buildFromApplication($app);
        $applicationRecord->status = "processed";
        try {
            $db->updateApp($applicationRecord);
        } catch (DatabaseException $e) {
            return array(
                "success" => false,
                "message" => $e->getMessage()
            );;
        }

        $emailArray = Emails::generateMessage("confirmPayment", $appID, null);
        $emailSent = false;
        if ($emailArray["result"]) {
            $address = $app->principal->crsid == null ? $app->principal->email : $app->principal->crsid . "@cam.ac.uk";
            $emailSent = Emails::sendEmail($address, $config["ticket_email"], $config["complete_event_date"], null /*$config["ticket_email"]*/, $emailArray["subject"], $emailArray["body"]);
        }
        file_put_contents("../../receipts/payment_" . ApplicationUtils::appIDZeros($appID) . "_" . time() . "_" . "_confirm.txt", $emailArray["body"] == null ? "Error" : $emailArray["body"]);
    }

    return array(
        "success" => true,
        "message" => "Payment succesfully added"
    );
}

function payNameChange($ncid, $hash)
{
    global $config;

    $db = Database::getInstance();

    if ($db->paymentIsDuplicate($hash)) {
        return array(
            "success" => false,
            "message" => "Payment already processed"
        );
    }

    $nc = $db->getNameChange($ncid);
    if (!$nc) {
        return array(
            "success" => false,
            "message" => "Application ID was not found"
        );
    }

    $auth = $nc->authorised;

    $nc->paid = true;
    if ($auth) {
        $nc->authorised = true;
        $nc->complete = true;
    }

    $db->updateNameChange($nc);
    if ($auth) {
        $db->insertPayment($nc->app_id, $config['name_change_cost'], "bacs", true, "Name change payment", $hash);
        NameChange::performChange($nc);
        $nameChangeVariables = array(
            "\$nameChange->changeID" => $nc->name_change_id,
            "\$nameChange->appID" => $nc->app_id,
            "\$nameChange->ticketHash" => $nc->ticket_hash,
            "\$nameChange->oldName" => $nc->full_name,
            "\$nameChange->newName" => $nc->new_title . " " . $nc->new_first_name . " " . $nc->new_last_name,
            "\$nameChange->ticketHash" => $nc->ticket_hash,
            "\$nameChange->newCRSid" => $nc->new_crsid,
            "\$nameChange->newCollege" => $nc->new_college,
            "\$nameChange->newPhone" => $nc->new_phone,
            "\$nameChange->submissionEmail" => $nc->submitter_email,
            "\$nameChange->secret" => $nc->secret
        );

        //TODO correct name change email
        $app = $db->getAppInfo("app_id", $nc->app_id);

        $emailArray = Emails::generateMessage("nameChangeComplete", $nc->app_id, array($nameChangeVariables));
        $emailSent = false;
        if ($emailArray["result"]) {
            //If $app is null then we shouldn't enter this block because generateMessage should have failed, thus safe to reference object fields
            $address = $app->principal->crsid == null ? $app->principal->email : $app->principal->crsid . "@cam.ac.uk";
            $emailSent = Emails::sendEmail($address, $config["ticket_email"], $config["complete_event_date"], $config["ticket_email"], $emailArray["subject"], $emailArray["body"]);
            file_put_contents("../../receipts/name_change_" . $app->principal_id . "_" . time() . "_complete.txt", $emailArray["body"]);
        } else {
            return array(
                "success" => true,
                "message" => "Name change completed, unable to generate confirmation email."
            );
        }
        if ($emailSent) {
            return array(
                "success" => true,
                "message" => "Name change completed"
            );
        } else {
            return array(
                "success" => true,
                "message" => "Name change completed, unable to send confirmation email."
            );
        }
    } else {
        return array(
            "success" => true,
            "message" => "Name change payment succesfully added. Change not yet authorised."
        );
    }
}
