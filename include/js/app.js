//Preloader
$(window).on('load', function () {    
    if ($('#preloader').length) {
        $('#preloader').delay(100).fadeOut('slow', function () {
            $(this).remove();
        });
    }});


//Set up global variables.
let worldMap;
let countryCode;
let geoJSONLayer; 
let bounds;
let capitalMarker;
let boundaries = [
    [-85.18289, -262.795597],
    [84.937126, 232.296248]
];
let longitude;
let latitude;
let landmarkLayer;
let searchCount = 0;


//Set up map. 
function createMap() {
    worldMap = L.map('worldMap').setView([53.800755, -1.549077], 6);
    const attribution = 'Tiles &copy; Esri &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012';
    const tileURL = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}';
    const tiles = L.tileLayer(tileURL, {attribution});
    tiles.addTo(worldMap);
    worldMap.setMinZoom(3);
    worldMap.setMaxBounds(boundaries);
}

createMap();


//Set up icons.
var pin = L.icon({
    iconUrl: 'include/images/icons/push-pin.svg',
    iconSize: [60, 110],
    iconAnchor: [22, 94],
    popupAnchor: [-3, -76]
});

var capIcon = L.ExtraMarkers.icon({
    icon: 'fas fa-angle-double-down',
    markerColor: 'red',
    shape: 'circle',
    prefix: 'fa',
  });


//Set up boundaries. 
function polyStyle(feature) {
    return {
        fillColor: 'rgb(202,235,250)',
        weight: 2,
        opacity: 0.4,
        color: 'black',  //Outline color
        fillOpacity: 0
    };
}


//Get location of user. 
function getLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(locationSuccess, locationError);
    }
  }

function locationSuccess(pos) {
    console.log(pos);
    updateSelect(countryCode);
    onSelectChange(countryCode);
}

function locationError(err) {
    countryCode = 'GB';
    updateSelect(countryCode);
    onSelectChange(countryCode);
}

$(document).ready(function(){
    getLocation(); 
});


//Update select value.
function updateSelect(countryCode) {
    $('#countrySearch').val(countryCode);
}


//On change of select, get border coords and pan to the area. 
$('#countrySelect').change(function() {
    countryCode = $('#countrySelect').val();
    worldMap.removeLayer(geoJSONLayer); 
    onSelectChange(countryCode);
});

function onSelectChange(countryCode) {
    console.log(countryCode);
    getInfo(countryCode);
    $.ajax({
        url: "include/php/getBounds.php",
        type: 'POST',
        dataType: 'json',
        data: {
            countryCode: countryCode,
        },
        success: function(result) {
          if (result.status.name == "ok") {
            console.log(result);
          let countryCoordsJSON = result['data'];
          console.log(countryCoordsJSON);
          geoJSONLayer = L.geoJSON(countryCoordsJSON, {style: polyStyle});
          geoJSONLayer.addTo(worldMap);
          worldMap.fitBounds(geoJSONLayer.getBounds());
          $("#dataDisplay").hide();
          $("#info").css('animation', 'none');
          }
        },
    });
}



//Update select value with map click location.
worldMap.on('click', handleMapClick);

function handleMapClick(e) {
    let mapClickLat = e.latlng.lat;
    let mapClickLong = e.latlng.lng;
    $.ajax({
        url: "include/php/reverseGeocode.php",
        type: 'POST',
        dataType: 'json',
        data: {
            latitude: mapClickLat,
            longitude: mapClickLong 
        },
        success: function(result) {
          if (result.status.name == "ok") {
            console.log(result);
            countryCode = result.data[0].components["ISO_3166-1_alpha-2"];
            worldMap.removeLayer(geoJSONLayer); 
            updateSelect(countryCode);
            onSelectChange(countryCode);
          }
        },
    });
}


//Perform API calls to retreive data. 
function getInfo(countryCode) {
    console.log(searchCount);
    $.ajax({
        url: "include/php/getInfo.php",
        type: 'POST',
        dataType: 'json',
        data: {
            countryCode: countryCode, 
            count: searchCount
        },
        success: function(result) {
          if (result.status.name == "ok") {
            console.log(result);
            longitude = result.data.openCage.lnglat.lng;
            latitude = result.data.openCage.lnglat.lat;
            fillSelect(result);
            placeMarker(result);
            fillTitles(result);
            fillStats(result);
            fillWeather(result);
            fillPeople(result);
            fillCurrency(result);
            fillCovid(result);
            $("#info").css('animation', 'wiggle 0.7s linear both');
            searchCount++;
            console.log(searchCount);
          }
        },
    });
}



//Fill in Data
function fillSelect(result) {
    if (result.data.countryList) {
        $('#countrySelect').html('');
        $.each(result.data.countryList, function(index) {
            $('#countrySelect').append($("<option>", {
                value: result.data.countryList[index].code,
                text: result.data.countryList[index].name
            })); 
        });
    }
}


function placeMarker(result) {
    let long = result.data.openCage.lnglat.lng;
    let lat = result.data.openCage.lnglat.lat;
    if (worldMap.hasLayer(capitalMarker)) {
        worldMap.removeLayer(capitalMarker);
     }            
    capitalMarker = L.marker([lat, long], {icon: capIcon});
    capitalMarker.bindPopup(result.data.restCountries.capitalCity).openPopup(); //Can add new icon in here.
    worldMap.addLayer(capitalMarker);
}

function fillTitles(result) {
    $('#countryName').html(result.data.restCountries.name);
    $('#capitalCity').html(result.data.restCountries.capitalCity);
}

function fillStats(result) {
    $('#neighbourCountries').html(result.data.restCountries.borders.join(", "));
    $('#callingCode').html(result.data.restCountries.callingCodes);
    $('#driving').html(result.data.openCage.driveOn);
    $('#timeZone').html(result.data.openCage.timezone.short_name);
    $('#driving').html(result.data.openCage.driveOn['drive_on']);
    $('#flag').attr("src", result.data.restCountries.flag);
    let offset = result.data.openCage.timezone.offset_sec;
    let currentTime = getLocalTime(offset);
    $('#currentTime').html(currentTime);
    let sunrise = correctTimestamp(result.data.openCage.sun.rise.apparent, offset); 
    let sunset = correctTimestamp(result.data.openCage.sun.set.apparent, offset);
    $('#sunrise').html(sunrise);
    $('#sunset').html(sunset);
}

function fillWeather(result) {
    $('#weatherDesc').html(result.data.openWeather.weather.description);
    $('#temp').html(result.data.openWeather.temp.temp);
    $('#feelsLike').html(result.data.openWeather.temp.feels_like);
    $('#humidity').html(result.data.openWeather.temp.humidity);
    $('#windSpeed').html(result.data.openWeather.wind);
    $('#weatherSymbol').attr("src", 'http://openweathermap.org/img/wn/' + result.data.openWeather.weather.icon +'@2x.png');
}

function fillPeople(result) {
    $('#population').html(result.data.restCountries.population.toLocaleString('en-UK'));
    $('#language').html(result.data.restCountries.languages[0].name);
    $('#translation').html(result.data.translate.translatedText);
    $('#wiki1').html(result.data.wiki[0].title);
    $('#wiki1').attr('href', 'http://' + result.data.wiki[0].wikipediaUrl);
    $('#wiki2').html(result.data.wiki[1].title);
    $('#wiki2').attr('href', 'http://' + result.data.wiki[1].wikipediaUrl);
    $('#wiki3').html(result.data.wiki[2].title);
    $('#wiki3').attr('href', 'http://' + result.data.wiki[2].wikipediaUrl);
    if (result.data.imgur != null) {
        $('#imgurImg').attr("src", result.data.imgur.link);
    }
}

function fillCurrency(result) {
    $('#currency').html(result.data.restCountries.currencies[0].name);
    $('#currCode').html(result.data.restCountries.currencies[0].code);
    $('#currSymbol').html(result.data.restCountries.currencies[0].symbol);
    $('#currSymbol2').html(result.data.restCountries.currencies[0].symbol);
    $('#exchangeRate').html(result.data.exchangeRate.result.toFixed(2));
}

function fillCovid(result) {
    let affectedPop = result.data.covid[0].confirmed / result.data.restCountries.population * 100;
    affectedPop = affectedPop.toFixed(2);
    let recovered = result.data.covid[0].recovered / result.data.covid[0].confirmed * 100;
    recovered = recovered.toFixed(2);
    let dead = result.data.covid[0].deaths / result.data.covid[0].confirmed * 100;
    dead = dead.toFixed(2);
    $('#population2').html(result.data.restCountries.population.toLocaleString('en-UK'));
    $('#totalCases').html(result.data.covid[0].confirmed.toLocaleString('en-UK'));
    $('#popAffected').html(affectedPop);
    $('#recovered').html(result.data.covid[0].recovered.toLocaleString('en-UK'));
    $('#percRecovered').html(recovered);
    $('#deaths').html(result.data.covid[0].deaths.toLocaleString('en-UK'));
    $('#percDeaths').html(dead);
}


//On click of landmark icon, perform API call and display landmark icons. 
$('#landmark').click(function() {
    getLandmarks(longitude, latitude);
    worldMap.setView([latitude, longitude], 15)
});

function getLandmarks(longitude, latitude) {
    $.ajax({
        url: "include/php/getLandmarks.php",
        type: 'POST',
        dataType: 'json',
        data: {
            longitude: longitude,
            latitude: latitude 
        },
        success: function(result) {
          if (result.status.name == "ok") {
            console.log(result);
            let data = result.data.landMarks.Response.View[0].Result;
            let landmarksArr = [];
            for (let i=0; i<10; i++) {
                let landmarkLat = data[i].Location.DisplayPosition.Latitude;
                let landmarkLong = data[i].Location.DisplayPosition.Longitude;
                let landmarkMarker = L.marker([landmarkLat, landmarkLong]);
                landmarkMarker.bindPopup(data[i].Location.Name + ', ' + data[i].Location.LocationType).openPopup();
                landmarksArr.push(landmarkMarker);
            }
            if (worldMap.hasLayer(landmarkLayer)) {
                worldMap.removeLayer(landmarkLayer);
             }            
            landmarkLayer = L.layerGroup(landmarksArr);
            landmarkLayer.addTo(worldMap);
            }
          }
    });
}



//Computation Functions
function correctTimestamp(unix, offset) {
    let newUnix = unix + offset;
    let modDate = new Date(newUnix * 1000);
    let time = modDate.toLocaleTimeString('en-UK');
    return time;
}

function getLocalTime(offset) {
    console.log('offset = ' + offset);
    let currentUnix = Math.floor(Date.now() / 1000);
    console.log('current unix = ' + currentUnix);
    let newUnix = currentUnix + offset;
    console.log('new unix = ' + newUnix);
    let time = new Date(newUnix * 1000).toLocaleTimeString("en-UK");
    return time;
} 



//DOM Stuff.
$( "#close" ).click(function() {
    $("#dataDisplay").toggle();
    $("#info").css('animation', 'none');
});

$("#info").click(function() {
    $("#dataDisplay").toggle();
    $("#info").css('animation', 'none');
});


