<?php
$cast = mysqli_connect('localhost', 'root', 'wert', 'cast') or die('DB cast was not connected');
$icecast = mysqli_connect('localhost', 'root', 'wert', 'icecast') or die('DB icecast was not connected');
date_default_timezone_set('Europe/Minsk');
mysqli_set_charset($cast, "utf8");
mysqli_set_charset($icecast, "utf8");
mysqli_query($cast, "TRUNCATE channels");
mysqli_query($cast, "TRUNCATE streams");
mysqli_query($cast, "TRUNCATE cha_sty");
mysqli_query($cast, "TRUNCATE stations");
$res = mysqli_query($icecast, "SELECT r.id, r.name_mount, r.name_radio, s.stream,
                                      b.bitrate, r.image, r.info_radio, r.home_url, r.source, r.timeadd
     FROM radio_mp3 AS r
     JOIN stream AS s ON r.stream_id = s.id
     JOIN bitrate AS b ON r.bitrate_id = b.id
     WHERE r.stream_id <> 0
     ORDER BY r.id
    ");
$total_channels = 0;
while ( $obj = mysqli_fetch_object($res) ) {
    print_r('<br>' . $obj->id . ' | ' . $obj->name_radio);
    $id = $obj->id;
    $byfly = $obj->name_mount;
    $channel = mysqli_escape_string($icecast,$obj->name_radio);
    $format = strtolower($obj->stream);
    $bitrate = $obj->bitrate;
    $pic = $obj->image;
    $description = mysqli_escape_string($icecast, $obj->info_radio);
    $tmp_www = $obj->home_url;
    $url = $obj->source;
    $created = DateTime::createFromFormat('U', $obj->timeadd);
    if (!($created instanceof DateTime)) {
        $created = new DateTime();
    }
    $created_format = $created->format('Y-m-d H:i:s');
    try {
        $query = "INSERT INTO channels
                (id, station, channel, created, pic, stream, description, tmp_www )
               VALUES
                ('$id', '$id', '$channel', '$created_format', 'img_auto_$id', '$id', '$description', '$tmp_www')
        ";
        mysqli_query($cast, $query);
        $query = "INSERT INTO streams
                (id, channel, bitrate, format, created, byfly, url)
               VALUES
                ('$id', '$id', '$bitrate', '$format', '$created_format', '$byfly', '$url')
        ";
        mysqli_query($cast, $query);
        //create station table
        $www = str_replace('http://', '',$tmp_www);
        $www = str_replace('www.', '', $www);
        mysqli_query($cast, "INSERT INTO stations
                            (id, station, www, created, description)
                         VALUES
                            ( '$id', '$channel', '$www', '$created_format', '$description' )
        ");
    }
    catch (Exception $e) {
        print_r($e);
    }
    $total_channels++;
}

$query = " SELECT id, channel, created, description, tmp_www
           FROM channels
           GROUP BY tmp_www
           ORDER BY id
";

$res = mysqli_query($cast, $query);
while ($obj = mysqli_fetch_object($res)) {
    $id = $obj->id;
    $description = mysqli_escape_string($cast, $obj->description);
    $station = $obj->channel;
    $created = $obj->created;
    $www = str_replace('http://', '', $obj->tmp_www);
    $www = str_replace('www.', '', $www);
    mysqli_query($cast, "INSERT INTO stations
                            (id, station, www, created, description)
                         VALUES
                            ( '$id', '$station', '$www', '$created', '$description' )
    ");
}


$res = mysqli_query($icecast, "SELECT styleID, radioID FROM r_s WHERE 1 ORDER BY radioID");
while ($obj = mysqli_fetch_object($res)) {
    $sid = $obj->styleID;
    $rid = $obj->radioID;
    mysqli_query($cast, "INSERT INTO cha_sty (chaID, styID) VALUES ('$rid', '$sid') ");
}
$res = mysqli_query($icecast, "SELECT number, style FROM styles WHERE 1 ORDER BY number");
while ($obj = mysqli_fetch_object($res)) {
    $id = $obj->number;
    $style = mysqli_escape_string($icecast, $obj->style);
    mysqli_query($cast, "INSERT INTO styles (id, style) VALUES ('$id', '$style') ");
}
mysqli_close($cast);
mysqli_close($icecast);
?>