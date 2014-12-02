<?php
/**
 * Score file download.
 *
 * @author TimmyXu
 * @since 2.0.0
 */

$grade = $_GET['grade'];
$name = $_GET['filename'];
$filepath = dirname(__FILE__) ."/score/ranking_".$grade.'_'.$name.'.csv';
$file = fopen($filepath,"r+");
Header("Content-type: application/octet-stream");
Header("Accept-Ranges: bytes");
Header("Accept-Length: ".filesize($filepath));
Header("Content-Disposition: attachment; filename=ranking_".$grade."_".$name.".csv");
$buffer=1024;
while(!feof($file)){
    $file_data=fread($file,$buffer);
    echo $file_data;
}
fclose($file);