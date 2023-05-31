<?php
  $url = 'http://ip-api.com/json';
  $response = file_get_contents($url);
  echo ($response);
?>