<?php

//Access the functions to check names and colleges
class Applicant {
  //Sections:
  // - Consructor Function that accesses:
  // - Data checks

  var $firstName;
  var $surname;
  var $email;
  var $mobile;
  var $college;
  var $description;
  var $error;

  function Applicant($firstName, $surname, $email, $mobile, $college, $description) {

    $dbcon = db_login(); // Required to make mysql_real_escape_string work

    $this->firstName   = $firstName;
    $this->surname     = $surname;
    $this->email       = $email;
    $this->mobile      = $mobile;
    $this->college     = $college;
    $this->description = $description;

    // All checks return false if there is no error

    $check_firstName  = $this->check_string($this->firstName, $description." First Name", $dbcon);
    $check_surname    = $this->check_string($this->surname,   $description." Surname", $dbcon);
    $check_email      = $this->check_email($this->email, $dbcon);
    $check_mobile     = $this->check_mobile($this->mobile);
    $check_college    = $this->check_college($college);

    if (($check_firstName || $check_surname || $check_email || $check_mobile || $check_college) == false) { $error = false; }
    else {
        $error = "";
        if ($check_firstName)  $error .= $check_firstName;
        if ($check_surname)    $error .= $check_surname;
        if ($check_email)      $error .= $check_email;
        if ($check_mobile)     $error .= $check_mobile;
        if ($check_college)    $error .= $check_college;
      }

      $this->error = $error;
      mysql_close($dbcon);
  }

  function showApplicant() {
    $string = $this->description.":<br />
              First Name: ".$this->firstName."<br />
              Surname: ".$this->surname."<br />
              Email: ".$this->email."<br />
              College: ".$this->college."<br />";
    return $string;
  }

  // Checks that names have no nasty characters
  function check_string(&$string, $fieldName, $dbcon) {
    if ($string == "") {
      $error = "The ".$fieldName." field is blank. <br />";
    }
    else {
      $string = quote_smart($string, false, $dbcon);
    }
  return $error;
  }

  // Validates & sanitizes the email address
  function check_email(&$email, $dbcon) {

    if (preg_match ("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/", $email, $matches)) {
      $error = false;
      $email = quote_smart(strtolower($matches[0]), false, $dbcon);
    }
    else {
      if ($email == "") {
        $error = "Email address is blank. <br />";
      }
      else {
        $error = "Invalid Email Address. <br />";
        $email = "";
      }
    }
    return $error;
  }

  function check_mobile(&$mobile) {
    $mobile = preg_replace("/[^0-9+]/", "", $mobile);
    $error = false;
    return $error;
  }

  // Checks that the college is one of the ones on the list.
  function check_college(&$college) {

    $colleges = array("Christs", "Churchill", "Clare", "ClareHall", "Corpus", "Darwin", "Downing", "Emma", "Fitz", "Girton", "Caius", "Homerton", "HughesHall", "Jesus", "Kings", "LucyCavendish", "Magdalene", "NewHall", "Newnham", "Pembroke", "Peterhouse", "Queens", "Robinson", "Catz", "Edmunds", "Johns", "Selwyn", "Sidney", "Trinity", "TitHall", "Wolfson");

    if($college == "") {
      $error = "Please select a College from the drop-down list. <br /> ";
    }
    else {
      $match = false;
      for($i=0; $i<31; $i++) {
        if (strcmp($college, $colleges[$i]) == 0) {
          $match = true;
        }
      }
      if ($match) {
        $error = false;
      }
      else {
        $college = "";
        $error = "Please select a College from the drop-down list. <br /> ";
      }
    }
    return $error;
  }
}
?>
