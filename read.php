<?php

  date_default_timezone_set('Asia/Kolkata');

  $now = date('d_m_Y');
  $jsonData = '';

  if (isset($_GET['date'])) {
    $now = $_GET['date'];
  }

  $fileName = 'log/' . $now . '_error.json';

  if (file_exists($fileName)) {
    $jsonData = file_get_contents($fileName);
    $arrayData = json_decode($jsonData, true);    
  }

  echo $jsonData;

?>