<?php

echo <<<HTML


<form action="get_ostatki_skladov_3.php" method="post" enctype="multipart/form-data">
<label for="market-select">Выбрать МАРКЕТ:</label>

<select required name="market" id="market-select">
    <option value="wb">WB</option>
    <option value="wbip">WB IP</option>
    
</select>
<hr>

<span>Выберите файл</span>
	<input required type="file" name="file_excel" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">		
	
 	
        
<hr>

 <input type="submit" value="ЗАПУСК">	

</form>



HTML;