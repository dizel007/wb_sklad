<?php

require_once 'libs/PHPExcel-1.8/Classes/PHPExcel.php';
require_once 'libs/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once 'libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';


// require_once "wb_catalog.php";
$uploaddir = "uploads/";
$uploadfile = $uploaddir . basename( $_FILES['file_excel']['name']);

if(move_uploaded_file($_FILES['file_excel']['tmp_name'], $uploadfile))
{
  echo "Файл с остатками товаров, УСПЕШНО ЗАГРУЖЕН";
}
else
{
  die ("DIE ОШИБКА при загрузке файла");
}


if (!copy($uploadfile, "temp_sklad/temp.xlsx")) {
    die ("DIE не удалось скопировать $uploadfile...\n");
}

echo "<br>";

$xls = PHPExcel_IOFactory::load('temp_sklad/temp.xlsx');
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();
$i=12;
$stop =0;


while ($stop <> 1 ) {

    $temp_zero_cell = $sheet->getCellByColumnAndRow(0,$i)->getValue(); // артикул 
    $temp_name = $sheet->getCellByColumnAndRow(2,$i)->getValue(); // название 
    $temp_qty = $sheet->getCellByColumnAndRow(10,$i)->getValue(); // количество

    if (($temp_zero_cell <>'') and ($temp_name <> '')) {
        $real_article = $sheet->getCellByColumnAndRow(0,$i)->getValue(); // артикул 

        // echo "MEW = $real_article, QTY=$temp_qty";
    }
if ($temp_qty=='#NULL!') {
    $temp_qty=0;
}
if ($temp_zero_cell == 'ЛЕРУА' ) {
 $arr_article_items[$real_article]['leroy'] = $temp_qty ;
} elseif ($temp_zero_cell == 'ОЗОН' ){
    $arr_article_items[$real_article]['ozon'] = $temp_qty ;
} elseif ($temp_zero_cell == 'WB' ){
    $arr_article_items[$real_article]['wb'] = $temp_qty ;
} elseif ($temp_zero_cell == 'WB ИП' ){
    $arr_article_items[$real_article]['wbip'] = $temp_qty ;
}






    if ($temp_zero_cell == ''){
        echo "закончили анализ EXCEL файла с остатками товаров<br>";
        break;
    }
    $i++;
}


// echo "<pre>";
// print_r($arr_article_items);

