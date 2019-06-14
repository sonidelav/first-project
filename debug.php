<?php
$grid = $_REQUEST['grid'];
$length = count($grid);
$files = array();
//var_dump($grid);

for($i=0;$i<$length;$i++){
    if(!empty($grid[$i])) {
        $filename = $grid[$i];
        $split = explode('/',$filename);
        $file = $split[count($split)-1];
        $files[$file]=$i+1;
    }
}

var_dump($files);
?>
