<?php
namespace Phonycron\Test\Acceptance;

use Phonycron\Parser;
use Phonycron\Job;

class JobRunAtTest extends \CustomTestCase
{
    /**
     * @dataProvider dataForRuns
     */
    public function testRuns($cronTime, $date, $runs=true)
    {
        $result = $this->runs($cronTime, $date);
        $this->assertEquals($runs, $result);
    }
    
    public function dataForRuns()
    {
        return array(
            array('* * * * *', '2011-01-01 10:00'),
            array('1 * * * *', '2011-01-01 10:01'),
            array('2 * * * *', '2011-01-01 10:01', false),
            
            array('*/4 * * * *', '2011-01-01 10:00'),
            array('*/4 * * * *', '2011-01-01 10:04'),
            array('*/4 * * * *', '2011-01-01 10:05', false),
            array('*/4 * * * *', '2011-01-01 10:59', false),
            array('*/4 * * * *', '2011-01-01 11:00'),
            
            array('1 2 3 4 *', '2011-04-03 02:01'),
            array('1 2 3 4 *', '2011-03-03 02:01', false),
            array('1 2 3 4 *', '2011-04-04 02:01', false),
            array('1 2 3 4 *', '2011-04-03 01:01', false),
            array('1 2 3 4 *', '2011-04-03 01:01', false),
            
            array('1 2 L 4 *', '2011-04-30 02:01'),
            array('1 2 L 4 *', '2011-04-29 02:01', false),
            
            array('1 2 L 5 *', '2011-05-31 02:01'),
            array('1 2 L 5 *', '2011-05-30 02:01', false),
            
            array('* * * * FRI', strtotime('next friday')),
            array('* * * * FRI', strtotime('next thursday'), false),
        );
    }
    
    public function runs($cronTime, $date)
    {    
        if (is_int($date)) {
            $date = new \DateTime('@'.$date);
        }
        else {
            $date = \DateTime::createFromFormat('Y-m-d H:i', $date);
        }
        $jobString = $cronTime.' JOB';
        $parser = new Parser();
        $jobs = $parser->parse($jobString, false);
        $runs = $jobs[0]->runsAt($date->getTimestamp());
        return $runs;
    }
}
