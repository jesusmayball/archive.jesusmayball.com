<?php
//Classes used by the ticket booking system
// - Application: Uses one person (booker) and and array of people.
// - Person: may be referenced by one application and one ticket

require_once('randomstring.php');
require_once('db_login.php');
require_once('db_query.php');
require_once('variables.php');
require_once('quote_smart.php');

class Application {

  var $applicant;
  var $guests;
  
  var $applicationID;
  var $dateTime;
  var $totalPrice;
  var $authString;
  var $confirmedDate; 
  var $paidDate;
  var $collectedDate;
  

  function Application($applicant, $guests) {

    $this->applicant     = $applicant;
    $this->guests        = $guests;
    $this->authString    = get_rand_letters(20);
    
    $this->calc_price();    
  }

  function calc_price() {
    // Loads the array of prices from variables.php
    $ticket_prices = ticket_prices();
    $this->totalPrice    = 0;
    foreach($this->guests as $guest) {
      $this->totalPrice += $ticket_prices[$guest->ticketType];
    }

    return $this->totalPrice;
  }

  function insert() {
    $dbcon = db_login();

    while(true) {
      //Check that the Authorization string isn't in use
      $query  = "SELECT COUNT(AuthString) AS 'num' FROM applications WHERE AuthString = '".$this->authString."'";
      $result = db_query($query, $dbcon);

      $data = mysql_fetch_array($result, MYSQL_ASSOC);

      if($data['num'] == 0) break;
      $this->authString = get_rand_letters(20);
    }

    //Insert the data into the applications table
    $query_format = "INSERT INTO applications VALUES ('', '%s', '%s', '%s', '%s', '%s', NOW(), %d, '%s', '', '', '');";
    $query = sprintf($query_format,
                    $this->applicant->firstName,
                    $this->applicant->surname,
                    $this->applicant->email,
                    $this->applicant->mobile,
                    $this->applicant->college,
                    $this->totalPrice,
                    $this->authString );

    db_query($query, $dbcon);

    $this->applicationID =  mysql_insert_id($dbcon);
    
    foreach($this->guests as $guest) {
        $query_format = "INSERT INTO tickets VALUES ( '', '%s', '%s', '%s' );\n";
        $query = sprintf($query_format,
                        $this->applicationID,
                        $guest->name,
                        $guest->ticketType );
      db_query($query, $dbcon);
    }
    mysql_close($dbcon);
  }

  function show() {
    // Outputs an HTML table containing the form data

    $colleges = colleges();
    $op  = "<table>";
    $op .= "<tr><td colspan=\"2\"> <h3>Applicant<h3></td></tr>";
    $op .= "<tr><td colspan=\"2\">Application ID: ".$this->applicationID."</td></tr>";
    $op .= "<tr><td colspan=\"2\"> <hr /></td></tr>";
    $op .= "<tr><td width=\"400\">First Name </td><td width=\"400\">".$this->applicant->firstName."</td></tr>";
    $op .= "<tr><td>Surname </td><td>".$this->applicant->surname."</td></tr>";
    $op .= "<tr><td>Email Address </td><td>".$this->applicant->email."</td></tr>";
    $op .= "<tr><td>Mobile </td><td>".$this->applicant->mobile."</td></tr>";
    $op .= "<tr><td>College </td><td>".$colleges[$this->applicant->college]."</td></tr>";
    $op .= "<tr><td>Total Price </td><td><b>&pound; ".$this->totalPrice."</b></td></tr>";
    $op .= "<tr><td>Confirmed On </td><td>".$this->confirmedDate."</td></tr>";
    $op .= "<tr><td>Paid On </td><td>".$this->paidDate."</td></tr>";
    $op .= "<tr><td>Collected On </td><td>".$this->collectedDate."</td></tr>";
    $op .= "<tr><td colspan=\"2\"> <hr /><h3>Tickets</h3></td></tr>";
    $op .= "<tr><td colspan=\"2\">Number of tickets: ".sizeof($this->guests)."</td></tr>";
    $op .= "<tr><td colspan=\"2\"> <hr /></td></tr>";
    $op .= "<tr><td><h4>Name<h4></td><td><h4>Ticket Type<h4></td></tr>";

    foreach($this->guests as $guest) {
      $op .= "<tr><td>".$guest->name."</td><td>".$guest->ticketType."</td></tr>";
    }
    $op .= "</table>";
    return $op;
  }
}

?>
