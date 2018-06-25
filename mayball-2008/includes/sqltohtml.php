<?php
function SQLtoHTML($sSQL) {
//Returns an HTML table from a SQL statement
    if (!$oConn = db_login()) {
        $sRetVal = mysql_error();
    }
    else {
        
        
            if (!$result = mysql_query($sSQL, $oConn)) {
                $sRetVal = mysql_error();
            }
            else {
                $sRetVal = "<table border=1>\n";
                /*$sRetVal .= "<tr><th colspan=" . mysql_num_fields($result) . ">";
                $sRetVal .= mysql_field_table($result,0) . "</th></tr>";
                $sRetVal .= "<tr>";*/
                $i=0;
                
                while ($i < mysql_num_fields($result)) {
                    $sRetVal .= "<th>" . mysql_field_name($result, $i) . "</th>";
                    $i++;
                }
                $sRetVal .= "</tr>";
                while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    $sRetVal .= "\t<tr>\n";
                    foreach ($line as $col_value) {
                        $sRetVal .= "\t\t<td>$col_value</td>\n";
                    }
                    $sRetVal .= "\t</tr>\n";
                }
                $sRetVal .= "</table>\n";
                mysql_free_result($result);
            }
        
        mysql_close($oConn);
    }
    return ($sRetVal);
}

function DBtoHTML($result) {

    $sRetVal = "<table border=1>\n";
    $sRetVal .= "<tr><th colspan=" . mysql_num_fields($result) . ">";
    $sRetVal .= mysql_field_table($result,0) . "</th></tr>";
    $sRetVal .= "<tr>";
    $i=0;
    
    while ($i < mysql_num_fields($result)) {
        $sRetVal .= "<th>" . mysql_field_name($result, $i) . "</th>";
        $i++;
    }
    $sRetVal .= "</tr>";
    
    while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $sRetVal .= "\t<tr>\n";
        foreach ($line as $col_value) {
            $sRetVal .= "\t\t<td>$col_value</td>\n";
        }
        $sRetVal .= "\t</tr>\n";
    }
    $sRetVal .= "</table>\n";
    
    return ($sRetVal);
}
?>