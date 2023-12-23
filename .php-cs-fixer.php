<?php

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__ . '/src')
;

$config = new PhpCsFixer\Config();
return $config
	->setRules([
		'@PSR-12' => true,
		'array_syntax' => ['syntax' => 'short'],
		'yoda_style' => false,
	])
	->setFinder($finder)
	;