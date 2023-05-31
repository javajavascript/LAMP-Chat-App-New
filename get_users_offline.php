<?php

  // connect to databases
  include('config.php');

  // grab all messages from db
  // microtime() does ms but it had a data type error
  // *1000 to convert to ms then -2000 to delay by 2 seconds
  $now = time()*1000-2000;
  error_log(print_r($now, TRUE)); 

  $sql = "SELECT * FROM pings WHERE time < '$now'"; //newer the last 30 seconds
  $results = $db->query($sql);
  
  error_log(print_r($sql, TRUE)); 
  error_log(print_r($results, TRUE)); 

  $return_array = array();

  while ($row = $results->fetchArray()) {
    $result_array = array();
    $result_array['id'] = $row['id'];
    $result_array['name'] = $row['name'];
    $result_array['time'] = $row['time'];
    array_push($return_array, $result_array);
  }

  print json_encode($return_array);

  // package up and send to client

  // https://stackoverflow.com/questions/9866139/database-locked-while-trying-to-access-from-php-script
  $db->close();
  unset($db);

  exit();
 ?>