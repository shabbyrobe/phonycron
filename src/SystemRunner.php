<?php
namespace Phonycron;

class SystemRunner extends Runner
{
    public $cwd;
    
    public function __construct($cwd=null)
    {
        $this->cwd = $cwd;
    }
    
    protected function runDue(Job $job)
    {
        $output = null;
        if ($this->cwd)
            chdir($this->cwd);
        
        exec($job->command, $output);
        return implode(PHP_EOL, $output);
    }
}
