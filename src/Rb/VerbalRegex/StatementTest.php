<?php
/**
 * Created by PhpStorm.
 * User: rik
 * Date: 12/11/13
 * Time: 23:38
 */

namespace Rb\VerbalRegex;

use PHPUnit_Framework_TestCase;

class StatementTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Statement
     */
    private $statement;

    public function setUp()
    {
        $this->statement = new Statement();
    }

    public function testSimple()
    {
        $statement = $this->statement;
        $statement->startsWith()
                ->add('test')
                ->endsWith();

        $expected = '/^test$/';

        $this->assertEquals($expected, $statement . '');
        $this->assertEquals(1, $statement->match('test'));
    }

    public function testUrl()
    {
        $statement = $this->statement;
        $statement->find('http')
            ->maybe('s')
            ->then('://')
            ->maybe('www')
            ->anythingBut(' ');

        $this->assertEquals(0, $statement->match('http:/this.should.not.match '));
        $this->assertEquals(1, $statement->match('http://this.should.match '));
        $this->assertEquals(1, $statement->match('http://www.google.com'));
        $this->assertEquals(1, $statement->match('https://tweakers.net'));
    }

    public function testCaptureGroups()
    {
        $statement = $this->statement;

        $statement->anything()
            ->any('@')
            ->anything()
            ->any('.')
            ->anything();

        $matches = array();
        $this->assertEquals(1, $statement->match('rik.bruil@gmail.com', $matches));

        $this->assertEquals('rik.bruil', $matches[1]);
        $this->assertEquals('gmail', $matches[2]);
        $this->assertEquals('com', $matches[3]);
    }

    public function testNamedCaptures()
    {
        $statement = $this->statement;
        $statement->anything('address')
            ->any('@')
            ->anything('host')
            ->any('.')
            ->anything('tld');

        $matches = array();
        $this->assertEquals(1, $statement->match('rik.bruil@gmail.com', $matches));

        $this->assertArrayHasKey('address', $matches);
        $this->assertArrayHasKey('host', $matches);
        $this->assertArrayHasKey('tld', $matches);

        $this->assertEquals('rik.bruil', $matches['address']);
        $this->assertEquals('gmail', $matches['host']);
        $this->assertEquals('com', $matches['tld']);
    }

    public function testPostalCode()
    {
        $statement = $this->statement;
        $statement->range(1, 9)
            ->times(4)
            ->maybe(' ')
            ->range('A', 'Z')
            ->times(2);

        $this->assertEquals(1, $statement->match('5855 AP'));
        $this->assertEquals(1, $statement->match('5855AP'));
    }
}
