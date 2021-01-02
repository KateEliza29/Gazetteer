<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="include/css/style.css">
    <!--<link rel="stylesheet" href="include/vendors/leaflet/leaflet.css">
    <script src="include/vendors/leaflet/leaflet.js"></script>-->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="include/vendors/bootstrap.min.css">
    <script src="include/vendors/bootstrap.bundle.min.js"></script>
    <title>Gazetteer</title>

</head>
<body>
    <div id="wrapper"> 
        <div id="preloader"></div>
        <!-- Use PHP to import the geoJSON file, order it alphabetically and populate the select search bar with the names -->
        <nav class="justify-content-center top-nav">
            <h1>Gazetteer</h1>
            <?php 
                ini_set('display_errors', 'On');
                error_reporting('E_All');

                $string = file_get_contents('include/js/countries.json');
                $json = json_decode($string, true);
                $baseArray = $json['features'];
                foreach ($baseArray as $key => $value) {
                    $countries[$value['properties']['ISO_A2']] = $value['properties']['ADMIN'];
                }
                array_multisort($countries, SORT_ASC);
                ?>
                <select id="countrySearch" class="m-4">  
                <option value="chooseCountry" selected disabled>Choose a Country</option>  
                <?php 
                    foreach ($countries as $key => $value) {
                ?>
                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>    
                <?php    
                    }
                ?> 
            </select>
</nav>
        <!-- Map -->
        <div id="worldMap"></div>
        <img src="include/images/icons/info.svg" id="info">
        <img src="include/images/icons/landmark.svg" id="landmark">

        <!-- Data display -->
        <section id="dataDisplay" class="p-3">
            <button type="button" id="close" class="btn-close" aria-label="Close"></button>
            <h2 id="countryName" class="text-center m-3"></h2>
            <h3 id="capitalCity" class="text-center m-3"></h3>
            <nav class="container">
                <div class="nav nav-pills nav-fill" id="nav-tab" role="tablist">
                    <a class="nav-link active" id="nav-stats-tab" data-bs-toggle="tab" href="#nav-stats" role="tab" aria-controls="nav-stats" aria-selected="true"><img class="tabIcon" src="include/images/icons/stats.svg"></a>
                    <a class="nav-link" id="nav-weather-tab" data-bs-toggle="tab" href="#nav-weather" role="tab" aria-controls="nav-weather" aria-selected="false"><img class="tabIcon" src="include/images/icons/weather.svg"></a>
                    <a class="nav-link" id="nav-people-tab" data-bs-toggle="tab" href="#nav-people" role="tab" aria-controls="nav-people" aria-selected="false"><img class="tabIcon" src="include/images/icons/people.svg"></a>
                    <a class="nav-link" id="nav-currency-tab" data-bs-toggle="tab" href="#nav-currency" role="tab" aria-controls="nav-currency" aria-selected="false"><img class="tabIcon" src="include/images/icons/currency.svg"></a>
                    <a class="nav-link" id="nav-news-tab" data-bs-toggle="tab" href="#nav-news" role="tab" aria-controls="nav-news" aria-selected="false"><img class="tabIcon" src="include/images/icons/virus.svg"></a>
                </div>
            </nav>
            <section class="tab-content container" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-stats" role="tabpanel" aria-labelledby="nav-stats-tab">
                    <h4 class="m-3">Statistics</h4>
                    <table class="table table-borderless">
                        <tr>
                            <td class="left">Neighbouring Countries: </td>
                            <td class="right" id="neighbourCountries"></td>
                        </tr>
                        <tr>
                            <td class="left">Calling Code: </td>
                            <td class="right">+<span id="callingCode"></span></td>
                        </tr>
                        <tr>
                            <td class="left">Drive On: </td>
                            <td class="right" id="driving"></td>
                        </tr>
                        <tr>
                            <td class="left">Time Zone: </td>
                            <td class="right" id="timeZone"></td>
                        </tr>
                        <tr>
                            <td class="left">Current Time: </td>
                            <td class="right" id="currentTime"></td>
                        </tr>
                        <tr>
                            <td class="left">Sunrise: </td>
                            <td class="right" id="sunrise"></td>
                        </tr>
                        <tr>
                            <td class="left">Sunset: </td>
                            <td class="right" id="sunset"></td>
                        </tr>
                        <tr>
                            <td colspan="2"><img id="flag" class="image" src=""></td>
                        </tr>
                    </table>
                </div>
                <div class="tab-pane fade" id="nav-weather" role="tabpanel" aria-labelledby="nav-weather-tab">
                    <h4 class="m-3">Weather</h4>
                    <table class="table table-borderless">
                        <tr>
                            <td class="left">Description: </td>
                            <td class="right" id="weatherDesc"></td>
                        </tr>
                        <tr>
                            <td class="left">Temperature: </td>
                            <td class="right"><span id="temp"></span>&#176 c</td>
                        </tr>
                        <tr>
                            <td class="left">Feels Like: </td>
                            <td class="right"><span id="feelsLike"></span>&#176 c</td>
                        </tr>
                        <tr>
                            <td class="left">Humidity: </td>
                            <td class="right"><span id="humidity"></span>%</td>
                        </tr>
                        <tr>
                            <td class="left">Wind Speed: </td>
                            <td class="right"><span id="windSpeed"></span> m/s</td>
                        </tr>
                        <tr>
                            <td colspan="2"><img id="weatherSymbol" class="image" src=""></td>
                        </tr>
                    </table>
                </div>
                <div class="tab-pane fade" id="nav-people" role="tabpanel" aria-labelledby="nav-people-tab">
                    <h4 class="m-3">People</h4>

                    <table class="table table-borderless">
                        <tr>
                            <td class="left">Population: </td>
                            <td class="right" id="population"></td>
                        </tr>
                        <tr>
                            <td class="left">Language: </td>
                            <td class="right" id="language"></td>
                        </tr>
                        <tr class="bord">
                            <td colspan="2">"Hello, how are you today?"</td>
                        </tr>
                        <tr>
                            <td colspan="2" id="translation"></td>
                        </tr>
                        <tr class="bord">
                            <td colspan="2"><h4>Wikipedia Links</h4></td>
                        </tr>
                        <tr>
                            <td colspan="2"><a id="wiki1" href="" target="_blank"></a></td>
                        </tr>
                        <tr>
                            <td colspan="2"><a id="wiki2" href="" target="_blank"></a></td>
                        </tr>
                        <tr>
                            <td colspan="2"><a id="wiki3" href="" target="_blank"></a></td>
                        </tr>
                        
                        <tr>
                            <td colspan="2"><img id="imgurImg" class="image" src=""></td>
                        </tr>
                    </table>
                </div>
                <div class="tab-pane fade" id="nav-currency" role="tabpanel" aria-labelledby="nav-currency-tab">
                    <h4 class="m-3">Currency</h4>
                    <table class="table table-borderless">
                        <tr>
                            <td class="left">Currency: </td>
                            <td class="right" id="currency"></td>
                        </tr>
                        <tr>
                            <td class="left">Currency Code: </td>
                            <td class="right" id="currCode"></td>
                        </tr>
                        <tr>
                            <td class="left">Symbol: </td>
                            <td class="right" id="currSymbol"></td>
                        </tr>
                        <tr>
                            <td class="left">Exchange Rate: </td>
                            <td class="right">You can exchange £1 for <span id="currSymbol2"> </span><span id="exchangeRate"></span></td>
                        </tr>
                    </table>
                </div>
                <div class="tab-pane fade" id="nav-news" role="tabpanel" aria-labelledby="nav-news-tab">
                    <h4 class="m-3">Covid</h4>
                    <table class="table table-borderless">
                        <tr>
                            <td class="left">Population: </td>
                            <td class="right" id="population2"></td>
                        </tr>
                        <tr>
                            <td class="left">Total Cases: </td>
                            <td class="right" id="totalCases"></td>
                        </tr>
                        <tr>
                            <td class="left">% of Population Affected: </td>
                            <td class="right"><span id="popAffected"></span>%</td>
                        </tr>
                        <tr>
                            <td class="left">Total Recovered: </td>
                            <td class="right" id="recovered"></td>
                        </tr>
                        <tr>
                            <td class="left">% Recovered: </td>
                            <td class="right"><span  id="percRecovered"></span>%</td>
                        </tr>
                        <tr>
                            <td class="left">Total Deaths: </td>
                            <td class="right" id="deaths"></td>
                        </tr>
                        <tr>
                            <td class="left">% Deaths: </td>
                            <td class="right"><span id="percDeaths"></span>%</td>
                        </tr>
                    </table>
                </div>
            </section>
        </section>
    </div>
<script src="include/vendors/jquery-3.5.1.min.js"></script>
<script src="include/js/app.js"></script>

</body>
</html>
