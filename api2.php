<?php
  $url = 'https://ipinfo.io/json';
  $response = file_get_contents($url);
  echo ($response);
?>