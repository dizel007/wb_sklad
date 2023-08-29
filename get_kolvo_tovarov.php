<?php
require_once "functions/torpen.php";


$ozon_dop_url = 'v1/product/info/stocks-by-warehouse/fbs';
$send_data ='{"sku": ["985937305"]}';

$res = send_injection_on_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );


echo "<pre>";
print_r($res);


function send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url ) {
 
	$ch = curl_init('https://api-seller.ozon.ru/'.$ozon_dop_url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Api-Key:' . $token,
		'Client-Id:' . $client_id, 
		'Content-Type:application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

	curl_close($ch);
	
	$res = json_decode($res, true);

   echo     'Результат обмена : '.$http_code. "<br>";
   
    return($res);	

}
