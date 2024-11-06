<?php

$dir = new RecursiveDirectoryIterator(__DIR__.'/api');
$iterator = new RecursiveIteratorIterator($dir);
$regex = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

foreach ($regex as $files) {
    require_once $files[0];
}
