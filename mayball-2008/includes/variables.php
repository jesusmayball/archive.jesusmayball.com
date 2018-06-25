<?php
//Variables.php
//This file includes all the things that might change during the lifetime of the website

function allowed_ip() {
  
  $range1 = array("fromip"=>'172.31.0.0',"toip"=>'172.31.255.255');
  $range2 = array("fromip"=>'192.168.0.0',"toip"=>'192.168.255.255'); 
  
  return array($range1, $range2);
}

function colleges() {
  return array("Christs"=>"Christ's", "Churchill"=>"Churchill", "Clare"=>"Clare College", "ClareHall"=>"Clare Hall", "Corpus"=>"Corpus Christi", "Darwin"=>"Darwin", "Downing"=>"Downing", "Emma"=>"Emmanuel", "Fitz"=>"Fitzwilliam", "Girton"=>"Girton", "Caius"=>"Gonville and Caius", "Homerton"=>"Homerton", "HughesHall"=>"Hughes Hall", "Jesus"=>"Jesus", "Kings"=>"King's", "LucyCavendish"=>"Lucy Cavendish", "Magdalene"=>"Magdalene", "NewHall"=>"New Hall", "Newnham"=>"Newnham", "Pembroke"=>"Pembroke", "Peterhouse"=>"Peterhouse", "Queens"=>"Queens'", "Robinson"=>"Robinson", "Catz"=>"St Catharine's", "Edmunds"=>"St Edmund's", "Johns"=>"St John's", "Selwyn"=>"Selwyn", "Sidney"=>"Sidney Sussex", "Trinity"=>"Trinity", "TitHall"=>"Trinity Hall", "Wolfson"=>"Wolfson");
  
}

function ticket_types() {
   return array("Normal"=>"Normal", "Priority"=>"Priority Queuing", "Dining"=>"Dining Option");
}

function ticket_availability() {
  $availability = array("Normal"=>1250, "Priority"=>103, "Dining"=>130);
  $waitingList  = array("Normal"=>0, "Priority"=>0, "Dining"=>0);
  $near         = array("Normal"=>300, "Priority"=>25, "Dining"=>30);
  
  return array("Available"=>$availability, "Waiting"=>$waitingList, "Near"=>$near);
}
function change_over_date() {
  //Date/Time that ticket prices go up by £10
  //Midnight (GMT) on 3rd March 2008
  return mktime(00,00,00,03,14,2009,0);
}

function ticket_prices() {

  if (change_over_date() >= time()) {
    $prices = array("Normal"=>99, "Priority"=>114, "Dining"=>129);
  }
  else {
    $prices = array("Normal"=>109, "Priority"=>124, "Dining"=>139);
  }
  return $prices;
}

function release_date () {
  //Change the return value of this function to the first date at which people outside the Jesus College network may buy tickets.
  return mktime(00,00,00,02,03,2007,0); //Midnight (GMT) on 3rd February 2007
}

function confirm_time () {
  //Change the return value of this function to the maximum amount of time allowed to confirm your application.
  return mktime(00,00,00,01,02,1970,0); //Epoch + 1 day
}

function payment_time () {
  //Change the return value of this function to the maximum amount of time allowed to pay for your tickets.
  return mktime(00,00,00,01,08,1970,0); //Epoch + 7 days
}

?>