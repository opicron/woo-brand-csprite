<?php
/*
	CSprite - settings.php
	Copyright 2020  Opicon Bv  GPLv2
*/


if(!defined("CSPRITE_VERSION"))
	return false;

function csprite_settings_page () {

	?>

<div class="wrap">
	<h2>CSprite</h2>

	<p>If you have made changes to the csprite folder then click below to rebuild the sprite.</p>
	<p>Image file names need to be either png or jpg.  They must also be unqiue and contain no special characters.</p>

<?php
	if( isset($_POST["sprite_generate"]) ) {

		$csprite_report = csprite_generate();

		?>
	<hr />

	<h3>Generation report</h3>

	<p><?php echo implode("</p>\n<p>", $csprite_report); ?></p>
<?php
	}

	$csprite_generated = get_option("csprite_generated");
	?>

	<p>&nbsp;</p>
	<hr />

	<form method="post">

		<input type="submit" name="sprite_generate" value="Rebuild" />
	</form>

	<p>View the <a target="_blank" href="<?php echo plugin_dir_url(__FILE__); ?>csprite.css?v=<?php echo $csprite_generated; ?>">stylesheet</a>.&nbsp; View the <a target="_blank" href="<?php echo plugin_dir_url(__FILE__); ?>csprite.png?v=<?php echo $csprite_generated; ?>">sprite</a>.</p>

	<p>Last generated on <i><?php echo @date("l dS F Y \a\\t H:i", $csprite_generated); ?></i></p>

<?php
	/*
	$classes = get_option('csprite_classes');
	foreach ($classes as $class)
	{
		echo '<div class="csprite-wrap">';
		echo '<div class="csprite csprite-scale '.$class.'"></div>';
		echo '</div>';
	}
	*/
	$classes = get_option('csprite_classes');
	$columns = 5;
	$count = 0;
	echo '<ul class="brand-thumbnails columns-'.$columns.'">';
	foreach ($classes as $classarr)
	{
		$class = $classarr['class'];
		if ($count % $columns == 0)
			$prefix = 'first';
		else
			$prefix = '';
		echo '<li class="'.$prefix.'">';
			echo '<div class="csprite-wrap">';
			echo '<div class="csprite csprite-scale '.$class.'"></div>';
			echo '</div>';
		echo '</li>';
		$count++;
	}
	echo '</ul>';

?>
</div>
<?php
}


function csprite_menu () {

	add_submenu_page("tools.php", "CSprite", "CSprite", "manage_options", "csprite", "csprite_settings_page");

}


if(is_admin()) {
	add_action("admin_menu", "csprite_menu");
}
?>
