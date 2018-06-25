<?php

require_once('db_login.php');


function college_tickets() {

  $colleges = colleges();
  $collegeData = array();
  
  $result = confirmed_tickets("applications.College, COUNT(*) AS 'Number' ", " GROUP BY applications.college");
  for($i = 0; $i < mysql_num_rows($result); $i++) {
    $row = mysql_fetch_array($result);
    $college = $colleges[$row['College']];
    $num = $row['Number'];
    $collegeData[$college] = $num;
    
  }
  return $collegeData;

}

function type_tickets() {

    $ticketTypes = ticket_types();
    $ticketData = array();

    $result = confirmed_tickets("tickets.TicketType, COUNT(*) AS 'Number' ", " GROUP BY tickets.TicketType");
    
    for($i = 0; $i < mysql_num_rows($result); $i++) {
        $row = mysql_fetch_array($result);
        $type = $row['TicketType'];
        $num = $row['Number'];
        $ticketData[$type] = $num;

    }
    return $ticketData;
}

function count_tickets($clauses) {
  //Note, clauses must produce a single row result table, otherwise only the first row will be given
  
    $result = confirmed_tickets("COUNT(TicketID) AS 'tot'", $clauses);
    $arr = mysql_fetch_array($result, MYSQL_ASSOC);  
    return $arr["tot"];
}

function confirmed_tickets($fields, $clauses) {
  //Ensure that the $clauses argument include a leading space if it is non-blank.
  
  $query = "SELECT ".$fields."
       FROM applications
       INNER JOIN tickets 
       ON applications.ApplicationID = tickets.ApplicationID
       WHERE Confirmed <> 0";
  
  $query .= $clauses;
  $dbcon = db_login();
  $result = db_query($query, $dbcon);
  mysql_close($dbcon);
  
  return $result;
}

function available_tickets() {
    $sold = type_tickets();
    $limits = ticket_availability();
    $limits = $limits['Available'];
    $available = array();
    foreach($sold as $type=>$number) {
        $available[$type] = $limits[$type] -$number;
    }
    return $available;
}

?>