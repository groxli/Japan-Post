#!/usr/bin/php
<?php
$postalhandle = fopen("./listjp.txt", "r");
$joyohandle = fopen("./listjoyo.txt", "r");

$postalArray = array();
$joyoArray = array();

while (($data = fgetcsv($postalhandle, 1000, ",")) !== FALSE) {
//echo $fileRowCount . "|";
//echo $data[0] . "\n";
	array_push($postalArray, $data[0]);
 //       $fileRowCount++;
}

while (($data = fgetcsv($joyohandle, 1000, ",")) !== FALSE) {
	array_push($joyoArray, $data[0]);
//echo $data[0];
}

foreach ($postalArray as $postal) {
	if(in_array($postal, $joyoArray)) {
		echo "IN,".$postal;
	} else {
		echo "OUT,".$postal;
	}
	echo "\n";
}

fclose($postalhandle);
fclose($joyohandle);
?>
