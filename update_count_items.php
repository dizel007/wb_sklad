<?php
require_once "functions/topen.php";
require_once "functions/functions.php";
require_once "wb_catalog.php"; // массиво с каталогов наших товаров
echo '<link rel="stylesheet" href="css/main_table.css">';
$arr_catalog = get_catalog_wb ();

foreach ($arr_catalog as $items) {
    $arr_skus[] = $items['barcode'];
}

$warehouseId = 34790;// ID склада ООО на ВБ

// $data = array("stocks" => array(
//     array("sku" => "4673728485587",
//           "amount" => 18),
//     array("sku" => "4673728485594",
//           "amount" => 48),
// ));

$sku= $_GET['BarCode'];
$amount= (int)$_GET['value_in_wb_bd'];

// $data = array("stocks" => array(
//     array("sku" => "4673728485587",
//           "amount" => 18)

// ));


// echo "<pre>";
// var_dump($data);

$one_item_quantity = array("sku"    => $sku,
                           "amount" => $amount);


$data = array("stocks" => array($one_item_quantity));

echo "<pre>";
var_dump($data);



// $js_data = json_encode($data, JSON_UNESCAPED_UNICODE);
// echo $js_data;
// die('Die pered');
$ch = curl_init('https://suppliers-api.wildberries.ru/api/v3/stocks/34790');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Authorization:' . $token_wb,
	'Content-Type:application/json'
));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);

$res = curl_exec($ch);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
curl_close($ch);

echo     'Результат обмена : '.$http_code. "<br>";

$res = json_decode($res, true);


// echo "<pre>";
// print_r($res);

// die('sssssss');

header('Location: get_ostatki_skladov_2.php');
exit();

