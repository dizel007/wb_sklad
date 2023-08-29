<?php
require_once "functions/torpen.php";
require_once "functions/functions.php";
require_once "razbor_post_array.php"; // массиво с каталогов наших товаров
require_once "functions/ozon_catalog.php"; // массиво с каталогов наших товаров

echo '<link rel="stylesheet" href="css/main_table.css">';


// получаем массив СКУ-количество (для обновления остатков на складе)
$update_items_quantity = razbor_post_massive($_POST);

// Получаем наш каталог (все товары)
$arr_catalog = get_catalog_ozon ();

// добавляем к массиву артикул
foreach ($update_items_quantity as &$item) {
    foreach ($arr_catalog as $prods) {
     if ($item ['sku'] == $prods['sku']) {
        $item['article'] = $prods['article'];
     }
    }
}

unset($item);

// Формируем массив для метода ОЗОНа по обновления остатков
foreach ($update_items_quantity as $prods) {
    $temp_data_send[] = 
        array(
            "offer_id" =>  $prods['article'],
            "product_id" =>   $prods['sku'],
            "stock" => $prods['amount'],
           )
       
       
        ;


}
$send_data =  array("stocks" => $temp_data_send);

echo "<pre>";
print_r($send_data);

// $send_data_good =  array(
//     "stocks" => array(
//         array(
//             "offer_id" => "7262-КП",
//             "product_id" =>   "985937305",
//             "stock" => 22,
//            )
//        )
//     );

$send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;


echo "<pre>";
print_r($send_data);

// die('UPDATE_DIE');

$ozon_dop_url = "v1/product/import/stocks";
$res11 = send_injection_on_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );

echo "<pre>";
print_r($res11);



die('sssssss');

exit();

