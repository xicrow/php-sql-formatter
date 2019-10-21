<?php
namespace Xicrow\PhpSqlFormatter\Adapter;

/**
 * Interface AdapterInterface
 *
 * @package Xicrow\PhpSqlFormatter\Adapter
 */
interface AdapterInterface
{
	/**
	 * AdapterInterface constructor.
	 *
	 * @param string $sql
	 */
	public function __construct(string $sql);

	/**
	 * Compress SQL as small as possible
	 *
	 * @return AdapterInterface
	 */
	public function compress(): AdapterInterface;

	/**
	 * Pretty format SQL
	 *
	 * @return AdapterInterface
	 */
	public function format(): AdapterInterface;

	/**
	 * Highlight SQL keywords, functions, etc.
	 *
	 * @param array $arrTypes
	 * @return AdapterInterface
	 */
	public function highlight(array $arrTypes = []): AdapterInterface;

	/**
	 * Strip comments from SQL
	 *
	 * @return AdapterInterface
	 */
	public function stripComments(): AdapterInterface;

	/**
	 * Obfusticate certain elements in the SQL, to keep your data safe
	 * Default types should be: strings, numbers and identifiers
	 * Even though it is possible, please dont use keyword/function types or the like, it will mess up any further formatting
	 *
	 * @param array $arrTypes
	 * @return AdapterInterface
	 */
	public function obfusticate(array $arrTypes = []): AdapterInterface;

	/**
	 * Render parsed SQL
	 *
	 * @return string
	 */
	public function render(): string;
}
