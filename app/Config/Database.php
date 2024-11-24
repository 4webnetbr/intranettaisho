<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations
     * and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to
     * use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
<<<<<<< HEAD
     * The default database connection.
     */
    public array $default = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'taisho_userdb',
        'password' => 'JePPiS@9wE9D6Qk#ZWtpIH',
        'database' => 'taisho_gerentedb',
<<<<<<< HEAD
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
=======
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
     * The config database connection.
     */
    public array $default = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'taisho_userdb',
        'password' => 'JePPiS@9wE9D6Qk#ZWtpIH',
        'database' => 'taisho_configdb',
=======
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * The config database connection.
     */
    public array $dbEstoque = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'taisho_userdb',
        'password' => 'JePPiS@9wE9D6Qk#ZWtpIH',
<<<<<<< HEAD
=======
        'database' => 'taisho_configdb',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * The config database connection.
     */
    public array $dbEstoque = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'taisho_userdb',
        'password' => 'JePPiS@9wE9D6Qk#ZWtpIH',
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
        'database' => 'taisho_estoquedb',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        // 'charset'  => 'utf8mb4', // UTF-8 para caracteres especiais
        // 'DBCollat' => 'utf8mb4_general_ci',
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * The config database connection.
     */
    public array $dbRh = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'taisho_userdb',
        'password' => 'JePPiS@9wE9D6Qk#ZWtpIH',
        'database' => 'taisho_rhdb',
<<<<<<< HEAD
=======
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * The Delivery database connection.
     */
    public array $dbDelivery = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'taisho_userdb',
        'password' => 'JePPiS@9wE9D6Qk#ZWtpIH',
        'database' => 'taisho_deliverydb',
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * The Delivery database connection.
     */
    public array $dbDelivery = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'taisho_userdb',
        'password' => 'JePPiS@9wE9D6Qk#ZWtpIH',
        'database' => 'taisho_deliverydb',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];

    /**
     * The default database connection.
     */
    public array $dbEstoque = [
        'DSN'      => '',
        'hostname' => 'localhost', 
        'username' => 'estoque_user',
        'password' => 'n3hoUV3LwAIyE2gv8C55',
        'database' => 'estoque_db',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];


    /**
     * This database connection is used when
     * running PHPUnit database tests.
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => 'utf8_general_ci',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
    ];

    public function __construct()
    {
        parent::__construct();

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
