<?php

/**
 * Load migration configuration information from
 * a Zend application.ini configuration file.
 *
 * @author Nathan Nobbe <nathan@moxune.com>
 */
class ZendConfiguration
	extends Doctrine\DBAL\Migrations\Configuration\AbstractFileConfiguration
{

    /**
     * @var Zend_Config $config
     */
    protected $_config;

    /**
     * This function is used to inject configuration into the ZendConfiguration
     * @param Zend_Config $config
     */
    public function setConfig(Zend_Config $config)
    {
        $this->_config = $config;
    }

    /**
     * @return Zend_Config $config
     */
    protected function _getConfig()
    {
        return $this->_config;
    }

    /**
     * @inheritdoc
     */
    protected function doLoad($file)
    {
        $migrationsConfig = $this->_getConfig();

        // set migrations name
        if(isset($migrationsConfig->name))
            $this->setName((string)$migrationsConfig->name);

        // set migrations table name
        if(isset($migrationsConfig->tableName))
            $this->setMigrationsTableName(
                (string)$migrationsConfig->tableName
            );

        // set migrations namespace
        if(isset($migrationsConfig->namespace))
            $this->setMigrationsNamespace(
                (string)$migrationsConfig->namespace
            );

        // set migrations directory
        // (assuming absolute path specificed via APPLICATION_PATH)
        if(isset($migrationsConfig->directory)) {
            $this->setMigrationsDirectory($migrationsConfig->directory);
            $this->registerMigrationsFromDirectory(
                $migrationsConfig->directory
            );
        }

        // register custom migrations
        if(isset($migrationsConfig->migrations))
            foreach($migrationsConfig->migrations as $migration) {
                $this->registerMigration(
                    (string)$migration->version,
                    (string)$migration->class
                );
            }
    }
}