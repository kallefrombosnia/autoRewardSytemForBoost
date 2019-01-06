<?php
/* Configuration file */ 

# Server ip address
$server['ip'] = '94.23.118.27:27023';

# Database connection
$mysql['host'] = 'localhost';
$mysql['username'] = 'root';
$mysql['password'] = '';
$mysql['db'] = 'amxx';
















$db = new mysqli($mysql["host"], $mysql["username"], $mysql["password"], $mysql["db"]);



?> 
