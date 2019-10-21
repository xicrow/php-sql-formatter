<?php
namespace Xicrow\PhpSqlFormatter\Tokenizer;

/**
 * Class TokenRule
 *
 * @package Xicrow\PhpSqlFormatter\Tokenizer
 */
class TokenRule
{
	/** @var string */
	private $type = '';
	/** @var string */
	private $pattern = '';

	/**
	 * TokenRule constructor.
	 *
	 * @param string $type
	 * @param string $pattern
	 */
	public function __construct(string $type, string $pattern)
	{
		$this->type    = $type;
		$this->pattern = $pattern;
	}

	/**
	 * Match rule against a string and return match
	 *
	 * @param string $string
	 * @return string|null
	 */
	public function match(string $string): ?string
	{
		if (preg_match($this->getPattern(), $string, $matches)) {
			return $matches[0];
		}

		return null;
	}

	/**
	 * Get rule type
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Get rule pattern
	 *
	 * @return string
	 */
	public function getPattern(): string
	{
		return $this->pattern;
	}

	/**
	 * Set rule type
	 *
	 * @param string $type
	 * @return $this
	 */
	public function setType(string $type): self
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Set rule pattern
	 *
	 * @param string $pattern
	 * @return $this
	 */
	public function setPattern(string $pattern): self
	{
		$this->pattern = $pattern;

		return $this;
	}
}
