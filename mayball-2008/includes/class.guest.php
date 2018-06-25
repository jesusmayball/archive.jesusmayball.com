<?php
require_once('variables.php');

//Access the functions to check names and colleges
class Guest {
  //Sections:
  // - Consructor Function that accesses:
  // - Data checks

  
  
  var $name;
  var $ticketType;
  var $error;

  function Guest($name, $ticketType, $description) {
   
    $dbcon = db_login(); // Required to make mysql_real_escape_string work
  
    $this->name        = $name;
    $this->ticketType  = $ticketType;
    $this->description = $description;

    // All checks return false if there is no error

    
    $check_name       = $this->check_string($this->name, $description, $dbcon);
    $check_ticketType = $this->check_ticketType($ticketType, $description);
    
    if (($check_name || $check_ticketType) == false) { $error = false;}
    else {
        $error = "";
        if ($check_name)       $error .= $check_name;
        if ($check_ticketType) $error .= $check_ticketType;
    }
      $this->error = $error;
      mysql_close($dbcon);
  }

  function showGuest() {
    $string = $this->description.":<br />
              Name: ".$this->Name."<br />              
              Ticket Type: ".$this->ticketType."<br />";
    return $string;
  }

  // Checks that names have no characters other that letters, spaces and dashes
  function check_string(&$string, $fieldName, $dbcon) {
    if ($string == "") {
      $error = "The ".$fieldName." Name field is blank <br />";
    }
    else {
      $string = quote_smart($string, false, $dbcon);
    }
  return $error;
  }

  // Checks that the ticket type is one of the ones on the list.
  function check_ticketType(&$ticketType, $fieldname) {

    $ticketTypes = ticket_types();    
    
    if($ticketType == "") {
      $error = "Please select a Ticket Type from the drop-down list for ".$fieldname.". <br /> ";
    }
    else {
      $match = false;
      foreach($ticketTypes as $type=>$fullname) {
        if (strcmp($ticketType, $type) == 0) {
          $match = true;
        }
      }
      if ($match) $error = false;
      else {
        $ticketType = "";
        $error = "Please select a Ticket Type from the drop-down list for ".$fieldname.". <br /> ";
      }
    }
    return $error;
  }
}
?>