<?php
namespace Phonycron;

class EchoOutputHandler implements OutputHandler
{
    public $colours = false;

    public function handle(Job $job, $output)
    {
        echo ($this->colours ? "\033[1;32m" : '').date('Y-m-d H:i').': '.$job->raw.PHP_EOL;
        $output = trim($output);
        echo ($this->colours ? "\033[0m" : '').($output ? $output.PHP_EOL.PHP_EOL : null);
    }
}
