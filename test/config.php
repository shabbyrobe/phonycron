<?php

/*
 * This file is part of Phonycron.
 * 
 * Phonycron is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Phonycron is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with Phonycron.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * Copyright 2011 Blake Williams
 * http://k3jw.com 
 */

abstract class CustomTestCase extends \PHPUnit_Framework_TestCase
{
    protected function callProtected($class, $name)
    {
        $ref = new ReflectionClass($class);
        $method = $ref->getMethod($name);
        $method->setAccessible(true);
        
        if ($method->isStatic()) $class = null;
        
        return $method->invokeArgs($class, array_slice(func_get_args(), 2));
    }
    
    protected function getProtected($class, $name)
    {
        $ref = new ReflectionClass($class);
        $property = $ref->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($class);
    }
    
    public function matchesLoose($string)
    {
        return new \LooseStringMatch($string);
    }
}

abstract class JobTestCase extends CustomTestCase
{
    protected function createDummyJob($time, $command = 'JOB')
    {
        $jobString = $time.' '.$command;
        $parser = new \Phonycron\Parser();
        $jobs = $parser->parse($jobString, false);
        if (!$jobs)
            throw new \UnexpectedValueException("Job parsing failed: $jobString");
        
        return $jobs[0];
    }
}

class LooseStringMatch extends \PHPUnit_Framework_Constraint
{
    /**
     * @var string
     */
    protected $string;

    /**
     * @param string $pattern
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     */
    public function evaluate($other, $description = '', $returnResult = FALSE)
    {
        $result = false;
        if ($this->string) {
            $pattern = '/'.preg_replace('/\s+/', '\s*', preg_quote($this->string, '/')).'/ix';
            $result = preg_match($pattern, $other) > 0;
        }
        if (!$returnResult) {
            if (!$result) $this->fail($other, $description);
        }
        else
            return $result;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf(
          'matches loose string "%s"',

          $this->string
        );
    }
}
