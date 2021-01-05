<?php
ini_set('memory_limit', '1024M');
ini_set('display_errors', 'On');
error_reporting('E_All');

//Global Variables
$countryCode = $_REQUEST['countryCode'];
$count = $_REQUEST['count'];

//Fill Select
if ($count < 1) {
    $countryData = json_decode(file_get_contents("../js/countries.json"), true);
    $country = [];
    foreach ($countryData['features'] as $feature) {
        $temp = null;
        $temp['code'] = $feature["properties"]['ISO_A2'];
        $temp['name'] = $feature["properties"]['ADMIN'];
        array_push($country, $temp);
    }
    usort($country, function ($item1, $item2) {
        return $item1['name'] <=> $item2['name'];
    });
    
    $output['data']['countryList'] = $country;
}



//REST Countries API Call
$curl = curl_init();
$url='https://restcountries.eu/rest/v2/alpha/' . $countryCode . '?fields=name;callingCodes;capital;population;latlng;timezones;borders;currencies;languages;flag';
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$result=curl_exec($ch);
curl_close($ch);
$decodeRestCountries = json_decode($result,true);

$output['data']['restCountries']['name'] = $decodeRestCountries['name'];
$output['data']['restCountries']['capitalCity'] = $decodeRestCountries['capital'];
$output['data']['restCountries']['borders'] = $decodeRestCountries['borders'];
$output['data']['restCountries']['callingCodes'] = $decodeRestCountries['callingCodes'];
$output['data']['restCountries']['currencies'] = $decodeRestCountries['currencies'];
$output['data']['restCountries']['languages'] = $decodeRestCountries['languages'];
$output['data']['restCountries']['population'] = $decodeRestCountries['population'];
$output['data']['restCountries']['flag'] = $decodeRestCountries['flag'];

//REST Countries Variables for use in other API calls
$capitalCityPre = $decodeRestCountries['capital'];
$capitalCity = str_replace(" ", "+", $capitalCityPre);
$currency = $decodeRestCountries['currencies'][0]['code']; 
$language = $decodeRestCountries['languages'][0]['iso639_1']; 
$countryPre = $decodeRestCountries['name'];
$country = str_replace(" ", "+", $countryPre);


//OpenCage API Call
$url='https://api.opencagedata.com/geocode/v1/json?q=' . $capitalCity . '&key=591712a3b55c49629fbc003abb3dbf1e&limit=1';
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$result=curl_exec($ch);
curl_close($ch);
$decodeOpenCage = json_decode($result,true);

$output['data']['openCage']['lnglat'] = $decodeOpenCage['results'][0]['geometry'];
$output['data']['openCage']['driveOn'] = $decodeOpenCage['results'][0]['annotations']['roadinfo'];
$output['data']['openCage']['timezone'] = $decodeOpenCage['results'][0]['annotations']['timezone'];
$output['data']['openCage']['sun'] = $decodeOpenCage['results'][0]['annotations']['sun'];

//OpenCage Variables for use in other API calls. 

$capitalLat = $decodeOpenCage['results'][0]['geometry']['lat'];
$capitalLong = $decodeOpenCage['results'][0]['geometry']['lng'];



//Open Weather API Call 
$url='api.openweathermap.org/data/2.5/weather?q=' . $capitalCity . '&appid=738aaab36db4be1b29ce7959b28a40e9&units=metric';
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$result=curl_exec($ch);
curl_close($ch);
$decodeOpenWeather = json_decode($result,true);

$output['data']['openWeather']['temp'] = $decodeOpenWeather['main'];
$output['data']['openWeather']['weather'] = $decodeOpenWeather['weather'][0];
$output['data']['openWeather']['wind'] = $decodeOpenWeather['wind']['speed']; 



//Imgur API Call
$url = 'https://api.imgur.com/3/gallery/search?q=' . $country . '+landscape';
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Client-ID 22cc0f0a2015d5f"]);

$result=curl_exec($ch);
curl_close($ch);
$decodeImgur = json_decode($result,true);

$output['data']['imgur'] = $decodeImgur['data'][0]['images'][0]; 



//Wikipedia API Call
$url = 'http://api.geonames.org/wikipediaSearchJSON?q=' . $country . '&title&maxRows=3&username=katebrown2901';
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$result=curl_exec($ch);
curl_close($ch);
$decodeWiki = json_decode($result,true);

$output['data']['wiki'] = $decodeWiki['geonames'];



//Covid-19 Global Tracker API call
$url = 'https://covid-19-data.p.rapidapi.com/country/code?code=' . $countryCode;
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["x-rapidapi-host: covid-19-data.p.rapidapi.com", "x-rapidapi-key: 3bddbe3c1emsh9ac607d46b03348p11fe22jsn45cb3b218e79"]);


$result = curl_exec($ch);
curl_close($ch);
$decodeCovid = json_decode($result,true);

$output['data']['covid'] = $decodeCovid; 



//Google Translate API Call
$url = "https://language-translation.p.rapidapi.com/translateLanguage/translate?text=Hello!%20How%20are%20you%20today%3F&type=plain&target=" . $language;
$ch = curl_init();

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["x-rapidapi-host: language-translation.p.rapidapi.com", "x-rapidapi-key: 3bddbe3c1emsh9ac607d46b03348p11fe22jsn45cb3b218e79"]);

$result = curl_exec($ch);
$err = curl_error($curl);
curl_close($ch);

$decodeTranslate = json_decode($result,true);
$output['data']['translate'] = $decodeTranslate;  




//Open Exchange Rates API Call
$curl = curl_init();
$url='https://api.exchangerate.host/convert?from=GPB&to=' . $currency;
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$result=curl_exec($ch);
curl_close($ch);
$decodeExchangeRate = json_decode($result,true);
$output['data']['exchangeRate'] = $decodeExchangeRate;



//header('Content-Type: application/json; charset=UTF-8');
$output['status']['code'] = "200";
$output['status']['name'] = "ok";
echo json_encode($output); 
