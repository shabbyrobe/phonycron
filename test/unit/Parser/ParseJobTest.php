<?php
namespace Phonycron\Test\Unit\Parser;

use Phonycron\Parser;

class ParseJobTest extends \CustomTestCase
{
    public function setUp()
    {
        $this->parser = new Parser(new \DateTimeZone('Australia/Melbourne'));
    }
    
    /**
     * @covers Phonycron\Parser::parse
     */
    public function testCommentedLinesSkipped()
    {
        $input = "# foobar\n* * * * * job";
        $jobs = $this->parser->parse($input);
        $this->assertEquals(1, count($jobs));
    }
    
    /**
     * @covers Phonycron\Parser::parse
     * @expectedException Phonycron\ParseException
     */
    public function testParseLineWithNoJobFailsWhenNotEnabled()
    {
        $input = "* * * * *";
        $jobs = $this->parser->parse($input);
    }
    
    /**
     * @covers Phonycron\Parser::parse
     */
    public function testParseDayOfWeekAsQuestionMark()
    {
        $input = "* * * * ? command";
        $jobs = $this->parser->parse($input);
        $this->assertEquals(array(0,1,2,3,4,5,6), $jobs[0]->daysOfWeek);
    }
    
    /**
     * @covers Phonycron\Parser::parse
     */
    public function testParseLineWithNoJobSucceedsWhenEnabled()
    {
        $this->parser->allowEmptyJob = true;
        $input = "* * * * *";
        $jobs = $this->parser->parse($input);
        $this->assertEquals(1, count($jobs));
        $this->assertEquals(null, $jobs[0]->command);
    }
    
    /**
     * @covers Phonycron\Parser::parse
     */
    public function testCommentedLinesPrecededByWhitespaceSkipped()
    {
        $input = "    # foobar\n* * * * * job";
        $jobs = $this->parser->parse($input);
        $this->assertEquals(1, count($jobs));
    }
    
    /**
     * @covers Phonycron\Parser::parse
     */
    public function testEmptyLinesSkipped()
    {
        $input = "\n\n* * * * * job\n\n* * * * * job2\n\n";
        $jobs = $this->parser->parse($input);
        $this->assertEquals(2, count($jobs));
    }
    
    /**
     * @covers Phonycron\Parser::parse
     */
    public function testWhitespaceOnlyLinesSkipped()
    {
        $input = "\n\n* * * * * job\n     \n* * * * * job2\n\n";
        $jobs = $this->parser->parse($input);
        $this->assertEquals(2, count($jobs));
    }
    
    /**
     * @covers Phonycron\Parser::parse
     */
    public function testParseMacro()
    {
        $jobs = $this->parser->parse('@weekly foobar');
        $job = $jobs[0];
        
        $this->assertEquals('foobar', $job->command);
        $this->assertEquals(array(0), $job->minutes);
        $this->assertEquals(array(0), $job->hours);
        $this->assertEquals(array(0), $job->daysOfWeek);
        $this->assertEquals(range(1, 12), $job->months);
        $this->assertEquals(range(1, 31), $job->daysOfMonth);
    }
    
    /**
     * @covers Phonycron\Parser::parse
     */
    public function testParseCustomMacro()
    {
        $this->parser->macros['@foobar'] = '1 2 3 4 5';
        $jobs = $this->parser->parse('@foobar bazqux');
        $job = $jobs[0];
        
        $this->assertEquals('bazqux', $job->command);
        $this->assertEquals(array(1), $job->minutes);
        $this->assertEquals(array(2), $job->hours);
        $this->assertEquals(array(3), $job->daysOfMonth);
        $this->assertEquals(array(4), $job->months);
        $this->assertEquals(array(5), $job->daysOfWeek);
    }
    
    /**
     * @covers Phonycron\Parser::parse
     * @expectedException Phonycron\ParseException
     */
    public function testParseUnknownMacroFails()
    {
        $jobs = $this->parser->parse('@dingdong yep');
    }
}
