<?php
require_once('../vendor/autoload.php');

use Xicrow\PhpSqlFormatter\Factory;

$sqls = [];
if (file_exists('demo.sql')) {
	$sqls = file_get_contents('demo.sql');
	$sqls = trim($sqls, ';');
	$sqls = explode(';', $sqls);
	$sqls = array_map('trim', $sqls);
	$sqls = array_filter($sqls);
	$sqls = array_map(function ($sql) {
		return $sql . ';';
	}, $sqls);
}

$sqlCount = 0;
foreach ($sqls as $sql) {
	// Skip if starting with...
//	if (preg_match('/^(SELECT)/i', $sql) === 1) {
//		continue;
//	}
	// Skip if NOT starting with...
//	if (preg_match('/^(INSERT|UPDATE)/i', $sql) !== 1) {
//		continue;
//	}

	if ($sqlCount > 0) {
		if (php_sapi_name() == 'cli') {
			echo "\n" . str_repeat('=', 100) . "\n";
		} else {
			echo '<br /><hr /><br />';
		}
	}

	try {
		echo Factory::getAdapter($sql)->render();
		echo Factory::getAdapter($sql)->format()->highlight()->render();
	} catch (Exception $exception) {
		echo $exception->getMessage();
	}

	$sqlCount++;
}
