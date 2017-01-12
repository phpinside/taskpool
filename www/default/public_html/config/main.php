<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath'=>dirname(__FILE__).'/../../htdocs/protected',
	'runtimePath'=>dirname(__FILE__).'/../runtime',
	'name'=>'任务池',
	'sourceLanguage' => 'zh_cn', 
	'timeZone' => 'PRC',


	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'ext.directmongosuite.components.*',
		'ext.PHP_EXCEL.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'111111',
		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1','10.0.0.16'),
		),
		
	),

	'behaviors' => array(
		/* 'edms' => array(
			'class'=>'EDMSBehavior',
			// 'connectionId' = 'mongodb' //if you work with yiimongodbsuite 
			//see the application component 'EDMSConnection' below
			// 'connectionId' = 'edms' //default;
			//'debug'=>true //for extended logging
		)
		*/
	),

	// application components
	'components'=>array(
		'themeManager'	=> array(
			'basePath'	=> dirname(__FILE__).'/../../htdocs/protected/themes'
		),
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/
		/*'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),*/
		// uncomment the following to use a MySQL database
		
		'db'=>array(
			'connectionString' => 'mysql:host=mysql;dbname=taskpool',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'my123456',
			'charset' => 'utf8',
			'schemaCachingDuration' => 0, //数据表结构缓存时间
			'enableProfiling' => true, //是否启用查询的 Profiling
		),
		
 
        //configure the mongodb connection
        //set the values for server and options analog to the constructor 
        //Mongo::__construct from the PHP manual
        /*
		'edms' => array(
            'class'            => 'EDMSConnection',
            'dbName'           => 'taskpool',
        	'server'           => 'mongodb://10.0.0.16:27017' //default
        	//'options'  => array(.....); 
        ),
        */
        
 
		'errorHandler'=>array(
			// use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				
				array(
					'class'=>'CWebLogRoute',
				),
				array(
					'class'=>'CProfileLogRoute',
				)
				
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'taskpool.devel@hudong.com',

		//！！重要，修改时须同步修改 console.php 配置中的此参数！！！！！最大分发次数
		'maxDispatchCount'=>3, 
	
		'ldap' => array(
			'host' => 'ldap',
			'ou' => array('tech'), // such as "people" or "users"
			'dc' => array('intra', 'denggao','org'),
		),
		'admins' => array('songdenggao','panxuepeng'),
		'team_leader'=>array('songdenggao','liguangming'),
		'viewer'=>array()
	),
);