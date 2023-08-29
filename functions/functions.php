<?php

/****************************************************************************************************************
****************************  Простой запрос на ВБ без данных **************************************
****************************************************************************************************************/
function light_query_without_data($token_wb, $link_wb){
	$ch = curl_init($link_wb);
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
	
	echo     'Результат обмена (without Data): '.$http_code. "<br>";
	
	$res = json_decode($res, true);
	
	return $res;
	}

/****************************************************************************************************************
**************************** Простой запрос на ВБ  с данными **************************************
****************************************************************************************************************/

function light_query_with_data($token_wb, $link_wb, $data){
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
	
	echo     'Результат обмена(with Data): '.$http_code. "<br>";
	
	$res = json_decode($res, true);
	var_dump($res);
	return $res;

}

/****************************************************************************************************************
****************************  ОТправка PATCH на ВБ  с данными **************************************
****************************************************************************************************************/

function patch_query_with_data($token_wb, $link_wb, $data) {
$ch = curl_init($link_wb);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Authorization:' . $token_wb,
	'Content-Type:application/json'
));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);

$res = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
curl_close($ch);

echo     'Результат обмена PATCH: '.$http_code. "<br>";
$res = json_decode($res, true);

return $res;
}

/****************************************************************************************************************
**************************** Получаем все новые заказы **************************************
****************************************************************************************************************/

function get_all_new_zakaz ($token_wb) {
	$link_wb = 'https://suppliers-api.wildberries.ru/api/v3/orders/new';
	$res = light_query_without_data($token_wb, $link_wb);
	return $res;
}




function make_right_articl($article) {
	// КАНТРИ Макси 
		if ($article == '8240282402-ч' ) {
			$new_article = '82402-ч';
		} else if ($article == '8240282402-к' ) {
			$new_article = '82402-к';
		} else if ($article == '8240282402-з' ) {
			$new_article = '82402-з';
	// КАНТРИ Средний 
		} else if ($article == '8240182401-ч' ) {
			$new_article = '82401-ч';
		} else if ($article == '8240182401-з' ) {
			$new_article = '82401-з';
		} else if ($article == '8240182401-к' ) {
			$new_article = '82401-к';
	// КАНТРИ Мини 
		} else if ($article == '8240082400-к' ) {
			$new_article = '82400-к';
		} else if ($article == '8240082400-з' ) {
			$new_article = '82400-з';
		} else if ($article == '8240082400-ч' ) {
			$new_article = '82400-ч';
		} else if ($article == '82552-82552-к' ) {
				$new_article = '82400-к';
		


	// Приствольные круги     
		} else if ($article == '7262-КП(Л)' ) {
			$new_article = '7262-КП';
		} else if ($article == '7262-КП(У)' ) {
			$new_article = '7262-КП';
	
	// Якоря 
		} else if ($article == '8910-8910-30' ) {
			$new_article = '8910-30';
		} else if ($article == '1840-301840-30' ) {
			$new_article = '1840-30';
		} else if ($article == '1940_1940-10' ) {
			$new_article = '1940-10';
	// Метровые борды
		} else if ($article == '7245-К7245-К-16' ) {
			$new_article = '7245-К-16';
		} 
		else if ($article == '7260-К-7260-К-12' ) {
			$new_article = '7260-К-12';
		} 
		else if ($article == '7260-К7260-К-12' ) {
			$new_article = '7260-К-12';


		} else if ($article == '7280-К7280-К-80' ) {
			$new_article = '7280-К-8';
		} else if ($article == '7280-К-7280-К-8' ) {
			$new_article = '7280-К-8';
		} 
	
	// Вся неучтенка    
		
		else {
			$new_article = $article;
		}
	
		return $new_article;
	}


	
/* * ******************************************************************************************************
Выводим список заказов ОЗОН на определенную дату 
РАБОЧАЯ ВЕРСИЯ 
*** ожидает упаковки ****
*************************************************************************************************************** */
function get_all_waiting_posts_for_need_date($token, $client_id, $date_query_ozon, $send_status, $dop_days_query){
    // awaiting_packaging - заказы ожидают сборку
    // awaiting_deliver   - заказы ожидают отгрузку 



$temp_dop_day = "+".$dop_days_query.' day';
$date_query_ozon_end = date('Y-m-d', strtotime($temp_dop_day, strtotime($date_query_ozon)));


$send_data=  array(
    "dir" => "ASC",
    "filter" => array(
    "cutoff_from" => $date_query_ozon."T00:00:00Z",
    "cutoff_to" =>   $date_query_ozon_end."T23:59:59Z",
    "delivery_method_id" => [ ],
    "provider_id" => [ ],
    "status" => $send_status,
    "warehouse_id" => [ ]
    ),
    "limit" => 1000,
    "offset" => 0,
    "with" => array(
    "analytics_data"  => true,
    "barcodes"  => true,
    "financial_data" => true,
    "translit" => true
    )
    );

 $send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;  


$ozon_dop_url = "v3/posting/fbs/unfulfilled/list";


// запустили запрос на озона
$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url );
return $res;
}

/* **************************************************************************************************************
*********  Функция обновляния данных Она ОЗОН
************************************************************************************************************** */

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

//    echo     'Результат обмена : '.$http_code. "<br>";
   
    return($res);	

}