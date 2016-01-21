<?php

require_once('settings.php');

date_default_timezone_set("Europe/Prague");

define("ERROR_TIMEOUT", 1);
define("ERROR_CONN_REFUSED", 2);
define("ERROR_NO_CERT", 3);
define("ERROR_PARSE_FAILED", 4);

function getErrorText($e) {
  $e_text = "neznámá";
  
  if($e == ERROR_TIMEOUT) {
    $e_text = "server timeoutoval";
  } elseif($e == ERROR_CONN_REFUSED) {
    $e_text = "připojení selhalo";
  } elseif($e == ERROR_NO_CERT) {
    $e_text = "certifkát není nedostupný";
  } elseif($e == ERROR_PARSE_FAILED) {
    $e_text = "certifikát není čitelný";
  }
  
  return($e_text);
}

function getListFromCSV() {
  $fileName = "list.csv";
  $fileText = file_get_contents($fileName);
  
  $out = array();
  foreach(explode("\n", $fileText) as $line) {
    $lineExploded = explode(';', $line);
    
    // completness check
    if(count($lineExploded) != 4) {
      continue;
    }
    
    $out[] = $lineExploded;
  }
  
  return($out);
}

function getServerInfo($ip, $port) {

  $iport = escapeshellarg($ip . ':' . $port);
  $shcmd = 'echo -n | timeout 2 openssl s_client -connect '.$iport;
        
  $dspecs = array(
    1 => array('pipe', 'w'),
    2 => array('pipe', 'w')
  );
          
  $proc = proc_open($shcmd, $dspecs, $pipes);
  
  $stdout = trim(stream_get_contents($pipes[1]));
  fclose($pipes[1]);
  $stderr = trim(stream_get_contents($pipes[2]));
  fclose($pipes[2]);
  $return_value = proc_close($proc);
  
  if($return_value == 124) {
    $out["status"] = ERROR_TIMEOUT;
    return($out);
  }
  
  if(preg_match('/Connection timed out/', $stderr)) {
    $out["status"] = ERROR_TIMEOUT;
    return($out);
  }

  if(preg_match('/Connection refused/', $stderr)) {
    $out["status"] = ERROR_CONN_REFUSED;
    return($out);
  }
  
  if(!preg_match('/[-]+BEGIN CERTIFICATE-(.*?)-END CERTIFICATE[-]+/s', $stdout, $matches)) {
    $out["status"] = ERROR_NO_CERT;
    return($out);
  }
  
  $cert_text = $matches[0];
  $cert_parsed = openssl_x509_parse($cert_text);
  
  if($cert_parsed == false) {
    $out["status"] = ERROR_PARSE_FAILED;
    return($out);
  }
  
  $out["status"] = 0;
  $out["cert"] = $cert_parsed;
  return($out);       
}
