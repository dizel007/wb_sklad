<?php


function  Parce_excel_1c_sklad ($xls) {
// $xls = PHPExcel_IOFactory::load('temp_sklad/temp.xlsx');
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();
$i=14;
$stop =0;


while ($stop <> 1 ) {

    $temp_zero_cell = $sheet->getCellByColumnAndRow(0,$i)->getValue(); // артикул 
    // echo "temp_zero_cell = $temp_zero_cell<br>";
    $temp_name = $sheet->getCellByColumnAndRow(3,$i)->getValue(); // название 
    // echo "temp_name = $temp_name<br>";
    $temp_qty = $sheet->getCellByColumnAndRow(10,$i)->getValue(); // количество
    // echo "temp_qty = $temp_qty<br>";

    if (($temp_zero_cell <>'') and ($temp_name <> '')) {
        $real_article = $sheet->getCellByColumnAndRow(0,$i)->getValue(); // артикул 

        echo "MEW = $real_article, QTY=$temp_qty<br>";
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
        // echo "закончили анализ EXCEL файла с остатками товаров<br>";
        break;
    }
    $i++;
}

$json_array_ozon = json_encode($arr_article_items);

file_put_contents('uploads/array_items.json', $json_array_ozon);
// echo "<pre>";
// print_r($arr_article_items);
return $arr_article_items;
}
