<?php
$api_key = "7XVKYG820ddd140aea6052b3V3yOW7rC"; // Ganti dengan API Key dari RajaOngkir
$origin = 444; // ID kota Surabaya (cek dokumentasi RajaOngkir starter)

$destination = $_POST["destination"];
$weight = $_POST["weight"];
$courier = $_POST["courier"];

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "origin=$origin&destination=$destination&weight=$weight&courier=$courier",
  CURLOPT_HTTPHEADER => array(
    "content-type: application/x-www-form-urlencoded",
    "key: $api_key"
  ),
));

$response = curl_exec($curl);
curl_close($curl);
echo $response;
?>
