<?php

use Rb\VerbalRegex\Statement;

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
        $statement->startsWith('te')
                ->search('st')
                ->endOfLine();

        $expected = '/^test$/';

        $statement->match('test', $result);
        $this->assertEquals($expected, $statement . '');
        $this->assertEquals(1, $result);
    }

    public function testUrl()
    {
        $statement = $this->statement;
        $statement->find('http')
            ->maybe('s')
            ->then('://')
            ->maybe('www')
            ->endsWith()
            ->anythingBut(' ');

        $this->assertEmpty($statement->match('http:/this.should.not.match'));
        $this->assertEmpty($statement->match('http://should.not.match '));
        $this->assertNotEmpty($statement->match('http://this.should.match'));
        $this->assertNotEmpty($statement->match('http://www.google.com'));
        $this->assertNotEmpty($statement->match('https://tweakers.net'));
    }

    public function testCaptureGroups()
    {
        $statement = $this->statement;

        $statement->anything()
            ->any('@')
            ->anything()
            ->any('.')
            ->anything();

        $matches = $statement->match('ad.dress@domain.com');

        $this->assertNotEmpty($matches);
        $this->assertEquals('ad.dress', $matches[1]);
        $this->assertEquals('domain', $matches[2]);
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

        $matches = $statement->match('ad.dress@domain.com');

        $this->assertNotEmpty($matches);
        $this->assertArrayHasKey('address', $matches);
        $this->assertArrayHasKey('host', $matches);
        $this->assertArrayHasKey('tld', $matches);

        $this->assertEquals('ad.dress', $matches['address']);
        $this->assertEquals('domain', $matches['host']);
        $this->assertEquals('com', $matches['tld']);
    }

    public function testPostalCode()
    {
        $statement = $this->statement;
        $statement->range(1, 9)
            ->times(4)
            ->search(' ')->between(0, 1) // same as maybe(' ')
            ->range('A', 'Z')
            ->times(2);

        $this->assertNotEmpty($statement->match('4321 AB'));
        $this->assertNotEmpty($statement->match('4321AB'));
    }

    public function testAlternativePostalCode()
    {
        $statement = $this->statement;
        $statement->decimals('numbers')
            ->maybe(' ')
            ->words('letters');

        $result = null;

        $statement->match('1234CD', $result);
        $this->assertEquals(1, $result);

        $matches = $statement->match('1234 CD');

        $this->assertNotEmpty($matches);
        $this->assertArrayHasKey('numbers', $matches);
        $this->assertArrayHasKey('letters', $matches);

        $this->assertEquals('1234', $matches['numbers']);
        $this->assertEquals('CD', $matches['letters']);
    }
}
