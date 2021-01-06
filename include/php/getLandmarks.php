<?php

ini_set('display_errors', 'On');
error_reporting('E_All');

//Global Variables
$longitude = $_REQUEST['longitude'];
$latitude = $_REQUEST['latitude'];

//$url='https://reverse.geocoder.ls.hereapi.com/6.2/reversegeocode.json?apiKey=LQB09zWJr1LcBdz-Y1nY8lI6rid58s7cOkJcNpi5zP4&mode=retrieveLandmarks&prox=' . $latitude . ',' . $longitude. ',8000';

//$url = 'https://discover.search.hereapi.com/v1/discover?apiKey=LQB09zWJr1LcBdz-Y1nY8lI6rid58s7cOkJcNpi5zP4&at=' . $latitude . '%2C' . $longitude . '&q=tourist+attractions&limit=10';
$url = 'https://discover.search.hereapi.com/v1/discover?apiKey=LQB09zWJr1LcBdz-Y1nY8lI6rid58s7cOkJcNpi5zP4&q=historical+monument&in=circle:' . $latitude . ',' . $longitude . ';r=10000&limit=10&lang=en';
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