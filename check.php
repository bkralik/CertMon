<?php
require_once('functions.php');

function sendMail($text, $to) {
  $headers   = array();
  $headers[] = "MIME-Version: 1.0";
  $headers[] = "Content-type: text/plain; charset=utf-8";
  $headers[] = "From: ".CERTMON_EMAIL;
  $headers[] = "Reply-To: ".CERTMON_ADMIN;
  $headers[] = "X-Mailer: PHP/".phpversion();
  
  mail($to, "Expirujici certifikaty", $text, implode("\r\n", $headers));
}

$servers = getListFromCSV();

$problems = array();
foreach($servers as $server) {
  $test = getServerInfo($server[0], $server[2]);
  
  $time = time();
  if($test["status"] != 0) {
    
  } elseif($test["cert"]["validTo_time_t"] < $time) {
    $problems[$server[3]][] = "   " . $server[0] . ":" . $server[2] . " - " . $server[1] . " - vyexpiroval";
  } elseif(($test["cert"]["validTo_time_t"] - CERTMON_SEND_BEFORE*24*3600) < $time) {
    $problems[$server[3]][] = "   " . $server[0] . ":" . $server[2] . " - " . $server[1] . " - za ". round(($test["cert"]["validTo_time_t"] - $time) / 3600 / 24 + 1) ." dní vyexpiruje";
  }
}

if(count($problems) > 0) {
  foreach($problems as $admin => $error_servers) {
    $text = "Dobrý den,\r\n";
    $text .=  "omlouvám se, ale vyexpirovaly / brzy vyexpirují některé vámi-spravované certifikáty. Konkrétně to jsou:\r\n\r\n";
    $text .= implode("\r\n", $error_servers);
    $text .=  "\r\n\r\n";
    $text .=  "Děkuji za brzkou nápravu, CertMonBot.";
    sendMail($text, $admin);
  }
}


?>