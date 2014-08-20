<?php
$usage = "Test runner
Usage: test/run.php [--filter=<expr>]
                    [--coverage-html=<outpath>]
                    [--exclude-group=<group>]
                    [--group=<group>]
";

$basePath = __DIR__;
$testPath = $basePath.'/test';
$libPath = $basePath.'/src';

require $basePath.'/vendor/autoload.php';
require $testPath.'/config.php';

date_default_timezone_set('Australia/Melbourne');

$options = array(
    'coverage-html'=>null,
    'filter'=>null,
    'exclude-group'=>null,
    'group'=>null,
);
$options = array_merge(
    $options,
    getopt('', array('help', 'filter:', 'coverage-html:', 'exclude-group:', 'group:'))
);
$help = array_key_exists('help', $options);
if ($help) {
    echo $usage;
    exit;
}

$config = array();

$groups = $options['group'] ? explode(',', $options['group']) : null;
$args = array(
    'reportDirectory'=>$options['coverage-html'],
    'coverageHtml'=>$options['coverage-html'],
    'filter'=>$options['filter'],
    'excludeGroups'=>explode(',', $options['exclude-group']),
    'groups'=>$groups,
    'strict'=>true,
    'processIsolation'=>false,
    'backupGlobals'=>false,
    'backupStaticAttributes'=>false,
    'convertErrorsToExceptions'=>true,
    'convertNoticesToExceptions'=>true,
    'convertWarningsToExceptions'=>true,
    'addUncoveredFilesFromWhitelist'=>true,
    'processUncoveredFilesFromWhitelist'=>true,
);

$suite = new PHPUnit_Framework_TestSuite();
suite_add_dir($suite, $basePath.'/test/');

$filter = new PHP_CodeCoverage_Filter();
$filter->addDirectoryToWhitelist($libPath, '.php');

$runner = new PHPUnit_TextUI_TestRunner(null, $filter);
$runner->doRun($suite, $args);

function suite_add_dir($suite, $dir)
{
    $iter = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($dir),
        \RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($iter as $item) {
        foreach (require_tests($item) as $class) {
            $suite->addTest(new PHPUnit_Framework_TestSuite($class));
        }
    }
}

function require_tests($file)
{
    static $cache = array();

    if (!preg_match("/Test\.php$/", $file))
        return array();

    $file = realpath($file);
    if (isset($cache[$file]))
        return $cache[$file];

    $prevClasses = get_declared_classes();
    require $file;
    $nowClasses = get_declared_classes();

    $tests = array_diff($nowClasses, $prevClasses);
    $found = array();
    foreach ($tests as $class) {
        if (preg_match("/Test$/", $class)) {
            $found[] = $class;
        }
    }
    $cache[$file] = $found;

    return $found;
}

