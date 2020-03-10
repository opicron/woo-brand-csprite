<?php
/*
	CSprite - uninstall.php
	Copyright 2014  Creatomatic Ltd.  GPLv2
*/

if(!defined("WP_UNINSTALL_PLUGIN"))
	return false;

delete_option("csprite_generated");
?>
