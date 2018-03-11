<?php

unlink('telegram_class.php');
copy('https://api.carabiniere.ovh/telegram_class.txt', 'telegram_class.php');
echo 'Class was created successfully! load it using "require_once(\'telegram_class.php\');"'.PHP_EOL;
die();

