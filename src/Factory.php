<?php
namespace Xicrow\PhpSqlFormatter;

use Xicrow\PhpSqlFormatter\Adapter\AdapterInterface;
use Xicrow\PhpSqlFormatter\Exception\AdapterNotFoundException;
use Xicrow\PhpSqlFormatter\Exception\UnknownDialectException;

/**
 * Class Factory
 *
 * @package Xicrow\PhpSqlFormatter
 */
class Factory
{
	/**
	 * @var array
	 */
	private static $dialectMap = [
		'mysql' => 'Xicrow\PhpSqlFormatter\Adapter\MySQL',
	];

	/**
	 * @param string $dialect
	 * @param string $class
	 */
	public static function appendDialect(string $dialect, string $class): void
	{
		static::$dialectMap = array_merge(static::$dialectMap, [$dialect => $class]);
	}

	/**
	 * @param string $dialect
	 * @param string $class
	 */
	public static function prependDialect(string $dialect, string $class): void
	{
		static::$dialectMap = array_merge([$dialect => $class], static::$dialectMap);
	}

	/**
	 * @param string $sql
	 * @param string $dialect
	 * @return AdapterInterface
	 * @throws AdapterNotFoundException
	 * @throws UnknownDialectException
	 */
	public static function getAdapter(string $sql, string $dialect = ''): AdapterInterface
	{
		if($dialect === '') {
			$dialect = key(static::$dialectMap);
		}

		if (!array_key_exists($dialect, static::$dialectMap)) {
			throw new UnknownDialectException('Dialect "' . $dialect . '" not supported');
		}

		if (!class_exists(static::$dialectMap[$dialect])) {
			throw new AdapterNotFoundException('Formatter has no support for "' . $dialect . '" dialect');
		}

		return new static::$dialectMap[$dialect]($sql);
	}
}
