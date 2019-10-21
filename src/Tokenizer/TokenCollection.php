<?php
namespace Xicrow\PhpSqlFormatter\Tokenizer;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;

/**
 * Class TokenCollection
 *
 * @package Xicrow\PhpSqlFormatter\Tokenizer
 */
class TokenCollection implements ArrayAccess, Countable, Iterator
{
	/** @var Token[] */
	private $storage = [];

	/**
	 * Add a token to the collection
	 *
	 * @param Token $token
	 * @return TokenCollection
	 */
	public function add(Token $token): self
	{
		$this->storage[] = $token;

		return $this;
	}

	/**
	 * Filter collection to given types
	 *
	 * @param array $types
	 * @param bool  $typeMatches
	 * @return TokenCollection
	 */
	public function filter(array $types = [], bool $typeMatches = true): self
	{
		$collection = new static();
		foreach ($this->storage as $token) {
			if ($typeMatches && in_array($token->getType(), $types, true)) {
				$collection->add($token);
			}
			if (!$typeMatches && !in_array($token->getType(), $types, true)) {
				$collection->add($token);
			}
		}

		return $collection;
	}

	/**
	 * Get array with token types
	 *
	 * @return array
	 */
	public function getTokenTypes(): array
	{
		$types = [];
		foreach ($this as $token) {
			$types[] = $token->getType();
		}

		return $types;
	}

	/**
	 * Get array with token content
	 *
	 * @return array
	 */
	public function getTokenContents(): array
	{
		$contents = [];
		foreach ($this as $token) {
			$contents[] = $token->getContent();
		}

		return $contents;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->storage);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet($offset): ?Token
	{
		return $this->storage[$offset] ?? null;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value): void
	{
		if (!$value instanceof Token) {
			throw new InvalidArgumentException('Value must be an instance of Token');
		}

		$this->storage[$offset] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset): void
	{
		if ($this->offsetExists($offset)) {
			unset($this->storage[$offset]);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int
	{
		return count($this->storage);
	}
	/**
	 * @inheritDoc
	 */
	public function current(): ?Token
	{
		return current($this->storage);
	}

	/**
	 * @inheritDoc
	 */
	public function next(): void
	{
		next($this->storage);
	}

	/**
	 * @inheritDoc
	 */
	public function key(): int
	{
		return key($this->storage);
	}

	/**
	 * @inheritDoc
	 */
	public function valid(): bool
	{
		return current($this->storage) instanceof Token;
	}

	/**
	 * @inheritDoc
	 */
	public function rewind(): void
	{
		reset($this->storage);
	}
}
