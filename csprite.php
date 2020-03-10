<?php
/*
	CSprite - csprite.php
	Copyright 2014  Creatomatic Ltd.  GPLv2

*/

if(!defined("CSPRITE_VERSION"))
	return false;


function csprite_load_image($file) {

	//d($file);
	$content = file_get_contents($file);
	//d($content);

    	if($content === FALSE) {
        	echo "Could not read the file.";
    	} else {
        	$upload_dir = wp_get_upload_dir();
		$tmpfile = trailingslashit( $upload_dir['path']) . basename($file);
		//unlink( $tmpfile );
		file_put_contents( $tmpfile, $content );
		//d($file);
    	}
	$str = strtolower($file);

	if( substr($str, -4) == ".png") {

		return ($image = @imagecreatefrompng($tmpfile)) ? $image : false;

	} else if( substr($str, -4) == ".jpg" || substr($str, -5) == ".jpeg") {

		return ($image = @imagecreatefromjpeg($tmpfile)) ? $image : false;

	} else if( substr($str, -4) == ".gif" ) {

		return ($image = @imagecreatefromgif($tmpfile)) ? $image : false;

	}
	//d('false');

	return false;
}




function csprite_load_images($cwd) {
	global $csprite_image_files;

	$cwdd = opendir($cwd);

	while(($cwdfile = readdir($cwdd)) !== false) {
		// skip cwd and pd
		if($cwdfile == "." || $cwdfile == "..")
			continue;

		$fpath = $cwd . $cwdfile;

		// recursion
		if(is_dir($fpath)) {

			csprite_load_images($fpath . "/");

		} else {

			$image = csprite_load_image($fpath);

			if( $image !== false ) {

				imagealphablending($image, true);

				$csprite_image_files[] = array(
					"image"    => $image,
					"width"    => imagesx($image),
					"height"   => imagesy($image),
					"path"     => $fpath,
				);

			}

		}
	}

	return true;
}
function csprite_load_brand_images() {
	global $csprite_image_files;

        $terms = get_terms( array('taxonomy'=>'product_brand', 'hide_empty'=>1, 'exclude'=>array(122,615,616) ) );
        foreach ( $terms as $term )
        {
                $imageid = get_term_meta( $term->term_id, 'thumbnail_id', true);
                $imagepath = wp_get_attachment_image_src( $imageid, 'large' );
                if ($imagepath)
		{
			//d($imagepath);
			$image = csprite_load_image($imagepath[0]);
			//	d($image);

			if( $image !== false ) {

				imagealphablending($image, true);

				$head = get_headers($imagepath[0], TRUE);
				//d($head);
				$filesize = $head['Content-Length'];

				$csprite_image_files[] = array(
					"image"    => $image,
					"width"    => imagesx($image),
					"height"   => imagesy($image),
					"path"     => $imagepath[0],
					"slug"	   => $term->slug,
					"name"	   => $term->name,
					"size"	   => $filesize,
				);

			}

		}
	}

	return true;
}


function get_csprite_sprite_url ( $csprite_generated = false) {

	if( $csprite_generated === false)
		$csprite_generated = get_option("csprite_generated");

       	//$upload_dir = wp_get_upload_dir();
	$upload_dir = get_site_url();
	//$csprite_image_file_url = trailingslashit( $upload_dir['baseurl'] ) . 'wp-content/uploads/csprite/';
	$csprite_image_file_url = trailingslashit( $upload_dir ) . 'wp-content/uploads/csprite/';

	return $csprite_image_file_url . "csprite.png?v=" . $csprite_generated;
}



function csprite_generate () {

	$csprite_report = array();

	$csprite_generated = time();

	$csprite_css = "
.csprite {
	background: url(" . get_csprite_sprite_url( $csprite_generated ) . ");
	background-repeat: no-repeat;

	display: inline-block;
}
";

       	$upload_dir = wp_get_upload_dir();
	//d($upload_dir);

	$csprite_image_file_base = trailingslashit( $upload_dir['basedir'] ) . 'csprite/';
	//d($csprite_image_file_base);
	//$csprite_image_file_base = get_template_directory() . "/images/csprite/";
	$csprite_image_file      = $csprite_image_file_base . "csprite.png";
	$csprite_css_file        = $csprite_image_file_base . "csprite.css";

	// 0 (no compression) to 9
	$quality = 0;


	if( !function_exists("imagecreatefrompng") ) {

		$csprite_report[] = "CSprite requires the PHP GD libary to work.";

	}
	else if( !is_dir( $csprite_image_file_base ) ) {

		$csprite_report[] = "A \"csprite\" folder is required in your uploads folder.";

	} else {

		$height = 50;
		$margin = 10;

		$csprite_image_files = array();

		global $csprite_image_files;

		//csprite_load_images($csprite_image_file_base);
		csprite_load_brand_images();

		if( sizeof( $csprite_image_files ) == 0 ) {

			$csprite_report[] = "Could not find any images in the theme's \"csprite\" folder.";

		} else {

			$max_width = $total_height = $total_filessize = 0;

			foreach($csprite_image_files as $image) {

				if($image["width"] > $max_width)
					$max_width = $image["width"];

				//$total_height += $image["height"];
				$total_height += $height + $margin;

				// for reporting
				$total_filessize += $image['size'];
			}


			//$sprite = imagecreatetruecolor($max_width, $total_height);
			$sprite = imagecreatetruecolor($max_width, $total_height);

			imagealphablending($sprite, true);

			$transparent = imagecolorallocatealpha($sprite, 0, 0, 0, 127);
			imagefill($sprite, 0, 0, $transparent);

			$classes = array();
			$top = 0;
			foreach($csprite_image_files as $image) {

				// file name to suitable css class
				$class = explode(".", basename($image["path"]));
				$class = strtolower($class[0]);
				$class = str_replace(" ", "_", $class);

				//imagecopy($sprite, $image["image"], 0, $top, 0, 0, $image["width"], $image["height"]);
// imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h ) : bool
				$ratio = $image["width"]/$image["height"];
				imagecopyresampled($sprite, $image["image"], 0, $top, 0, 0, $ratio * $height, $height, $image["width"], $image["height"]);
				
				//$csprite_css .= ".csprite-" . $class . " { background-position: 0 -" . $top . "px; width: " . $image["width"] . "px; height: " . $image["height"] . "px; }\n";
				$csprite_css .= ".csprite-" . $class . " { background-position: 0 -" . $top . "px; width: " . $ratio * $height . "px; height: " . ($height+1) . "px; }\n";
//transform: scale(0.3); transform-origin: top left; 

				//save classname
				$tmp['class'] = "csprite-".$class; 
				$tmp['slug'] = $image['slug'];
				$tmp['name'] = $image['name'];
				$classes[] = $tmp;

				//$top += $image["height"];
				$top += $height + $margin;
			}
			$csprite_css .= ".csprite-scale { transform: scale(0.3); transform-origin: top left; }";
			$csprite_css .= ".csprite-wrap { height:22px; }";


			imagesavealpha($sprite, true);

			//imagepng($sprite, NULL, $quality);
			imagepng($sprite, $csprite_image_file, $quality);

			imagedestroy($sprite);

			file_put_contents($csprite_css_file, $csprite_css);

			update_option( 'csprite_classes', $classes );
			update_option( "csprite_generated", $csprite_generated);

			//optimize png
			$output = shell_exec('optipng '.$csprite_image_file);
			//$csprite_report[] = "<pre>$output</pre>";

			$csprite_report[] = "CSprite combined " . count($csprite_image_files) . " images totalling " . sprintf("%.01f", $total_filessize / 1024) . " KB, into one single image of " . sprintf("%.01f", filesize($csprite_image_file) / 1024) . " KB! Reduced file size and less web requests, you're welcome :)";
		}
	}

	return $csprite_report;
}


/*
function csprite_stylesheet () {

	$csprite_generated = get_option("csprite_generated");

       	$upload_dir = wp_get_upload_dir();
	$csprite_image_file_url = trailingslashit( $upload_dir['baseurl'] ) . 'csprite/';

	wp_enqueue_style ("csprite", $csprite_image_file_url . "csprite.css", array(), $csprite_generated);
}
*/
//add_action("wp_head", "csprite_stylesheet");

?>
