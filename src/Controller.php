<?php
namespace Phonycron;

class Controller
{
    public $runner;
    public $parser;
    public $cwd;
    public $forceRun;
    public $crontab;
    public $outputHandle;
    public $errorHandle;

    private $tz;
    
    protected $usage;
    
    public function __construct($crontab, $cwd, $runner=null, $parser=null, $tz=null)
    {
        if (!$runner) {
            $runner = new SystemRunner();
            $runner->cwd = $cwd;
        }
        if ($tz === null) {
            $tz = new \DateTimeZone(date_default_timezone_get());
        }
        if (!$parser) {
            $parser = new Parser($tz);
        }

        $this->tz = $tz;
        $this->crontab = $crontab;
        $this->runner = $runner;
        $this->cwd = $cwd;
        $this->parser = $parser;

        
        $this->usage = 
            "cron.php [OPTIONS]\n".
            "  -l       List all jobs\n".
            "  -c       List jobs that would run this minute\n".
            "  -r       Run jobs\n".
            "  -v       Verbose\n".
            "  -p       Test parsing the crontab\n".
            "  -t date  Set the run time. Anything accepted by DateTime's constructor.\n"
        ;
    }
    
    public function run()
    {
        // VELOCIRAPTOR!!
        $options = getopt('vlcrpt:');
        
        if (isset($this->forceRun)) {
            $options['r'] = true;
        }

        $runTime = new \DateTime('now', $this->tz);
        if (isset($options['t'])) {
            $runTime = new \DateTime($options['t'], $this->tz);
            if (isset($options['v'])) {
                $this->out("Run time set to ".$runTime->format('Y-m-d H:i').PHP_EOL);
            }
        }
        
        $jobs = $this->parser->parse($this->crontab);
        if (isset($options['l'])) {
            foreach ($jobs as $j) {
                $this->out($j->raw."\n");
            }
        }
        elseif (isset($options['c'])) {
            foreach ($jobs as $j) {
                if ($j->runsAt($runTime)) {
                    $this->out($j->raw."\n");
                }
            }
        }
        elseif (isset($options['p'])) {
            // do nothing. kludge for parse testing.
        }
        elseif (isset($options['r'])) {
            if (isset($options['v'])) {
                $this->runner->outputHandler = new \Phonycron\EchoOutputHandler();
            }
            $this->runner->run($jobs, $runTime);
        }
        else {
            fwrite($this->errorHandle ?: STDERR, $this->usage);
        }
    }

    private function out($text)
    {
        $h = $this->outputHandle ?: STDOUT;
        fwrite($h, $text);
    }
}
