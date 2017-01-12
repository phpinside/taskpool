-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2012 年 08 月 10 日 06:56
-- 服务器版本: 5.5.27
-- PHP 版本: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `taskpool`
--

-- --------------------------------------------------------

--
-- 表的结构 `rank`
--

CREATE TABLE IF NOT EXISTS `rank` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排行版开始时间',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排行版结束时间',
  `rankype` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1为周排行，2为月排行，3为半年排行，4为年度排行',
  `rankcode` text NOT NULL COMMENT '状态变更时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `task`
--

CREATE TABLE IF NOT EXISTS `task` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '任务id',
  `user_id` int(11) NOT NULL COMMENT '任务创建者uid',
  `subject` varchar(250) NOT NULL COMMENT '任务标题',
  `description` text NOT NULL COMMENT '任务描述',
  `gain` text NOT NULL COMMENT '任务交付成果',
  `man_hour` smallint(6) NOT NULL COMMENT '任务工时', 
  `credit` smallint(6) NOT NULL COMMENT '任务积分',
  `difficulty` tinyint(4) NOT NULL COMMENT '任务难度',
  `dateline` int(11) NOT NULL COMMENT '交付日期',
  `create_time` int(11) NOT NULL COMMENT '任务创建时间',
  `update_time` int(11) NOT NULL  COMMENT '任务更新时间，只要表数据被改动就自动更新',
  `status_update_time` int(11) NOT NULL DEFAULT '0' COMMENT '任务状态变更时间',
  `status_counter` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态计数，用在分发统计第几次',
  `winner_id` int(11) NOT NULL DEFAULT '0' COMMENT '抢到任务的uid',
  `status` tinyint(4) NOT NULL COMMENT '任务状态，1、添加 2、开放申请 3、已经被抢走 4、任务完成 5、审核通过',

  `remark`  varchar(250)  NULL  COMMENT '任务完成情况',
  `audit_time` int(11) NULL  COMMENT '任务审核时间',
  `audit_message`  varchar(250)  NULL COMMENT '任务审核意见',

  `dispatch_time` int(11) NULL  COMMENT '任务分发时间',
  `done_time` int(11) NULL  COMMENT '任务完成时间',

  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`,`winner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- 表的结构 `task_application`
--

CREATE TABLE IF NOT EXISTS `task_application` (
  `task_id` int(11) NOT NULL COMMENT '任务id',
  `user_id` int(11) NOT NULL COMMENT '申请用户uid',
  `create_time` int(11) NOT NULL COMMENT '申请时间',
  `status` tinyint(4) NOT NULL COMMENT '本字段目前没用',
  PRIMARY KEY (`task_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务申请记录表';

-- --------------------------------------------------------

--
-- 表的结构 `task_log`
--

CREATE TABLE IF NOT EXISTS `task_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `info` varchar(100) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='任务事件记录'   ;

-- --------------------------------------------------------

--
-- 表的结构 `task_receiver`
--

CREATE TABLE IF NOT EXISTS `task_receiver` (
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`task_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='哪些人可抢哪些任务';



--
-- 表的结构 `task_grade`
--

CREATE TABLE IF NOT EXISTS `task_grade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `credit` smallint(6) NOT NULL COMMENT '评价分数',
  `description` text NOT NULL COMMENT '打分理由',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='打分表';
-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `realname` varchar(50) DEFAULT '',
  `passwd` varchar(32) DEFAULT '',
  `email` varchar(100) DEFAULT '',
  `total_score`  int(11) NULL  COMMENT '总分数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8   ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
