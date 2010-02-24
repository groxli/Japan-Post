#!/usr/bin/php
<?
date_default_timezone_set('Asia/Tokyo');
$logHandle = fopen("./process.log", "a");
fwrite($logHandle, date('Y-m-d H:i:s') . " opening load file\n");
$handle = fopen("../data/todofuken.csv", "r");

$fileRowCount = 0; // keep track of how many rows in the load file
                   // were processed.
$fileCharCount = 0;  // keep track of the total number of characters in
                    // the load file that were processed.

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
echo $fileRowCount . "|";
        $fileRowCount++;
        //$num = count($data);
        //echo "$num fields in line $row:\n";
//       for ($c=0; $c < $num; $c++) {
 //           echo $data[$c] . "<br />\n";
  //            // to be inserted into db
              //Build SQL and insert char into table
   //     }
$str = mb_convert_encoding($data[0], "UTF-8", "auto");
              $insertSQL = "INSERT INTO todofuken (todofuken_id, kanji) VALUES (".$fileRowCount.", '". $str . "')"; 
              echo $insertSQL . "\n";
mysql_query($insertSQL);
           // $results = $db_connection->query($insertSQL);

        //$row++;
//        echo "\n";
    }

fwrite($logHandle, date('Y-m-d H:i:s') . " " . $fileRowCount . " rows from load file processed.\n");
fwrite($logHandle, date('Y-m-d H:i:s') . " " . $fileCharCount . " characters from load file processed.\n");
fwrite($logHandle, date('Y-m-d H:i:s') .  " closing db connection and load file\n");

mysql_close($db_connection);

fclose($handle);
fclose($logHandle);
?>
