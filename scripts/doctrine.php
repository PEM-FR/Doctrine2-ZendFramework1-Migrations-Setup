<?php
// Display errors ?
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH','/your/path/to/zendframework/application');

// Define application environment
defined('APPLICATION_ENV')
    || define(
        'APPLICATION_ENV',
        (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development')
    );


// Ensure library/ is on include_path
set_include_path(
    '/path/to/your/library/Zend' . PATH_SEPARATOR .
    '/path/to/Doctrine' . PATH_SEPARATOR .
    get_include_path()
);

// Requiring a batch of Classes we will need for namespacing
use Doctrine\Common\ClassLoader,
    Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper,
    Doctrine\ORM\Version,
    Doctrine\ORM\Tools\Console\ConsoleRunner,
    Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper,
    Symfony\Component\Console\Helper\HelperSet,
    Symfony\Component\Console\Helper\DialogHelper,
    Symfony\Component\Console\Application;

// namespacing migrations commands we will be needing later
use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand,
    Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
    Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
    Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
    Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
    Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand();

// We need the Doctrine ClassLoader to manage autoloading
require_once 'Doctrine/Common/ClassLoader.php';

// Load Doctrine
$classLoader = new ClassLoader('Doctrine');
$classLoader->register();

// Load Symfony tools
$classLoader = new ClassLoader('Symfony', 'Doctrine');
$classLoader->register();

// Load Migration
$classLoader = new ClassLoader('Migrations', 'Doctrine/DBAL/');
$classLoader->register();

// Zend_Application
require_once 'Zend/Application.php';

// Create application
$application = new Zend_Application(
    APPLICATION_ENV, '/path/to/application.ini'
);

// Bootstrap
$application->bootstrap();

// loading doctrine resource, sometimes called entityManager
$em = $application->getBootstrap()->getResource('doctrine');

// Load doctrine helpers
$helperSet = new HelperSet(array(
    'db'     => new ConnectionHelper($em->getConnection()),
    'em'     => new EntityManagerHelper($em),
    'dialog' => new DialogHelper()
));

$cli = new Application('Doctrine Command Line Interface', Version::VERSION);
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);

// We are settign the commands to bypass the configuration process and
// directly use our ZendConfiguration
require_once('ZendConfiguration.php');
$connexion = $em->getConnection();
$zendConfig = new ZendConfiguration($connexion);

// injecting configurations necessary to our ZendConfiguration
// Pass the Application.ini Parameters to our Configuration
$applicationConfig = new Zend_Config($application->getBootstrap()->getOptions(), true);
$zendConfig->setConfig($applicationConfig->resources->migration);
// Here we just need to put some string because the parameter is not optional
// though will not need it. Just need to call the function
$config->load('zend');

// Setting up Migrations Commands
$diffCmd = new DiffCommand();
$diffCmd->setMigrationConfiguration($zendConfig);

$executeCmd = new ExecuteCommand();
$executeCmd->setMigrationConfiguration($zendConfig);

$generateCmd = new GenerateCommand();
$generateCmd->setMigrationConfiguration($zendConfig);

$migrateCmd = new MigrateCommand();
$migrateCmd->setMigrationConfiguration($zendConfig);

$statusCmd = new StatusCommand();
$statusCmd->setMigrationConfiguration($zendConfig);

$versionCmd = new VersionCommand();
$versionCmd->setMigrationConfiguration($zendConfig);


// Register migration Commands
$cli->addCommands(array(
    $diffCmd, $executeCmd, $generateCmd, $migrateCmd, $statusCmd, $versionCmd
));

// Register All Doctrine Commands
ConsoleRunner::addCommands($cli);

// Runs console application
$cli->run();