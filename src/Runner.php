<?php
namespace Phonycron;

abstract class Runner
{
    public $outputHandler;
    
    protected abstract function runDue(Job $job);
    
    public function run(array $jobs, $runTime)
    {
        $runTime = Functions::ensureDateTime($runTime);

        $toRun = array();
        foreach ($jobs as $job) {
            if ($job->runsAt($runTime)) {
                $toRun[] = $job;
            }
        }

        foreach ($toRun as $job) {
            ob_start();
            $output = $this->runDue($job);
            $output .= ob_get_clean();
            if ($this->outputHandler) {
                $this->outputHandler->handle($job, $output);
            }
        }
    }
}
