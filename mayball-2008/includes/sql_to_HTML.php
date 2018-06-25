<?php

require_once('db_query.php');
require_once('result_to_HTML.php');

function sql_to_HTML($query, $con) {
    $result = db_query($query, $con);
    echo result_to_HTML($result);
    return $result;
}

?>