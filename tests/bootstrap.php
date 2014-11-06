<?php

error_reporting(E_ALL & ~E_USER_DEPRECATED);

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->addPsr4('Openbuildings\\Postmark\\Test\\', __DIR__.'/src');
