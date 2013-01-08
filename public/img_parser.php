<?php
$cast = mysqli_connect('localhost', 'root', 'wert', 'cast') or die('DB cast was not connected');
$query = "SELECT id FROM channels WHERE 1";
$res = mysqli_query($cast, $query);
while ( $obj = mysqli_fetch_object($res) ) {
    $img = curl_init('http://shoutcast.byfly.by/resize.php?id='.$obj->id.'&w=100&h=100');
    $fp = fopen("c:\\test_images\\img_auto_".$obj->id.".png", 'wb');
    curl_setopt($img, CURLOPT_FILE, $fp);
    curl_setopt($img, CURLOPT_HEADER, 0);
    curl_exec($img);
    curl_close($img);
    fclose($fp);
}

?>