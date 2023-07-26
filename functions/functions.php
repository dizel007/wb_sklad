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



/****************************************************************************************************************
************************************* Создаем поставку на сайте WB **************************************
****************************************************************************************************************/
function make_postavka ($token_wb, $name_postavka) {
$data = array('name' => $name_postavka);
 $link_wb = 'https://suppliers-api.wildberries.ru/api/v3/supplies';
 $res = light_query_with_data ($token_wb, $link_wb, $data);

 return $res; // Возвращаем номер поставки
}


/****************************************************************************************************************
************************************* Получаем заказы из одной поставки    **************************************
****************************************************************************************************************/
function get_orders_from_supply($token_wb, $supplyId) {
	$link_wb = 'https://suppliers-api.wildberries.ru/api/v3/supplies/'.$supplyId.'/orders';
	$res =  light_query_without_data($token_wb, $link_wb);
	// echo "<pre>";
    // print_r($res['orders']);
	return $res['orders']; // 
	}


/****************************************************************************************************************
************************************* Получаем стикеры по номерам заказа  **************************************
****************************************************************************************************************/

	function get_stiker_from_supply ($token_wb, $arr_orders, $N_1C_zakaz, $article) {
		$dop_link="?type=png&width=40&height=30";  // QUERY PARAMETERS
		$link_wb  = "https://suppliers-api.wildberries.ru/api/v3/orders/stickers".$dop_link;;
		 // массив с номерами заказа
	 	$data = array(
					"orders"=> $arr_orders
				);
  		// получаем данные со стикерами 
		$res_stikers = light_query_with_data($token_wb, $link_wb, $data); 
		
		// ФОРМИРУЕМ ПДФ файл
		require_once "libs/fpdf/fpdf.php";
		//create pdf object
		$pdf = new FPDF('L','mm', array(80, 106)); // задаем пдф файл размером с пнг файл
		//add new page
		$pdf->AliasNbPages();
		
		$file_num=1; // временный порядковй номер для картинки
		foreach ($res_stikers['stickers'] as $items) {
		$filedata='';
		$pdf->AddPage();
		$file = "EXCEL/stik".$file_num.".png";
		
		$filedata = base64_decode($items['file']);
		
		file_put_contents($file, $filedata, FILE_APPEND); // добавляем данные в файл с накопительным итогом
		$pdf->image($file,0,0,'PNG');
		unlink ($file); // удалям пнг файлы, чтобы не копить их
		
		$file_num++;
				}
		// запись в пдф файл
		$pdf_file = "№".$N_1C_zakaz."_stikers_(".$article.") ".count($res_stikers['stickers'])."шт.pdf";  
		
		if (file_exists("EXCEL/".$pdf_file)) {
			$pdf_file=rand(0,100000)."_".$pdf_file; // если уже есть название такого файла
		}
		$pdf->Output("EXCEL/".$pdf_file, 'F');

		return $pdf_file; // возвращаем название ПДФ файла для формирования  архива;
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