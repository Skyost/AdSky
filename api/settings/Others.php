<?php

$settings['APP_NAME'] = 'AdSky';
$settings['APP_WEBSITE'] = 'https://dev.bukkit.org/projects/adsky';
$settings['APP_CURRENCY'] = 'USD';
$settings['APP_DEBUG'] = true; // TODO Remove this on release

$settings['DB_PREFIX'] = 'adsky_';
$settings['DB_THROTTLING'] = !$settings['APP_DEBUG'];

$settings['PAGINATOR_MAX'] = 10;

$settings['EMAIL_SENDER'] = 'adsky@skyost.eu';