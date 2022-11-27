<?php

/**
 * Gives a structured record of ticket info for a ticket. Must be json-encodable,
 * so adding extra fields or methods could mess things up...
 * Made it extend from TicketRecord to provide the useful ticket_type field.
 *
 * @author James G
 */
class Ticket extends TicketRecord {
    
    public $ticket_type = null;
    
}
