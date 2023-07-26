<?php
require_once "functions/topen.php";
require_once "functions/functions.php";
require_once "wb_catalog.php"; // массиво с каталогов наших товаров

require_once "parce_excel_sklad.php"; // массиво с каталогов наших товаров
// echo "<pre>";
// print_r($arr_article_items);


echo '<link rel="stylesheet" href="css/main_table.css">';
$arr_catalog = get_catalog_wb ();

foreach ($arr_catalog as $items) {
    $arr_skus[] = $items['barcode'];
}

$warehouseId = 34790;// ID склада ООО на ВБ

$link_wb  = "https://suppliers-api.wildberries.ru/api/v3/stocks/".$warehouseId;

$data = array("skus"=> $arr_skus);

//    echo "<pre>";
//    print_r($data);
    
 
$ch = curl_init($link_wb);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Authorization:' . $token_wb,
	'Content-Type:application/json'
));
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
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


foreach ($res['stocks'] as $prods)  {
    foreach ($arr_catalog as &$items) {
        if ($prods['sku'] == $items['barcode']) {
$items['quantity'] = $prods['amount'];
        }

}
}

/*******************************************************************************************************
* *****************************      Достаем фактически заказанные товары
*******************************************************************************************************/


$ch = curl_init('https://suppliers-api.wildberries.ru/api/v3/orders/new');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Authorization:' . $token_wb,
	'Content-Type:application/json'
));
// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);

$res = curl_exec($ch);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
curl_close($ch);

echo     'Результат обмена : '.$http_code. "<br>";

$result = json_decode($res, true);




// формируем массив ключ - артикул ; значение - количество элементов этого артикула

foreach ($result['orders'] as $itemss) {
 
	$arr_name[$itemss['article']][]= $itemss;
$sum = @$sum + $itemss['convertedPrice']/100;

}

// echo "<pre>";
// print_r($arr_catalog);
// die('ddnnnn333nn');


foreach ($arr_name as $key => $temp_items) {
	$arr_article_count[$key] = count($arr_name[$key]);
}

// echo "<pre>";
// print_r($arr_catalog);
// die('dddd');

foreach ($arr_article_count as $key=>$prods)  {
    foreach ($arr_catalog as &$items) {
        // echo "<br>key=$key<br>";
        if ($key == $items['article']) {

            $items['sell_count'] = $prods;
            
        } 
    }

}


// echo "<pre>";
// print_r($arr_catalog);

// $quantity_1c = 29;

echo <<<HTML
<table>
<tr class="prods_table">

    <td>артикул</td>
    <td>Наименование</td>
    <td>БарКод</td>
    <td>Кол-во на WB<br>(Остаток)</td>
    <td>Кол-во продано</td>
    <td>Oстатки из 1С</td>
    
    <td>Рекомендуемый/<br>(Будуший остаток)</td>
    <td>пп</td>
    <td>START</td>
    <td>артикул</td>

</tr>
HTML;

// echo "<pre>";

// $i=0;
// foreach ($arr_catalog as $items22) {
//     print_r($items22);
// //     $article22 = $items['real_article'];
// //     $ppppppp = $arr_catalog[$i]['real_article'];
// //     echo "**$article22**($ppppppp)"; 

// // $i++;

// }
// die('fffffffff');
unset ($items);
foreach ($arr_catalog as $items) {
   
    $article = $items['real_article'];
  


    $name = $items['name'];
    $quantity = $items['quantity'];
    $barCode =  $items['barcode'];
    isset($items['sell_count'])?$sell_count = $items['sell_count']:$sell_count = 0;

    foreach ($arr_article_items as $key1C=>$items1Csklad){
        if (mb_strtoupper($article) == mb_strtoupper($key1C)){
        $quantity_1c = $items1Csklad['wb'];
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
   <form action="update_count_items.php" method="get">

    <td>$article</td>
    <td>$name</td>
    <td>$barCode</td>

    <td class="$alarm_class text14">$quantity</td>
    <td class="text14">$sell_count</td>
    <td class="text14">$quantity_1c</td>
   

    <td><input type="number" name="value_in_wb_bd" value=$value_in_wb_bd></td>

    <td><input checked type="checkbox" name="check_$article" value="1"></td>
    <td><input type="submit" value="START"></td>

    <input hidden type="text"  name = "BarCode" value="$barCode">
    <td class="text14">$article</td>

</form>
</tr>

HTML;
}
echo "</table>";




