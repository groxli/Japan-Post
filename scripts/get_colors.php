#!/usr/bin/php
<?
date_default_timezone_set('Asia/Tokyo');
$extractHandle = fopen("../extracts/color_count.csv", "a");

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

// Set up an array of colors to be counted/summarized from the
// Japan Post addresses:
$colorArray = array();

array_push($colorArray, "青", "赤", "黄", "黒", "緑", "紫", "白", "銀","金","藍","碧");

foreach ($colorArray as $theColor) {

$theSQL = "SELECT count(*) AS theCount, glyph FROM address_glyph WHERE glyph = '".$theColor."'";
//echo "sql is: ".$theSQL."\n";
$results = mysql_query($theSQL);
while ($row = mysql_fetch_assoc($results)) {
  $outStr = $row['glyph'] .",". $row['theCount'].  "\n";
  fwrite($extractHandle, $outStr);
//echo $outStr;
}
}

mysql_close($db_connection);

fclose($extractHandle);
echo "\nDone.\n";
?>
