<?php

/**
 * Holds the information about a particular application.
 * The principal field is a TicketInfo object of the main applicant.
 * The guests field is an array of TicketInfo objects, one for each guest.
 * 
 * @author James G
 */
class Application extends ApplicationRecord {
    public $totalCost = null;
    public $totalPaid = null;
    public $principal = null;
    public $guests = null;
    public $payments = null;
}

?>
