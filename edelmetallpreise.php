<?php
$ch = curl_init("http://www.allgemeine-gold.de/ek/ek.php/iphone");
$ch = curl_init("http://www.flussreiter.de");
/*curl_setopt( $ch,CURLOPT_USERAGEND,"Internet Explorer");*/

ob_start();
curl_exec( $ch );
curl_close( $ch );
$str = ob_get_contents();
ob_end_clean();

//String Suchen

preg_match_all("#Verkaufspreis unverarbeitet:(.+?)</b>#", $str, $events);    
$i=0;
foreach($events[0] as $event => $key){
    echo '<div class="headline">'.$key.'</div>';
    $i++;
}  
?>
