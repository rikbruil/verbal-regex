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
     * @see find
     */
    public function then($statement = '', $name = null)
    {
        return $this->find($statement, $name);
    }

    /**
     * @param string $statement
     * @param string|null $name
     * @return $this
     */
    public function find($statement = '', $name = null)
    {
        $statement = self::sanitize($statement);
        return $this->capture($statement, $name);
    }

    /**
     * @param string $statement
     * @return string
     */
    private static function sanitize($statement)
    {
        return preg_quote($statement, self::$delimiter);
    }

    /**
     * @param string $statement
     * @param null $name
     * @return $this
     */
    private function capture($statement = '', $name = null)
    {
        $prefix = (empty($name)) ? '' : '?P<' . $name . '>';
        return $this->add('(' . $prefix . $statement . ')');
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function add($statement = '')
    {
        if ($statement) {
            $this->buffer .= $statement;
        }
        return $this;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function anything($name = null)
    {
        return $this->capture('.*', $name);
    }

    /**
     * @param string $statement
     * @param string|null $name
     * @return $this
     */
    public function anythingBut($statement = '', $name = null)
    {
        $statement = self::sanitize($statement);
        return $this->capture(self::charactersNotIn($statement) . '*', $name);
    }

    /**
     * @param string $statement
     * @return string
     */
    public static function charactersNotIn($statement = '')
    {
        return self::charactersIn('^' . $statement);
    }

    /**
     * @param string $statement
     * @return string
     */
    public static function charactersIn($statement = '')
    {
        return '[' . $statement . ']';
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
    public function somethingBut($statement = '', $name = null)
    {
        $statement = self::sanitize($statement);
        return $this->capture(self::charactersNotIn($statement) . '+', $name);
    }

    /**
     * Todo: test this
     * @return $this
     */
    public function word()
    {
        return $this->add('\w+');
    }

    /**
     * @param string $from
     * @param string|null $to
     * @return $this
     */
    public function range($from = '', $to = null)
    {
        $statement = (!$to) ? $from : $from . '-' . $to;
        return $this->add(self::charactersIn($statement));
    }

    /**
     * @param int $number
     * @return $this
     */
    public function times($number)
    {
        return $this->add('{' . $number . '}');
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function maybe($statement = '')
    {
        return $this->find($statement)->add('?');
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function any($statement = '')
    {
        return $this->anyOf($statement);
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function anyOf($statement = '')
    {
        $statement = self::sanitize($statement);
        return $this->add(self::charactersIn($statement));
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function startsWith($statement = '')
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
    public function endsWith($statement = '')
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
     * @return string
     */
    public function compile()
    {
        return self::$delimiter . $this->buffer . self::$delimiter;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->compile();
    }
}