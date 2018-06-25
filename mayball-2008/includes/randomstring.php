<?php
//randomstring.php
//Generates a n length random string.
function assign_rand_value($num)
{

// accepts 0 - 61
  $characters = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0");

return $characters[$num];
}

function get_rand_letters($length)
{
  if($length>0)
  {
  $rand_id="";
    for($i=1; $i<=$length; $i++)
    {
      mt_srand((double)microtime() * 1000000);
      $num = mt_rand(0,61);
      $rand_id .= assign_rand_value($num);
   }
  }
return $rand_id;
}
?>