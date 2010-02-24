#!/usr/bin/php
<?
date_default_timezone_set('Asia/Tokyo');
$extractHandle = fopen("../extracts/tokyo_kanji.csv", "a");

// Make sure to change the server/IP address and/or the port as needed.
// Same goes for name and password.
$db_connection = null;        // Database connection string

 // Attempt connectio
        $server = "127.0.0.1:3306";            // Database server
        $database = "";          // The database being connected to
        $username = "";          // The database username
        $password = "";          // The database password
        $CONNECTED = false;           // Determines if connection is established

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

$results = mysql_query('SELECT td.kanji, pa.addr_1, pa.addr_2 FROM postal_address pa, todofuken td WHERE pa.todofuken_id = td.todofuken_id and td.kanji = \'東京都\' AND pa.addr_2 NOT LIKE \'%以下に掲載がない場合%\' AND pa.addr_2 NOT LIKE \'（次のビルを除く）%\'');
//$results = mysql_query('SELECT td.kanji, pa.addr_1, pa.addr_2 FROM postal_address pa, todofuken td WHERE pa.todofuken_id = td.todofuken_id and td.kanji = \'東京都\' AND pa.addr_2 NOT LIKE \'%以下に掲載がない場合%\' OR pa.addr_2 NOT LIKE \'\'（次のビルを除く）%\'');
while ($row = mysql_fetch_assoc($results)) {
  $outStr = $row['kanji'] . ",". $row['addr_1'] ."," . $row['addr_2'] . "\n";
  fwrite($extractHandle, $outStr);
echo $outStr;
}

mysql_close($db_connection);

fclose($extractHandle);
echo "\nDone.\n";
?>
