<?php
namespace Phonycron\Test\Unit\Parser;

use Phonycron\Parser;

class ParseNumericTest extends \CustomTestCase
{
    public function setUp()
    {
        $this->parser = new Parser(new \DateTimeZone('Australia/Melbourne'));
    }

    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testStar()
    {
        $expected = array(0, 1, 2);
        $parsed = $this->parser->parseNumeric('*', 0, 2);
        $this->assertEquals($expected, $parsed);
    }

    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testStarWithDivide()
    {
        $expected = array(0, 4, 8, 12, 16);
        $parsed = $this->parser->parseNumeric('*/4', 0, 16);
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testList()
    {
        $expected = array(4, 9, 12);
        $parsed = $this->parser->parseNumeric('4,9,12', 1, 20);
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testListWithDuplicates()
    {
        $expected = array(4, 9, 12);
        $parsed = $this->parser->parseNumeric('4,9,9,12', 1, 20);
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testListWithDivide()
    {
        $expected = array(4, 12);
        $parsed = $this->parser->parseNumeric('4,9,12/4', 0, 20);
        $this->assertEquals($expected, $parsed);
    }

    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testMixAndMatchSinglesAndARange()
    {
        $expected = array(4, 9, 10, 11, 12, 13, 14, 16);
        $parsed = $this->parser->parseNumeric('4,9,10-14,16', 1, 20);
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testMixAndMatchRanges()
    {
        $expected = array(4, 5, 6, 10, 11, 12);
        $parsed = $this->parser->parseNumeric('4-6,10-12', 1, 20);
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @expectedException Phonycron\ParseException
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testCompleteGarbage()
    {
        $parsed = $this->parser->parseNumeric('4-,6,10,-12', 1, 20);
    }
    
    /**
     * @expectedException Phonycron\ParseException
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testListBeyondMaxBoundFails()
    {
        $parsed = $this->parser->parseNumeric('1,5,12', 1, 9);
    }
    
    /**
     * @expectedException Phonycron\ParseException
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testListBeyondMinBoundFails()
    {
        $parsed = $this->parser->parseNumeric('1,5,12', 2, 13);
    }
    
    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testRange()
    {
        $expected = array(1, 2, 3);
        $parsed = $this->parser->parseNumeric('1-3', 1, 10);
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @expectedException Phonycron\ParseException
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testRangeBeyondMaxBoundFails()
    {
        $parsed = $this->parser->parseNumeric('1-10', 1, 9);
    }
    
    /**
     * @expectedException Phonycron\ParseException
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testRangeBeyondMinBoundFails()
    {
        $parsed = $this->parser->parseNumeric('1-10', 2, 10);
    }
    
    /**
     * @expectedException Phonycron\ParseException
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testInvertedRangeFails()
    {
        $parsed = $this->parser->parseNumeric('3-1', 1, 10);
    }
    
    /**
     * @expectedException Phonycron\ParseException
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testInvertedRangeInMixAndMatchFails()
    {
        $parsed = $this->parser->parseNumeric('1,6-2,7', 1, 10);
    }
    
    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testRangeBeginningAtZeroWithDivide()
    {
        $expected = array(0, 4, 8, 12);
        $parsed = $this->parser->parseNumeric('0-13/4', 0, 100);
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testRangeBeginningAtOneWithDivide()
    {
        $expected = array(1, 5, 9, 13);
        $parsed = $this->parser->parseNumeric('1-13/4', 0, 100);
        $this->assertEquals($expected, $parsed);
    }
    
    /**
     * @covers Phonycron\Parser::parseNumeric
     */
    public function testDivideWayBiggerThanMaxBound()
    {
        $expected = array(0);
        $parsed = $this->parser->parseNumeric('0-4/16', 0, 4);
        $this->assertEquals($expected, $parsed);
    }
}
