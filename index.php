<?php
$dbhost = 'MYSQL HOST';
$dbuser = 'DATABASE USER';
$dbpass = 'DATABASE PASSWORD';
$dbname = 'DATABASE NAME';

@$link = mysql_connect($dbhost,$dbuser,$dbpass);
if(mysql_errno())
{
    die('Could not connect: ' . mysql_error());
}
@mysql_select_db($dbname,$link);
if(mysql_errno())
{
    die('Database name: ' . mysql_error());
}
mysql_query("SET NAMES 'utf8'");
        
if(isset($_GET['export']) && $_GET['export'] != "")
{
    $set_table = $_GET['export'];
    //get all of the tables
    if($set_table == '*')
    {
        $tables = array();
        $result = mysql_query('SHOW TABLES');
        while($row = mysql_fetch_row($result))
        {
            $tables[] = $row[0];
        }
    }
    else
    {
        $tables = array($set_table);
    }
    
    $return='';
    //cycle through
    foreach($tables as $table)
    {
            $result = mysql_query('SELECT * FROM '.$table);
            $num_fields = mysql_num_fields($result);

            //$return.= 'DROP TABLE '.$table.';'; // Drop section alre
            $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
            $return.= "\n\n".$row2[1].";\n\n";

            for ($i = 0; $i < $num_fields; $i++) 
            {
                    while($row = mysql_fetch_row($result))
                    {
                            $return.= 'INSERT INTO '.$table.' VALUES(';
                            for($j=0; $j<$num_fields; $j++) 
                            {
                                    $row[$j] = addslashes($row[$j]);
                                    $row[$j] = str_replace("\n","\\n",$row[$j]);
                                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                                    if ($j<($num_fields-1)) { $return.= ','; }
                            }
                            $return.= ");\n";
                    }
            }
            $return.="\n\n\n";
    }

    //save file
    $handle = fopen('db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
    if(@fwrite($handle,$return))
    {
        echo "OK";
    }
    else
    {
        echo "Error!";
    }
    fclose($handle);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>MySQL Db Exporter</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style>
            body *
            {
                font-size: 12px;
                font-family: Arial;
            }
            table
            {
                border-collapse: collapse;
            }
            th, td
            {
                border: 1px solid #d0d0d0;
                padding: 4px;
            }
        </style>
    </head>
    <body>
        <div>
            <?php
            if(!$_SERVER['QUERY_STRING'])
            {
                echo '<h1>Mysql Db exporter!</h1>';
                echo '<table>';
                $result = mysql_query("SELECT TABLE_NAME,SUM(TABLE_ROWS) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $dbname . "' GROUP BY TABLE_NAME;");
                if(mysql_num_rows($result))
                {
                    echo '<tr>';
                        echo '<th>Tables</th>';
                        echo '<th>Rows</th>';
                        echo '<th>&nbsp;</th>';
                    echo '</tr>';
                    $count = 0; 
                    while($row = mysql_fetch_row($result))
                    {
                        echo '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td><td><a href="?export=' . $row[0] . '">Export</a></td></tr>';
                        $count++; 
                    }
                    echo '<tr><th colspan="2">Number of table (s) : ' . $count .'</th><td><a href="?export=*">Export</a></td></tr>';
                }
                else
                {
                    $result = mysql_query('SHOW TABLES');
                    if(mysql_num_rows($result))
                    {
                        echo '<tr>';
                            echo '<th>Table</th>';
                        echo '</tr>';
                        while($row = mysql_fetch_row($result))
                        {
                            echo '<tr><td>' . $row[0] . '</td></tr>';
                        }
                    }
                }
                echo '</table>';
            }
            else
            {
            }
            ?>
        </div>
    </body>
</html>
