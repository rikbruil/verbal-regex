<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 rikbruil
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Rb\VerbalRegex;

/**
 * Class Statement
 * @package Rb\VerbalRegex
 */
class Statement
{
    /**
     * @var string
     */
    private static $delimiter;

    /**
     * @var string
     */
    private $buffer;

    /**
     * @param string $delimiter
     */
    public function __construct($delimiter = '/')
    {
        self::$delimiter = $delimiter;
        $this->buffer = '';
    }

    /**
     * Alternative syntax for the Statement::find() method
     * @see find
     */
    public function then($statement, $name = null)
    {
        return $this->find($statement, $name);
    }

    /**
     * Find and capture the given literal string
     * @param string $statement
     * @param string|null $name
     * @return $this
     */
    public function find($statement, $name = null)
    {
        $statement = self::sanitize($statement);
        return $this->capture($statement, $name);
    }

    /**
     * Search a literal string without capturing.
     * @param string $statement
     * @return $this
     */
    public function search($statement)
    {
        $statement = self::sanitize($statement);
        return $this->add($statement);
    }

    /**
     * Capture anything (with optional name)
     * @param string|null $name
     * @return $this
     */
    public function anything($name = null)
    {
        return $this->capture('.*', $name);
    }

    /**
     * Capture anything but given characters (with optional name)
     * @param string $statement
     * @param string|null $name
     * @return $this
     */
    public function anythingBut($statement, $name = null)
    {
        $statement = self::sanitize($statement);
        return $this->capture(self::charactersNotIn($statement) . '*', $name);
    }

    /**
     * Todo: test this
     * @param string|null $name
     * @return $this
     */
    public function something($name = null)
    {
        return $this->capture('.+', $name);
    }

    /**
     * Todo: test this
     * @param string $statement
     * @param string|null $name
     * @return $this
     */
    public function somethingBut($statement, $name = null)
    {
        $statement = self::sanitize($statement);
        return $this->capture(self::charactersNotIn($statement) . '+', $name);
    }

    /**
     * Capture any/given amount of word characters
     * @param string|null $name Optionally specify name for the match
     * @param int|null $times Optionally specify amount of characters to match
     * @return $this
     */
    public function words($name = null, $times = null)
    {
        $times = ($times) ? '{' . (int) $times. '}' : '+';
        return $this->capture(self::charactersIn('\w') . $times, $name);
    }

    /**
     * Capture any/given amount of word decimal
     * @param string|null $name Optionally specify name for the match
     * @param int|null $times Optionally specify amount of characters to match
     * @return $this
     */
    public function decimals($name = null, $times = null)
    {
        $times = ($times) ? '{' . (int) $times. '}' : '+';
        return $this->capture(self::charactersIn('\d') . $times, $name);
    }

    /**
     * @param string $from
     * @param string|null $to
     * @return $this
     */
    public function range($from, $to = null)
    {
        $statement = (!$to) ? $from : $from . '-' . $to;
        return $this->add(self::charactersIn($statement));
    }

    /**
     * Set the number of times the preceding statement should match
     * @param int $number
     * @return $this
     */
    public function times($number)
    {
        return $this->add('{' . $number . '}');
    }

    /**
     * Set the minimum and maximum amount of times the
     * preceding statement should match
     * @param int $from
     * @param int $to
     * @return $this
     */
    public function between($from, $to)
    {
        $from = min((int) $from, (int) $to);
        $to = max((int) $from, (int) $to);
        return $this->times($from . ',' . $to);
    }

    /**
     * Try to match the given statement (with optional name)
     * @param string $statement
     * @param string|null $name
     * @return $this
     */
    public function maybe($statement, $name = null)
    {
        return $this->find($statement, $name)->add('?');
    }

    /**
     * Alternative syntax for Statement::anyOf()
     * @see anyOf
     */
    public function any($statement)
    {
        return $this->anyOf($statement);
    }

    /**
     * Find but don't capture any of the given characters
     * @param string $statement
     * @return $this
     */
    public function anyOf($statement)
    {
        $statement = self::sanitize($statement);
        return $this->add(self::charactersIn($statement));
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function startsWith($statement)
    {
        return $this->startOfLine()->add($statement);
    }

    /**
     * @return $this
     */
    public function startOfLine()
    {
        return $this->add('^');
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function endsWith($statement)
    {
        return $this->add($statement)->endOfLine();
    }

    /**
     * @return $this
     */
    public function endOfLine()
    {
        return $this->add('$');
    }

    /**
     * Test the current statement against the given subject and return the matches found
     * @param string $subject
     * @param null &$result The result of preg_match (0 = no match, 1 = match, false = error)
     * @return array Array containing the matches, or empty array
     */
    public function match($subject, &$result = null)
    {
        $matches = array();
        $result = preg_match($this->compile(), $subject, $matches);
        return $matches;
    }

    /**
     * Return the RegEx statement as string
     * @return string
     */
    protected function compile()
    {
        return self::$delimiter . $this->buffer . self::$delimiter;
    }

    /**
     * @return string
     */
    public final function __toString()
    {
        return $this->compile();
    }

    /**
     * Escape characters with special meaning in RegEx
     * @param string $statement
     * @return string
     */
    protected final static function sanitize($statement)
    {
        return preg_quote($statement, self::$delimiter);
    }

    /**
     * Create a character class which should not include given characters
     * @param string $statement
     * @return string
     */
    protected final static function charactersNotIn($statement)
    {
        return self::charactersIn('^' . $statement);
    }

    /**
     * Create a character class which should include given characters
     * @param string $statement
     * @return string
     */
    protected final static function charactersIn($statement)
    {
        return '[' . $statement . ']';
    }

    /**
     * Add a (optionally named) capture group.
     * This method does not do any sanitizing of input.
     * Internal use only
     * @param string $statement
     * @param null $name
     * @return $this
     */
    protected final function capture($statement, $name = null)
    {
        $prefix = (empty($name)) ? '' : '?P<' . $name . '>';
        return $this->add('(' . $prefix . $statement . ')');
    }

    /**
     * Add the given statement to the buffer
     * Internal use only
     * @param string $statement
     * @return $this
     */
    protected final function add($statement)
    {
        if ($statement) {
            $this->buffer .= $statement;
        }
        return $this;
    }
}