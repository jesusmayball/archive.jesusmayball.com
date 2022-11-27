<?php
require("auth.php");
global $config;


function endsWith($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}
	return (substr($haystack, -$length) === $needle);
}

$zip = new ZipArchive();
$file = dirname(__FILE__) . "/../receipts/receipts.zip";

if(file_exists($file)) {
	if($zip->open($file, ZIPARCHIVE::CHECKCONS) !== TRUE) {
		echo "Unable to Open $file";
		exit();
	}
} else {
	if($zip->open($file, ZIPARCHIVE::CM_PKWARE_IMPLODE) !== TRUE) {
		echo "Could not Create $file";
		exit();
	}
}

$dirLoc = dirname(__FILE__) . "/../receipts/";
$dir = opendir($dirLoc);
$i = 0;
while ($f = readdir($dir)) {
	if ($f != "." && $f != ".." && endsWith($f, ".txt")) {
		if(file_exists($dirLoc . $f) && is_readable($dirLoc . $f)) {
			$zip->addFile($dirLoc . $f, $f);
			$i++;
		}
	}
}

$readmeMessage = $config["complete_event"] . "\n";
$readmeMessage .= "Archive contains $i receipts up to " . date(DateTime::RSS, time()) . "\n";
$zip->addFromString("README.txt", $readmeMessage);

$zip->close();
header('Content-Type: application/zip');
header('Content-Length: ' . filesize($file));
header('Content-Disposition: attachment; filename="receipts_' . date("M-d-Y", time()). '.zip"');
readfile($file);
unlink($file);