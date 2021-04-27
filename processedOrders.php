<?php
////$data = json_decode(file_get_contents('php://input'), true);
   $data = $_REQUEST;
   $fp = fopen("processed.txt", "w");
   fwrite($fp, serialize($data));
   fclose($fp);
   
   $data = json_decode(file_get_contents('php://input'), true);
   $fp = fopen("processed1.txt", "w");
   fwrite($fp, serialize($data));
   fclose($fp);