<?php
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
     * @see find
     */
    public function then($statement = '', $name = null)
    {
        return $this->find($statement, $name);
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
    public function anyOf($statement = '')
    {
        $statement = self::sanitize($statement);
        return $this->add(self::charactersIn($statement));
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
    public function startsWith($statement = '')
    {
        return $this->startOfLine()->add($statement);
    }

    /**
     * @return $this
     */
    public function endOfLine()
    {
        return $this->add('$');
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
     * @param string $statement
     * @return $this
     */
    public function add($statement = '')
    {
        if ($statement)
        {
            $this->buffer .= $statement;
        }
        return $this;
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
     * @return string
     */
    public static function charactersIn($statement = '')
    {
        return '[' . $statement . ']';
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
    private static function sanitize($statement)
    {
        return preg_quote($statement, self::$delimiter);
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