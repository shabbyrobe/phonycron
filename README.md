Phonycron
=========

Phonycron is a PHP-based parser for a vixie cron schedule. It was created in
order to allow a cron schedule to be versioned and managed as part of a PHP
project, while maintaining compatibility with an existing complex crontab.

It is not 100% complete, but it should be compatible with almost every cron job
you will ever use. See the *Limitations* section for more information.


Quickstart
----------

Create a crontab file called `crontab` in your project with the following
contents:

```
# every minute
* * * * * script1.php

# every hour
0 * * * * script2.php
```


Add `script1.php` and `script2.php` to your project:

```php
<?php
// script1.php
echo "Script 1";
```

```php
<?php
// script2.php
echo "Script 2";
```

Create a PHP script called `cron.php` in your project:

```php
<?php
// Register phonycron's default autoloader.
$phonycronPath = '/path/to/phonycron';
require_once($phonycronPath.'/src/Loader.php');
Phonycron\Loader::register();


// Parse the crontab 
$parser = new Phonycron\Parser();
$jobs = $parser->parse(file_get_contents(__DIR__.'/crontab'));


// Create a runner and optional output handler
$cronWorkingDirectory = __DIR__;
$runner = new Phonycron\SystemRunner($cronWorkingDirectory);
$runner->outputHandler = new Phonycron\EchoOutputHandler();

// Run all jobs that are due to be run at time()
$runner->run($jobs, time());

```

Test it out:

    php cron.php


Add to your system's cron to run every minute:

```
cat <( crontab -l ) <( echo "* * * * * php /path/to/your/project/cron.php" ) | crontab -
```

Phonycron is PSR-0 compliant, so you can use any autoloader that supports this
standard.


Customising
-----------

Phonycron comes with a very limited set of output handlers and runners by
default. It is very easy to create your own though.


### Runners

Create your own custom runner by extending `Phonycron\Runner` and implementing
the `runDue` method. This is called when a job is due to be run and is passed an
instance of `Phonycron\Job`. The `$command` property of `Phonycron\Job`
contains the command portion of the crontab entry.

If you would like your crontab to define PHP scripts to require, instead of
system commands to run, you could create a runner like so:

```php
<?php
class RequireRunner extends Phonycron\Runner
{
    public $scriptDir;
    
    public function __construct($scriptDir)
    {
        $this->scriptDir = $scriptDir;
    }
    
    protected function runDue(Phonycron\Job $job)
    {
        // careful - make sure your inputs are sanitised!
        $fullPath = realpath($this->scriptDir.'/'.$job->command);
        if (!$fullPath)
            throw new \RuntimeException("Script $fullPath not found");
        if (strpos($fullPath, $this->scriptDir)!==0)
            throw new \RuntimeException("Path breakout");
        
        require($fullPath);
    }
}
```


### Output Handlers

Phonycron swallows all output by default. If your `Phonycron\Runner` has an
instance of `Phonycron\OutputHandler` in its `$outputHandler` property, the
output of each job will be passed to it.

If you want to log the output to a file, you could create an output handler like
so:
 

```php
<?php
class FileLogHandler implements OutputHandler
{
    public $file;
    public $handle;
    
    public function __construct($file)
    {
        $this->file = $file;
        $this->handle = fopen($this->file, 'a');
    }
    
    public function handle(Phonycron\Job $job, $output)
    {
        fwrite($this->handle, $output.PHP_EOL);
    }
    
    public function __destruct()
    {
        fclose($this->handle);
    }
}

```


Limitations
-----------

Phonycron doesn't implement 100% of the features that a typical unix system cron
would implement.

* **Mix-n-match lists and ranges**
  
  e.g. `1-15,32,55 * * * *` for every minute for the first fifteen  minutes,
  followed by once on the thirty-second minute and once on  the fifty-fifth
  minute.

* **`W` modifier in the "day of month" field**  

  e.g. `* * 3W * *` for the nearest weekday to the 3rd of the month

* **`#` modifier in the "day of week" field**  

  e.g. `0 0 ? 1 3#2` for the second wednesday in January

* **`@reboot` macro**


License
=======

Phonycron is released under an MIT license. Details are in the `LICENSE` file.

