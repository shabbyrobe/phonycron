<?php
namespace Phonycron;

class SystemRunner extends Runner
{
    public $cwd;
    
    public $outputTemplate = "<err>\n<out>";
    
    public function __construct($cwd=null)
    {
        $this->cwd = $cwd;
    }
    
    protected function runDue(Job $job)
    {
        $output = null;
        if ($this->cwd)
            chdir($this->cwd);
        
        $spec = [['pipe', 'r'], ['pipe', 'w'],  ['pipe', 'w']];
        $p = proc_open($job->command, $spec, $pipes);
        fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        $return = proc_close($p);
        
        return strtr($this->outputTemplate, array(
            '<out>'=>$out,
            '<err>'=>$err,
            '<return>'=>$return
        ));
    }
}
