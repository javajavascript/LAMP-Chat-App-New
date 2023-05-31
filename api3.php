<?php
  $url = 'https://geolocation-db.com/json/';
  $response = file_get_contents($url);
  echo ($response);
?>