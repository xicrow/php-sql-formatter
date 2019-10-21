<?php
namespace Xicrow\PhpSqlFormatter\Tokenizer;

/**
 * Class Token
 *
 * @package Xicrow\PhpSqlFormatter\Tokenizer
 */
class Token
{
	/** @var string */
	private $type = '';
	/** @var string */
	private $content = '';

	/**
	 * Token constructor.
	 *
	 * @param string $type
	 * @param string $content
	 */
	public function __construct(string $type = '', string $content = '')
	{
		$this->type    = $type;
		$this->content = $content;
	}

	/**
	 * Check if token type equals given value
	 *
	 * @param $value
	 * @return bool
	 */
	public function isType($value): bool
	{
		return $this->getType() === $value;
	}

	/**
	 * Check if token type not equals given value
	 *
	 * @param $value
	 * @return bool
	 */
	public function isTypeNot($value): bool
	{
		return $this->getType() !== $value;
	}

	/**
	 * Check if token type is of given values
	 *
	 * @param array $values
	 * @return bool
	 */
	public function isTypeIn(array $values): bool
	{
		return in_array($this->getType(), $values, true);
	}

	/**
	 * Check if token type is not of given values
	 *
	 * @param array $values
	 * @return bool
	 */
	public function isTypeNotIn(array $values): bool
	{
		return !in_array($this->getType(), $values, true);
	}

	/**
	 * Check if token content equals given value
	 *
	 * @param $value
	 * @return bool
	 */
	public function isContent($value): bool
	{
		return $this->getContent() === $value;
	}

	/**
	 * Check if token content not equals given value
	 *
	 * @param $value
	 * @return bool
	 */
	public function isContentNot($value): bool
	{
		return $this->getContent() !== $value;
	}

	/**
	 * Check if token content is of given values
	 *
	 * @param array $values
	 * @return bool
	 */
	public function isContentIn(array $values): bool
	{
		return in_array($this->getContent(), $values, true);
	}

	/**
	 * Check if token content is not of given values
	 *
	 * @param array $values
	 * @return bool
	 */
	public function isContentNotIn(array $values): bool
	{
		return !in_array($this->getContent(), $values, true);
	}

	/**
	 * Get token type
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Get token content
	 *
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}

	/**
	 * Set token type
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
	 * Set token content
	 *
	 * @param string $content
	 * @return $this
	 */
	public function setContent(string $content): self
	{
		$this->content = $content;

		return $this;
	}
}
