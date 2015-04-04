<?php

/*
  Plugin Name: Mingle Forum
  Plugin URI: http://cartpauj.com/projects/mingle-forum-plugin
  Description: Mingle Forum is growing rapidly in popularity because it is simple, reliable, lightweight and does just enough to keep things interesting. If you like this plugin please consider making a donation at http://cartpauj.com/donate/
  Version: 1.1.0beta
  Author: Cartpauj
  Author URI: http://cartpauj.com/
  Text Domain: mingleforum
  Copyright: 2009-2011, cartpauj

  GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
//test
//Textdomain Hook

define('DEBUG',false);

if(DEBUG){	
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);	
}

$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain('mingleforum', false, $plugin_dir . '/i18n/');

//Setup defines
require("wpf_define.php");

//Load class files
require('bbcode.php');
require('mf-admin/mfadmin.php');

require("wpf-functions.php");
require("wpf.class.php");

//Set $mingleforum global
global $mingleforum;
$mingleforum = new mingleforum();
?>
