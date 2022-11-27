<?php 
require("glue.php");
session_start();
$builder = new CaptchaBuilder;
$builder->build();
$_SESSION["uid"] = $builder->getPhrase();
header('Content-type: image/jpeg');
$builder->output();
?>