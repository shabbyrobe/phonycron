<?php
namespace Phonycron\Test\Acceptance;

use Phonycron\Parser;
use Phonycron\Job;

class JobMaxRunsPerDayTest extends \JobTestCase
{
    /**
     * @dataProvider dataForMaxRuns
     */
    public function testMaxRunsPerDay($cronTime, $runs)
    {
        $result = $this->maxRunsPerDay($cronTime);
        $this->assertEquals($runs, $result);
    }
    
    public function dataForMaxRuns()
    {
        return array(
            array('*     *    * * *', 1440),
            array('2     *    * * *', 24),
            array('2-5   *    * * *', 96),
            array('2-5   1    * * *', 4),
            array('*     1    * * *', 60),
            array('*     1-5  * * *', 300),
            array('*/15  *    * * *', 96),
        );
    }
    
    public function testFindMinRunsPerDay()
    {
        $list = array(
            $this->createDummyJob('2     * * * *'),
            $this->createDummyJob('2,3,4 * * * *'),
            $this->createDummyJob('2,3   * * * *'),
        );
        $runs = Job::findMinRunsPerDay($list);
        $this->assertEquals(72, $runs);
    }
    
    public function maxRunsPerDay($cronTime)
    {
        $job = $this->createDummyJob($cronTime);
        $runs = $job->maxRunsPerDay();
        return $runs;
    }
}
