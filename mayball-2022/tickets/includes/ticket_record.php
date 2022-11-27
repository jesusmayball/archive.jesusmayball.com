<?php

/**
 * An object to mirror a ticket record in the database ticket table.
 * WARNING: the variables MUST have the same name as their respective fields in
 * the database 'tickets' table, and there MUST be no extra fields in this class.
 *
 * @author James G
 */
class TicketRecord
{

	public $ticket_id = null;
	public $app_id = null;
	public $title = null;
	public $first_name = null;
	public $last_name = null;
	public $crsid = null;
	public $email = null;
	public $college = null;
	public $phone = null;
	public $address = null;
	public $hash_code = null;
	public $ticket_type_id = null;
	public $price = null;
	public $valid = null;
	public $deleted = null;
	public $created_at = null;
	public $updated_at = null;
	public $collected_at = null;
	public $entered_at = null;
	public $entered_via = null;
	public $charity_donation = null;
	public $diet = null;
	public $printed_program = null;
	// public $tailored_gin = null;
	public $is_refunded = null;
	public $is_ballot = null;

	public static function buildFromTicket($ticket)
	{
		if (get_class($ticket) != "Ticket") {
			return null;
		}
		$ticketRecord = new TicketRecord();
		$ticketRecord->ticket_id = $ticket->ticket_id;
		$ticketRecord->app_id = $ticket->app_id;
		$ticketRecord->title = $ticket->title;
		$ticketRecord->first_name = $ticket->first_name;
		$ticketRecord->last_name = $ticket->last_name;
		$ticketRecord->crsid =  $ticket->crsid;
		$ticketRecord->email =  $ticket->email;
		$ticketRecord->college =  $ticket->college;
		$ticketRecord->phone =  $ticket->phone;
		$ticketRecord->address =  $ticket->address;
		$ticketRecord->hash_code =  $ticket->hash_code;
		$ticketRecord->ticket_type_id =  $ticket->ticket_type_id;
		$ticketRecord->price =  $ticket->price;
		$ticketRecord->valid =  $ticket->valid;
		$ticketRecord->deleted =  $ticket->deleted;
		$ticketRecord->created_at =  $ticket->created_at;
		$ticketRecord->updated_at =  $ticket->updated_at;
		$ticketRecord->collected_at =  $ticket->collected_at;
		$ticketRecord->entered_at =  $ticket->entered_at;
		$ticketRecord->entered_via =  $ticket->entered_via;
		$ticketRecord->charity_donation =  $ticket->charity_donation;
		$ticketRecord->is_ballot = $ticket->is_ballot;
		$ticketRecord->printed_program = $ticket->printed_program;
		$ticketRecord->diet = $ticket->diet;
		return $ticketRecord;
	}
}
