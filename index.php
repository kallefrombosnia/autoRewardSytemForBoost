<?php
// Include all needed libs and config file
include_once('config.php');
include_once('./src/functions.php');

init($server['ip']);
sleep(2);
main($server['ip'],$db);

?> 