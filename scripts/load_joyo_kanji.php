#!/usr/bin/php
<?
date_default_timezone_set('Asia/Tokyo');
$logHandle = fopen("./process.log", "a");
fwrite($logHandle, date('Y-m-d H:i:s') . " opening load file\n");
$handle = fopen("../data/joyo_kanji.csv", "r");

$fileRowCount = 0; // keep track of how many rows in the load file
                   // were processed.

fwrite($logHandle, date('Y-m-d H:i:s') . " opening db connection\n");

// Make sure to change the server/IP address and/or the port as needed.
// Same goes for name and password.
$db_connection = null;        // Database connection string
        $server = "127.0.0.1:3306";            // Database server
        $database = "";          // The database being connected to
        $username = "";          // The database username
        $password = "";          // The database password
        $CONNECTED = false;           // Determines if connection is established

 // Attempt connection
            try
            {
                // Create connection to MYSQL database
                // Fourth true parameter will allow for multiple connections to be made
                $db_connection = mysql_connect ($server, $username, $password, TRUE);
                mysql_select_db ($database);
                if (!$db_connection)
                {
                    throw new Exception('MySQL Connection Database Error: ' . mysql_error());
                }
                else
                {
                    $CONNECTED = true;
			echo "ABLE TO CONNECT.  OK.\n";
                }
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
            }

  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
//echo $fileRowCount . "|";
        $fileRowCount++;
        //$num = count($data);
        //echo "$num fields in line $row:\n";
	// Determine whether the joyo kanji is a secondary school level glyph.
	$isSecondary = "FALSE";
	$elementaryGrade = "";
	if($data[4] == "S") {
		$isSecondary = "TRUE";
		$elementaryGrade = 99;
	} else {
		$elementaryGrade = $data[4];	
	}
	$glyph = mb_convert_encoding($data[1], "UTF8", "auto");
	$insertSQL = "INSERT INTO kanji (kanji_num, new_glyph, old_glyph, num_strokes, grade) VALUES (".$data[0] . ", '". $glyph ."', '". $data[2] . "', ". $data[3] . ", ". $elementaryGrade . ")";
        echo $insertSQL . "\n";
	mysql_query($insertSQL) OR die(mysql_error());

        //$row++;
//        echo "\n";
    }

fwrite($logHandle, date('Y-m-d H:i:s') . " " . $fileRowCount . " rows from load file processed.\n");
fwrite($logHandle, date('Y-m-d H:i:s') .  " closing db connection and load file\n");

mysql_close($db_connection);

fclose($handle);
fclose($logHandle);
?>
