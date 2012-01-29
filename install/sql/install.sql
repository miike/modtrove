-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 07, 2010 at 04:22 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.6-1+lenny9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `labtrove`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog_bits`
--

CREATE TABLE IF NOT EXISTS `blog_bits` (
  `bit_id` int(11) NOT NULL default '0',
  `bit_rid` int(11) NOT NULL auto_increment,
  `bit_user` varchar(255) NOT NULL,
  `bit_title` varchar(255) NOT NULL,
  `bit_content` text NOT NULL,
  `bit_meta` text NOT NULL,
  `bit_datestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `bit_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `bit_group` varchar(50) NOT NULL,
  `bit_blog` int(11) NOT NULL default '0',
  `bit_md5` varchar(50) NOT NULL,
  `bit_edit` int(11) default '0',
  `bit_edituser` varchar(255) default NULL,
  `bit_editwhy` text,
  `bit_uri` text,
  `bit_cache` text NOT NULL,
  PRIMARY KEY  (`bit_rid`),
  KEY `bit_id` (`bit_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_blogs`
--

CREATE TABLE IF NOT EXISTS `blog_blogs` (
  `blog_id` int(11) NOT NULL auto_increment,
  `blog_name` varchar(127) NOT NULL default '',
  `blog_sname` varchar(64) NOT NULL,
  `blog_desc` text NOT NULL,
  `blog_user` varchar(255) NOT NULL,
  `blog_zone` int(11) NOT NULL default '0',
  `blog_del` int(11) NOT NULL default '0',
  `blog_type` int(11) NOT NULL default '0',
  `blog_redirect` text NOT NULL,
  `blog_infocache` text NOT NULL,
  `blog_about` text NOT NULL,
  PRIMARY KEY  (`blog_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_com`
--

CREATE TABLE IF NOT EXISTS `blog_com` (
  `com_id` int(11) NOT NULL default '0',
  `com_rid` int(11) NOT NULL auto_increment,
  `com_bit` int(11) NOT NULL default '0',
  `com_user` varchar(255) NOT NULL,
  `com_title` varchar(255) NOT NULL,
  `com_cont` text NOT NULL,
  `com_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `com_del` int(11) NOT NULL default '0',
  `com_edit` int(11) NOT NULL default '0',
  `com_edituser` varchar(255) default NULL,
  `com_editwhy` text,
  PRIMARY KEY  (`com_rid`),
  KEY `com_id` (`com_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Table structure for table `blog_data`
--

CREATE TABLE IF NOT EXISTS `blog_data` (
  `data_id` int(11) NOT NULL auto_increment,
  `data_post` int(11) NOT NULL,
  `data_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `data_type` varchar(10) NOT NULL default '',
  `data_data` longtext NOT NULL,
  PRIMARY KEY  (`data_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Table structure for table `blog_sub`
--

CREATE TABLE IF NOT EXISTS `blog_sub` (
  `sub_username` varchar(255) NOT NULL,
  `sub_blog` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_types`
--

CREATE TABLE IF NOT EXISTS `blog_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `type_name` varchar(127) NOT NULL default '',
  `type_desc` text NOT NULL,
  `type_order` int(11) NOT NULL,
  PRIMARY KEY  (`type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_users`
--

CREATE TABLE IF NOT EXISTS `blog_users` (
  `u_name` varchar(64) NOT NULL,
  `u_emailsub` tinyint(4) NOT NULL,
  `u_sortsub` tinyint(4) NOT NULL,
  `u_proflocate` tinyint(4) NOT NULL,
  UNIQUE KEY `u_name` (`u_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_zone`
--

CREATE TABLE IF NOT EXISTS `blog_zone` (
  `zone_id` int(11) NOT NULL default '0',
  `zone_name` varchar(127) NOT NULL default '',
  `zone_type` varchar(127) NOT NULL default '',
  `zone_res` varchar(255) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `mess_id` int(11) NOT NULL auto_increment,
  `mess_subject` text NOT NULL,
  `mess_body` text NOT NULL,
  `mess_html` text NOT NULL,
  `mess_to` varchar(64) NOT NULL,
  `mess_email` tinyint(4) NOT NULL,
  `mess_proflocate` int(11) NOT NULL,
  `mess_key` varchar(32) NOT NULL,
  `mess_pri` tinyint(4) NOT NULL,
  `mess_datetime` datetime NOT NULL,
  PRIMARY KEY  (`mess_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `printers`
--

CREATE TABLE IF NOT EXISTS `printers` (
  `print_id` int(11) NOT NULL auto_increment,
  `print_name` varchar(127) NOT NULL default '',
  `print_desc` varchar(127) NOT NULL default '',
  `print_uri` varchar(255) NOT NULL default '',
  `print_size` varchar(32) NOT NULL default '',
  `print_local` int(11) NOT NULL default '0',
  PRIMARY KEY  (`print_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uri`
--

CREATE TABLE IF NOT EXISTS `uri` (
  `uri_id` bigint(20) NOT NULL auto_increment,
  `uri_url` text NOT NULL,
  PRIMARY KEY  (`uri_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_name` varchar(255) NOT NULL,
  `user_openid` varchar(255) NOT NULL,
  `user_fname` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_image` text NOT NULL,
  `user_type` int(11) NOT NULL default '0',
  `user_enabled` int(11) NOT NULL default '0',
  `user_uid` varchar(32) NOT NULL,
  `user_notes` text character set utf8 NOT NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


INSERT INTO  `blog_zone` (
`zone_id` ,
`zone_name` ,
`zone_type` ,
`zone_res`
)
VALUES (
'1',  'Logged In',  'user',  'any'
);


INSERT INTO `blog_types` (
`type_id` ,
`type_name` ,
`type_desc` ,
`type_order`
)
VALUES (
NULL ,  'Blogs',  '',  '10'
);

-- Under Test - #3429369 --
-- Gives the option to use MySQL full text search --
-- See use_mysql_fulltext_search in default_config.php --
ALTER TABLE blog_bits ADD FULLTEXT (bit_content, bit_title);


-- Under Test - #3438710 --
-- Adds extra provision for storing references to larger files rather than the file itself
ALTER TABLE blog_data ADD mode varchar(10);
ALTER TABLE blog_data ADD filesize int;
ALTER TABLE blog_data ADD filepath varchar(1024);
ALTER TABLE blog_data ADD checksum varchar(60);

