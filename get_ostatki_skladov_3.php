<?php
require_once "functions/topen.php";
require_once "functions/functions.php";
require_once "wb_catalog.php"; // массиво с каталогов наших товаров

require_once "parce_excel_sklad.php"; // массиво с каталогов наших товаров

$market = $_POST['market'];
//  присваиваем номер склада
if ($market == 'wb'){
    $token = $token_wb;
    $warehouseId = 34790;// ID склада ООО на ВБ
    $arr_catalog = get_catalog_wb ();
} elseif ($market == 'wbip'){
    $token = $token_wbip;
    $warehouseId = 221597;// ID склада ИП на ВБ 
    $arr_catalog = get_catalog_wbip ();
} else {
    die('DIE не смогли выбрать склад ВБ');
}



// die('DIE START GET OSTATKI');

echo '<link rel="stylesheet" href="css/main_table.css">';


foreach ($arr_catalog as $items) {
    $arr_skus[] = $items['barcode'];
}


$link_wb  = "https://suppliers-api.wildberries.ru/api/v3/stocks/".$warehouseId;
$data = array("skus"=> $arr_skus);

//    echo "<pre>";
//    print_r($data);
    
 
$ch = curl_init($link_wb);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Authorization:' . $token,
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
//******************************************************************************************* */

foreach ($res['stocks'] as $prods)  {
    foreach ($arr_catalog as &$items) {
        if ($prods['sku'] == $items['barcode']) {
$items['quantity'] = $prods['amount'];
        }

}
}

/*******************************************************************************************************
* *****************************      Достаем фактически заказанные товары  *****************************
*******************************************************************************************************/

$ch = curl_init('https://suppliers-api.wildberries.ru/api/v3/orders/new');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Authorization:' . $token,
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
// $sum = @$sum + $itemss['convertedPrice']/100;
}

foreach ($arr_name as $key => $temp_items) {
	$arr_article_count[$key] = count($arr_name[$key]);
}

foreach ($arr_article_count as $key=>$prods)  {
    foreach ($arr_catalog as &$items) {
        // echo "<br>key=$key<br>";
        if ($key == $items['article']) {
            $items['sell_count'] = $prods;
        } 
    }

}


// $quantity_1c = 29;

echo <<<HTML
<form action="update_count_items_3.php" method="post">
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
    <td>артикул</td>

</tr>
HTML;

unset ($items); // чистим эту переменную

foreach ($arr_catalog as $items) {
   
    $article = $items['real_article'];
    $name = $items['name'];
    $quantity = $items['quantity'];
    $barCode =  $items['barcode'];
    isset($items['sell_count'])?$sell_count = $items['sell_count']:$sell_count = 0;

    foreach ($arr_article_items as $key1C => $items1Csklad){
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
    <td class="$alarm_class text14">$quantity</td>
    <td class="text14">$sell_count</td>
    <td class="text14">$quantity_1c</td>
    <td><input type="number" name="_value_$article" value=$value_in_wb_bd></td>
    <td><input checked type="checkbox" name="_check_$article" value="1"></td>
    <input hidden type="text"  name = "_BarCode_$article" value="$barCode">
    <td class="text14">$article</td>

</tr>

HTML;
}
echo <<<HTML
</table>
<input hidden type="text" name ="market" value="$market">
<input hidden type="text" name ="warehouseId" value="$warehouseId">

<input type="submit" value="START">
</form>


HTML;



