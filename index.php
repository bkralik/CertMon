<?php
require_once('functions.php');

$start = microtime(true);

$servers = getListFromCSV();

$tests = array();
foreach($servers as $server) {
  $test = getServerInfo($server[0], $server[2]);
  
  $time = time();
  if($test["status"] != 0) {
    $cellColor = "active";
  } elseif($test["cert"]["validFrom_time_t"] > $time) {
    $cellColor = "warning";
  } elseif($test["cert"]["validTo_time_t"] < $time) {
    $cellColor = "danger";
  } elseif(($test["cert"]["validTo_time_t"] - 30*24*3600) < $time) {
    $cellColor = "warning";
  } else {
    $cellColor = "success";
  }

  $tests[] = array("server" => $server, "test" => $test, "cellColor" => $cellColor);
}

$end = microtime(true);
$elapsed = round(($end - $start)*10)/10;

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Certificate monitor</title>
    
    <link rel="icon" href="favicon.ico">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
  </head>
  <body>
    <div class="container">
      <h1>Certificate monitor</h1>
      
      <table class="table">
        <tr>
          <th>Server</th>
          <th>Služba (port)</th>
          <th>Jméno certifikátu</th>
          <th>Hash</th>
          <th>Platnost od</th>
          <th>Platnost do</th>
        </tr>
        <?php
        foreach($tests as $test) {
          ?>
            <tr class="<?php echo($test["cellColor"]); ?>">
              <td><?php echo($test["server"][0]); ?></td>
              <td><?php echo($test["server"][1]); ?> (<?php echo($test["server"][2]); ?>)</td>
              <?php
              if($test["test"]["status"] == 0) {
              ?>
                <td><?php echo($test["test"]["cert"]["name"]); ?></td>
                <td><?php echo($test["test"]["cert"]["hash"]); ?></td>
                <td><?php echo(date("j. n. Y", $test["test"]["cert"]["validFrom_time_t"])); ?></td>
                <td><?php echo(date("j. n. Y", $test["test"]["cert"]["validTo_time_t"])); ?></td>
              <?php } else { ?>
                <td colspan="4">Chyba - <?php echo(getErrorText($test["test"]["status"])); ?>.</td>
              <?php } ?>
              
            </tr>
          <?php
        }
        ?>
      </table>
      
      <hr>
      Vygenerováno v <?php echo(date("G:i j. n. Y")); ?> za <?php echo($elapsed); ?> sekund. Napsal <a href="mailto:bkralik@hkfree.org">bkralik</a> v roce 2016.
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
  </body>
</html>