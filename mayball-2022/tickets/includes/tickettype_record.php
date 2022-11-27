<?php

/**
 * An object to mirror the fields of a ticket_type record in the database.
 * WARNING: the variables MUST have the same name as their respective fields in
 * the database 'tickets' table, and there MUST be no extra fields in this class.
 *
 * @author James
 */
class TicketTypeRecord {
    public $ticket_type_id = null;
    public $ticket_type = null;
    public $price = null;
    public $available = null;
    public $maximum = null;
    public $restricted = null;
    public $use_other_max = null;
}

?>
