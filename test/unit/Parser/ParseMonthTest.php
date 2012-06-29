<?php
namespace Phonycron\Test\Unit\Parser;

use Phonycron\Parser;

class ParseMonthTest extends \CustomTestCase
{
    public function setUp()
    {
        $this->parser = new Parser;
    }

    /**
     * @covers Phonycron\Parser::parseMonth
     */
    public function testNumericMonth()
    {
        $expected = array(2);
        $parsed = $this->parser->parseMonth('2');
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @expectedException Phonycron\ParseException
     * @covers Phonycron\Parser::parseMonth
     */
    public function testInvalidLowNumericMonth()
    {
        $parsed = $this->parser->parseMonth('0');
    }
    
    /**
     * @expectedException Phonycron\ParseException
     * @covers Phonycron\Parser::parseMonth
     */
    public function testInvalidHighNumericMonth()
    {
        $parsed = $this->parser->parseMonth('13');
    }

    /**
     * @covers Phonycron\Parser::parseMonth
     */
    public function testNumericMonthList()
    {
        $expected = array(2, 5);
        $parsed = $this->parser->parseMonth('2,5');
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseMonth
     */
    public function testNumericMonthRange()
    {
        $expected = array(2, 3, 4, 5);
        $parsed = $this->parser->parseMonth('2-5');
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseMonth
     */
    public function testTextMonth()
    {
        $expected = array(2);
        $parsed = $this->parser->parseMonth('FEB');
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseMonth
     */
    public function testTextMonthRange()
    {
        $expected = array(2,3,4);
        $parsed = $this->parser->parseMonth('FEB-APR');
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseMonth
     */
    public function testTextMonthList()
    {
        $expected = array(2, 4, 6);
        $parsed = $this->parser->parseMonth('FEB,APR,JUN');
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseMonth
     */
    public function testTextMonthDivide()
    {
        $expected = array(2, 4, 6);
        $parsed = $this->parser->parseMonth('FEB-JUN/2');
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseMonth
     */
    public function testInvalidTextMonth()
    {
        $parsed = $this->parser->parseMonth('GRN');
        $this->assertFalse($parsed);
    }
}
