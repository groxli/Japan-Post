#!/usr/bin/php
<?
// Need to set the timezone for the database calls to work properly.
date_default_timezone_set('Asia/Tokyo');

// Make sure to change the server/IP address and/or the port as needed.
// Same goes for name and password.
$db_connection = null;        // Database connection string
        $server = "127.0.0.1:3306";            // Database server
        $database = "";          // The database being connected to
        $username = "";          // The database username
        $password = "";          // The database password
        $CONNECTED = false;           // Determines if connection is established

// Get the log file handle and load file handle.
$logHandle = fopen("./process.log", "a");
fwrite($logHandle, date('Y-m-d H:i:s') . " opening load file\n");
$handle = fopen("../data/KENOUT.csv", "r");

// Make sure we can connect to the DB.
fwrite($logHandle, date('Y-m-d H:i:s') . " opening db connection\n");
try
{
	// Create connection to MYSQL database
	// Fourth true parameter will allow for multiple connections to be made
	$db_connection = mysql_connect ($server, $username, $password, TRUE);
	mysql_select_db ($database);
	if (!$db_connection)
	{
		throw new Exception('MySQL Connection Database Error: ' . mysql_error());
	} else {
		$CONNECTED = true;
		echo "ABLE TO CONNECT.  OK.\n";
	}
}
catch (Exception $e)
{
	echo $e->getMessage();
}

// Load in the todofuken IDs into an array which can be 
// referred to when loading in each address from the input file.
$tdArray = array(); // instanciate todofuken array

$results = mysql_query('SELECT todofuken_id, kanji FROM todofuken');
while ($row = mysql_fetch_assoc($results))
{
	$outStr = $row['todofuken_id'] . "," . $row['kanji'] . "\n";
	$tdArray[$row['kanji']] = $row['todofuken_id'];
	//fwrite($extractHandle, $outStr);
	//echo $outStr;
}

$fileRowCount = 0; // keep track of how many rows in the load file
                   // were processed.
$fileCharCount = 0;  // keep track of the total number of characters in
                    // the load file that were processed.

while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	$num = count($data); // get the number of columns available from file
	//echo "$num fields in line $row:\n";
	$row++;
	$isTodofuken = "FALSE"; // variable for keeping track of
                               // whether character instance use in
                              // prefectural string of address
	$theChar; // variable for holding the character
                 // to be inserted into db

	// Get the 3 and 4 zip codes from the current row:
	$zip3 = substr($data[1], 0, 3); // 2nd column.  Substring to first 3 integers.
	$zip7 = $data[2]; // 3rd column  TODO: we have to pad it.
	$theKenID = $tdArray[$data[6]];
//echo "The ken: ". $theKenID;
//echo "The zip3: ". $zip3. "\n";

	// create the SQL string to insert into the the postal_address
	// table.
	$insertSQL = "INSERT INTO postal_address (todofuken_id, postal_3, postal_7, addr_1, addr_1_reading, addr_2, addr_2_reading) VALUES($theKenID, $zip3, $zip7, '".$data[7]."', '".$data[4]."', '".$data[8]."', '".$data[5]."')";
//echo $insertSQL . "\n";
	mysql_query($insertSQL);

	// Start looping through the needed columns in the KEN_ALL file
	// and get the relevant todofuken ID from tdArray and 
	// start inserting the data into the database.
	for ($c=6; $c < 9; $c++) {
		//echo $data[$c] . "\n";
		$numChars = strlen(utf8_decode($data[$c]));
		//echo $data[$c] . "\n";
		for ($char=0; $char<$numChars; $char++) 
		{
			$theChar = mb_substr($data[$c], $char, 1, 'UTF-8'); 
			if ($c == 6) {
				$isTodofuken = "TRUE";
			} else {
				$isTodofuken = "FALSE";
			}

//TODO: need to get the proper kanji id of the joyo kanji

	// Get the database ID of the last inserted  address so that we know
	// how to properly associate each glyph with a given postal
	// address.
	$lastInsertSQL = "SELECT LAST_INSERT_ID() FROM postal_address";
	$latestID = mysql_num_rows(mysql_query($lastInsertSQL));
//echo "DEBUG DEBUG DEBUG THE LAST ID IS:      " . $latestID ."\n";

			//Build SQL and insert char into table
			$insertSQL = "INSERT INTO address_glyph (address_id, glyph, is_todofuken) VALUES ( ".$latestID.", '". $theChar . "', '". $isTodofuken . "')"; 
			//echo $insertSQL . "\n";
			mysql_query($insertSQL);
			$fileCharCount++;
		}
	}
	echo $fileRowCount . "|";
	$fileRowCount++;
//        echo "\n";
}

fwrite($logHandle, date('Y-m-d H:i:s') . " " . $fileRowCount . " rows from load file processed.\n");
fwrite($logHandle, date('Y-m-d H:i:s') . " " . $fileCharCount . " characters from load file processed.\n");
fwrite($logHandle, date('Y-m-d H:i:s') .  " closing db connection and load file\n");

mysql_close($db_connection);
fclose($handle);
fclose($logHandle);
?>
