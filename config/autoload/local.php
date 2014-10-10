<?php
/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */
$db = array(
        'database' => 'db_clickmark',
        'username' => 'dev_user',
        'password' => '3f8af7c0504a99c0',
        'hostname' => 'ckickmark-master.cvh6qgvenkpw.us-east-1.rds.amazonaws.com',
        'port' => '3306'
);

return array(
        'service_manager' => array(
                'factories' => array(
                        'zend_db_adapter' => function  ($sm) use( $db)
                        {
                            return new Zend\Db\Adapter\Adapter(
                                    array(
                                            'driver' => 'Pdo_Mysql',
                                            'dns' => 'mysql:host=' .
                                                     $db['hostname'] . ';port=' .
                                                     $db['port'] . ';dbname=' .
                                                     $db['database'],
                                                    'database' => $db['database'],
                                                    'username' => $db['username'],
                                                    'password' => $db['password'],
                                                    'hostname' => $db['hostname'],
                                                    'port' => $db['port'],
                                                    'driver_options' => array(
                                                            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                                                    )
                                    ));
                        },
                        
                        'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory'
                )
        )
);