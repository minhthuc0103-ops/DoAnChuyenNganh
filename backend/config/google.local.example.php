<?php
// Sao chép file này thành  google.local.php  rồi điền giá trị thật. google.local.php KHÔNG được commit lên Git.
// Lấy Client ID / Secret ở https://console.cloud.google.com → APIs & Services → Credentials → OAuth client ID (Web application).
// "Authorized redirect URIs" trong Google phải GIỐNG HỆT redirect_uri dưới đây.
return [
    'client_id'     => 'xxxxxxxx.apps.googleusercontent.com',
    'client_secret' => 'GOCSPX-xxxxxxxx',
    'redirect_uri'  => 'http://localhost:8000/index.php?controller=auth&action=googleCallback',
];
