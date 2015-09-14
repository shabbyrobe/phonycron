<?php
namespace Phonycron\Test\Unit\Parser;

use Phonycron\Parser;

/**
 * This job was adapted from an example on the wikipedia cron page and is
 * described as running:
 * 
 *    On the 11th to 26th of each month in January to June on every third 
 *    minute starting from 2 past 1am, 5am, 6am, 7am, 10am, 11am and 10pm
 *
 */
class ParseComplexJobTest extends \CustomTestCase
{
    public function setUp()
    {
        $this->parser = new Parser(new \DateTimeZone('Australia/Melbourne'));
        
        $jobs = $this->parser->parse('2-59/3 1,5-7,10-11,22 11-26 1-6 ? do stuff');
        $this->job = $jobs[0];
    }
    
    /**
     * @covers Phonycron\Job::runsAt
     * @dataProvider dataForRunsAt
     */
    public function testRunsAt($date, $time, $runs)
    {
        $timestamp = \DateTime::createFromFormat('YmdHi', $date.$time);
        $this->assertEquals($runs, $this->job->runsAt($timestamp));
    }
    
    public function dataForRunsAt()
    {
        return array(
            // testing minutes: month, day and hour should always match
            array('20110111', '0500', false),
            array('20110111', '0502', true),
            array('20110111', '0504', false),
            array('20110111', '0505', true),
            array('20110111', '0508', true),
            array('20110111', '0510', false),
            array('20110111', '0511', true),
            array('20110111', '0512', false),
            array('20110111', '0559', true),
            
            // testing hours: month, day and minute should always match
            array('20110111', '0002', false),
            array('20110111', '0102', true),
            array('20110111', '0202', false),
            array('20110111', '0302', false),
            array('20110111', '0402', false),
            array('20110111', '0502', true),
            array('20110111', '0602', true),
            array('20110111', '0702', true),
            array('20110111', '0802', false),
            array('20110111', '0902', false),
            array('20110111', '1002', true),
            array('20110111', '1102', true),
            array('20110111', '1202', false),
            array('20110111', '1302', false),
            array('20110111', '1402', false),
            array('20110111', '1502', false),
            array('20110111', '1602', false),
            array('20110111', '1702', false),
            array('20110111', '1802', false),
            array('20110111', '1902', false),
            array('20110111', '2002', false),
            array('20110111', '2102', false),
            array('20110111', '2202', true),
            array('20110111', '2302', false),
            
            // testing days: month, hour and minute should always match
            array('20110101', '0502', false),
            array('20110102', '0502', false),
            array('20110110', '0502', false),
            array('20110111', '0502', true),
            array('20110112', '0502', true),
            array('20110113', '0502', true),
            // snip. will add if needed
            array('20110125', '0502', true),
            array('20110126', '0502', true),
            array('20110127', '0502', false),
            array('20110128', '0502', false),
            
            // testing month: day, hour and minute should always match
            array('20110111', '0502', true),
            array('20110211', '0502', true),
            array('20110311', '0502', true),
            array('20110411', '0502', true),
            array('20110511', '0502', true),
            array('20110611', '0502', true),
            array('20110711', '0502', false),
            array('20110811', '0502', false),
            array('20110911', '0502', false),
            array('20111011', '0502', false),
            array('20111111', '0502', false),
            array('20111211', '0502', false),
        );
    }
}
