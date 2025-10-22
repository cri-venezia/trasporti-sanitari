#!/usr/bin/env php
<?php

// Questo script viene eseguito da riga di comando per compilare i file SCSS.

// Bootstrap Composer's autoloader
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
	echo "Errore: Eseguire 'composer install' per installare le dipendenze." . PHP_EOL;
	exit(1);
}
require_once $autoloader;

use CRIVenice\Transport\Includes\SCSSCompiler;

echo "Compilazione di form-style.scss..." . PHP_EOL;

try {
	$compiler = new SCSSCompiler();
	$plugin_dir = dirname(__DIR__); // La directory principale del plugin
	$compiler->compile('form-style', $plugin_dir);
	echo "Compilazione completata con successo!" . PHP_EOL;
} catch (\Exception $e) {
	echo "Errore durante la compilazione: " . $e->getMessage() . PHP_EOL;
	exit(1);
}
