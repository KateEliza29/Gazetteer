<?php                
    $countryData = json_decode(file_get_contents("include/js/countries.json"), true);
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



    $output['status']['code'] = "200";
    $output['status']['name'] = "ok";
    $output['status']['description'] = "success";
    $output['data'] = $country;

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode($output);

?>