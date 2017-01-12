<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).'/../../htdocs/protected',
	'name'=>'Task Pool Console',
	'sourceLanguage' => 'zh_cn', 
	'timeZone' => 'PRC',
	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),
	
	// application components
	'components'=>array(
		'db'=>array(
			'connectionString' => 'mysql:host=172.17.0.4;dbname=taskpool',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'weiou2015',
			'charset' => 'utf8',
			'schemaCachingDuration' => 0, //数据表结构缓存时间
			'enableProfiling' => true, //是否启用查询的 Profiling
		),
		
	),
	'params'=>array(
		'adminEmail'=>'taskpool.devel@weiou.com',
		'baseUrl'=>'http://task.weiou.com/index.php?r=',
		
		//！！重要，修改时须同步修改 main.php 配置中的此参数！！！！！最大分发次数
		'maxDispatchCount'=>3, //最大分发次数
		
		'dispatchTime'=>60, //每次分发间隔
	),
);