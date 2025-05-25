<?php
header('Content-Type: application/json');

// Example static city list - replace with your actual data source
$cities = [
    ['city_id' => '444', 'city_name' => 'Surabaya'],
    ['city_id' => '445', 'city_name' => 'Malang'],
    ['city_id' => '446', 'city_name' => 'Sidoarjo'],
    ['city_id' => '447', 'city_name' => 'Kediri'],
    ['city_id' => '448', 'city_name' => 'Madiun']
];

echo json_encode($cities);
?>