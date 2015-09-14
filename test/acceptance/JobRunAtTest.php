<?php
namespace Phonycron\Test\Acceptance;

use Phonycron\Parser;
use Phonycron\Job;

class JobRunAtTest extends \CustomTestCase
{
    /**
     * @dataProvider dataForRuns
     */
    public function testRuns($runs, $cronTime, $date)
    {
        $result = $this->runs($cronTime, $date);
        $this->assertEquals($runs, $result);
    }
    
    public function dataForRuns()
    {
        return array(
            array(true , '* * * * *', '2011-01-01 10:00'),
            array(true , '1 * * * *', '2011-01-01 10:01'),
            array(false, '2 * * * *', '2011-01-01 10:01'),
            
            array(true , '*/4 * * * *', '2011-01-01 10:00'),
            array(true , '*/4 * * * *', '2011-01-01 10:04'),
            array(false, '*/4 * * * *', '2011-01-01 10:05'),
            array(false, '*/4 * * * *', '2011-01-01 10:59'),
            array(true , '*/4 * * * *', '2011-01-01 11:00'),
            
            array(true , '1 2 3 4 *', '2011-04-03 02:01'),
            array(false, '1 2 3 4 *', '2011-03-03 02:01'),
            array(false, '1 2 3 4 *', '2011-04-04 02:01'),
            array(false, '1 2 3 4 *', '2011-04-03 01:01'),
            array(false, '1 2 3 4 *', '2011-04-03 01:01'),
            
            array(true , '1 2 L 4 *', '2011-04-30 02:01'),
            array(false, '1 2 L 4 *', '2011-04-29 02:01'),
            
            array(true , '1 2 L 5 *', '2011-05-31 02:01'),
            array(false, '1 2 L 5 *', '2011-05-30 02:01'),
            
            array(true , '* * * * FRI', strtotime('next friday')),
            array(false, '* * * * FRI', strtotime('next thursday')),
        );
    }
    
    public function runs($cronTime, $date)
    {
        $tz = new \DateTimeZone('Australia/Melbourne');
        if (is_int($date)) {
            $date = new \DateTime('@'.$date);
            $date->setTimeZone($tz);
        } else {
            $date = \DateTime::createFromFormat('Y-m-d H:i', $date, $tz);
        }

        $jobString = $cronTime.' JOB';
        $parser = new Parser(new \DateTimeZone('Australia/Melbourne'));
        $jobs = $parser->parse($jobString, false);
        $runs = $jobs[0]->runsAt($date);
        return $runs;
    }
}
