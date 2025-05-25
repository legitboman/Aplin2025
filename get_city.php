<?php
header('Content-Type: application/json');

$api_key = "7XVKYG820ddd140aea6052b3V3yOW7rC"; // Ganti dengan API Key Anda

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.rajaongkir.com/starter/city",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["key: $api_key"]
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    // Fallback data jika API error
    $cities = [
        ['city_id' => '444', 'city_name' => 'Kota Surabaya'],
        ['city_id' => '445', 'city_name' => 'Kota Malang']
    ];
} else {
    $result = json_decode($response, true);
    $cities = [];
    
    if(isset($result['rajaongkir']['results'])) {
        foreach($result['rajaongkir']['results'] as $city) {
            $cities[] = [
                'city_id' => $city['city_id'],
                'city_name' => $city['type'].' '.$city['city_name']
            ];
        }
    }
}

echo json_encode($cities);
?>