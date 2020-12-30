<?php

ini_set('display_errors', 'On');
error_reporting('E_All');

//Global Variables
$longitude = $_REQUEST['longitude'];
$latitude = $_REQUEST['latitude'];

$curl = curl_init();
$url='https://reverse.geocoder.ls.hereapi.com/6.2/reversegeocode.json?apiKey=KEY!&mode=retrieveLandmarks&prox=' . $latitude . ',' . $longitude. ',8000';
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$result=curl_exec($ch);
curl_close($ch);
$decodeLandmarks = json_decode($result,true);
$output['data']['landMarks'] = $decodeLandmarks;


$output['status']['code'] = "200";
$output['status']['name'] = "ok";
echo json_encode($output); 

?>
