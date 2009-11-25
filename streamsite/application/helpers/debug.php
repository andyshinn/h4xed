<?php
   function print_array($var,$title=''){
    echo '<pre style="border: 1px solid black; padding: 5px;">'
        . ((!empty($title)) ? $title.' :: ' : '')
        . print_r($var, true)
        . '</pre>';
} 
?>