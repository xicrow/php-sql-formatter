<?php
namespace Xicrow\PhpSqlFormatter\Tokenizer;

/**
 * Class Tokenizer
 *
 * @package Xicrow\PhpSqlFormatter\Tokenizer
 */
class Tokenizer
{
	/** @var TokenRule[] */
	private $rules = [];

	/**
	 * @param TokenRule $rule
	 * @return $this
	 */
	public function addRule(TokenRule $rule): self
	{
		$this->rules[] = $rule;

		return $this;
	}

	/**
	 * @param string $string
	 * @return TokenCollection
	 */
	public function tokenize(string $string): TokenCollection
	{
		$collection    = new TokenCollection();
		$token         = null;
		$currentLength = strlen($string);
		while ($currentLength) {
			$token = self::getNextToken($string);
			if ($token === null) {
				return $collection;
			}

			$collection->add($token);

			$tokenContentLength = strlen($token->getContent());
			$string             = substr($string, $tokenContentLength);
			$currentLength      -= $tokenContentLength;
		}

		return $collection;
	}

	/**
	 * @param string $string
	 * @return Token|null
	 */
	private function getNextToken(string $string): ?Token
	{
		foreach ($this->rules as $rule) {
			$match = $rule->match($string);
			if ($match !== null) {
				return new Token($rule->getType(), $match);
			}
		}

		return null;
	}
}
