<?php
require_once "functions/torpen.php";
require_once "functions/functions.php";
require_once "functions/ozon_catalog.php"; // массиво с каталогов наших товаров

echo '<link rel="stylesheet" href="css/main_table.css">';


$date_query_ozon = date('Y-m-d');
$date_query_ozon = date('Y-m-d', strtotime('-4 day', strtotime($date_query_ozon))); // начальную датк на 4 дня раньше берем

$dop_days_query = 14; // захватывает 14 дней после сегодняшней даты

//  Получаем фактические заказы с сайта озона (4 дня доо и 14 после сегодняшне йдаты)
$res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, 'awaiting_packaging', $dop_days_query);

foreach ($res['result']['postings'] as $items) {
    foreach ($items['products'] as $product) {
        $arr_products[$product['offer_id']] = @$arr_products[$product['offer_id']] + $product['quantity'];
    }
    
}
// Получаем количество товара из 1С 
$arr_sklad = json_decode(file_get_contents('uploads/array_items.json'),true);

// Получаем наш каталог (все товары)
$arr_catalog = get_catalog_ozon ();

// FПолучаем фактическое количество товаров указанное на складе ОЗОН
$ozon_dop_url = 'v1/product/info/stocks-by-warehouse/fbs';


foreach ($arr_catalog as $item) {
$send_data ='{"sku": ["'.$item['sku'].'"]}';
$res = send_injection_on_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );
$arr_tovarov_na_site[$item['article']] = $res['result'][0]['present'] - $res['result'][0]['reserved'];
}

// echo "<pre>";
// print_r($arr_tovarov_na_site);
// print_r($arr_products);

// die('tuts');

// $send_data =  array(
//     "stocks" => array(
//         array(
//             "offer_id" => "7262-КП",
//             "product_id" =>   "985937305",
//             "stock" => 22,
//            )
//        )
//     );

//   $send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;


// echo "<pre>";
// print_r($send_data);


// $ozon_dop_url = "v1/product/import/stocks";
// $res11 = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url );

// echo "<pre>";
// print_r($res11);

$market='ozon';

echo <<<HTML
<form action="update_count_items_ozon.php" method="post">
<table>
<tr class="prods_table">

    <td>артикул</td>
    <td>Наименование</td>
    <td>SKU</td>

    <td>Кол-во продано</td>
    <td>Oстатки из 1С</td>
    <td>Кол-во на OZON<br>(Остаток)</td>
     <td>Рекомендуемый/<br>(Будуший остаток)</td>
    <td>пп</td>
    <td>артикул</td>

</tr>
HTML;

unset ($items); // чистим эту переменную

foreach ($arr_catalog as $items) {
   
    $article = $items['article'];
    $name = $items['name'];
    $quantity = $arr_tovarov_na_site[$article];
    $barCode =  $items['sku'];
    
    isset($arr_products[$article])?$sell_count = $arr_products[$article]:$sell_count = 0;

        foreach ($arr_sklad as $key1C => $items1Csklad){
            if (mb_strtoupper($article) == mb_strtoupper($key1C)){
        // Проверяем есть ли такой товар на складе 
                if (isset($items1Csklad[$market])) {
                    $quantity_1c = $items1Csklad[$market]; // 
                } else {
                    $quantity_1c = 0;
                }
            
            if ($quantity_1c>0){
            $value_in_wb_bd = $quantity_1c - $sell_count-1; // делаем запас на 1 шт;
            } else {
                $value_in_wb_bd = $quantity_1c - $sell_count;
            }

            break;
            }  else {
                $quantity_1c = 0; 
                $value_in_wb_bd = 0; 
            }
        }

// Подсвечиваем неточные количсетва
                    if ($quantity > $quantity_1c - $sell_count) {
                        $alarm_class= "alarm";
                    }else {
                        $alarm_class= ""; 
                    }

echo <<<HTML
<tr class="prods_table">

    <td>$article</td>
    <td>$name</td>
    <td>$barCode</td>
  
    <td class="text14">$sell_count</td>
    <td class="text14">$quantity_1c</td>
    <td class="$alarm_class text14">$quantity</td>
    <td><input type="number" name="_value_$article" value=$value_in_wb_bd></td>
    <td><input checked type="checkbox" name="_check_$article" value="1"></td>
    <input hidden type="text"  name = "_BarCode_$article" value="$barCode">
    <td class="text14">$article</td>

</tr>

HTML;
}
echo <<<HTML
</table>

<input type="submit" value="START">
</form>


HTML;


die('DIE START GET OSTATKI');
