<?php

class NameChange
{

	static function performChange($namechange)
	{
		$db = Database::getInstance();
		$ticketInfo = $db->getTicketInfo("hash_code", $namechange->ticket_hash);
		if (!$ticketInfo) {
			return false;
		}

		$ticket = TicketRecord::buildFromTicket($ticketInfo);
		$ticket->title = $namechange->new_title;
		$ticket->first_name = $namechange->new_first_name;
		$ticket->last_name = $namechange->new_last_name;

		/* Note, these 3 conditionals are only for the primary ticket holder
	    *  Other guests won't have phone numbers or a college but if they have
	    *  them they should be removed since they'll be inconsistent.
	    */
		if ($namechange->new_crsid !== null && $namechange->new_crsid != "") {
			$ticket->crsid = $namechange->new_crsid;
			$ticket->email = $namechange->new_crsid . "@cam.ac.uk";
		} else {
			$ticket->crsid = null;
			$ticket->email = null;
		}
		if ($ticket->diet === null) {
			$ticket->diet = "None";
		}


		if ($namechange->new_college !== null && $namechange->new_college != "") {
			$ticket->college = $namechange->new_college;
		} else {
			$ticket->college = "";
		}

		if ($namechange->new_phone !== null && $namechange->new_phone != "") {
			$ticket->phone = $namechange->new_phone;
		} else {
			$ticket->phone = "";
		}

		$ticket->address = "";

		return $db->updateTicket($ticket, true);
	}
}
