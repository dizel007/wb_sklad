<?php

function razbor_post_massive($arr_post){

foreach ($arr_post as $key=>$value) {
 
    if (mb_strpos($key, 'BarCode_') > 0){
        $new_key = str_replace('_BarCode_', '', $key);
        $arr_BarCode[$new_key] = $value;
    }

    if (mb_strpos($key, 'value_') > 0){
        $new_key = str_replace('_value_', '', $key);
        $arr_value[$new_key] = $value;
    }
    
    if (mb_strpos($key, 'check_')){
        $new_key = str_replace('_check_', '', $key);
        $arr_check[$new_key] = $value;
// формируем массив для обновления (Где стояла галочка в строке)
        $item_quantity[] = array("sku"    => $arr_BarCode[$new_key],
                           "amount" => (int)$arr_value[$new_key]); // требуется преобразование типа на интегер

    }
}

return $item_quantity;
}