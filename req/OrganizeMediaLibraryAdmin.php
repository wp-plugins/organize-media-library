<?php
/**
 * Organize Media Library
 * 
 * @package    Organize Media Library
 * @subpackage OrganizeMediaLibraryAdmin Main & Management screen
/*  Copyright (c) 2013- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class OrganizeMediaLibraryAdmin {

	public $postcount;

	/* ==================================================
	 * Add a "Settings" link to the plugins page
	 * @since	1.0
	 */
	function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty($this_plugin) ) {
			$this_plugin = ORGANIZEMEDIALIBRARY_PLUGIN_BASE_FILE;
		}
		if ( $file == $this_plugin ) {
			$links[] = '<a href="'.admin_url('tools.php?page=organizemedialibrary').'">'.__( 'Settings').'</a>';
		}
			return $links;
	}

	/* ==================================================
	 * Settings page
	 * @since	1.0
	 */
	function add_pages() {
		add_management_page('Organize Media Library', 'Organize Media Library', 'manage_options', 'organizemedialibrary', array($this, 'manage_page'));
	}

	/* ==================================================
	 * Add Css and Script
	 * @since	2.23
	 */
	function load_custom_wp_admin_style() {
		wp_enqueue_style( 'jquery-responsiveTabs', ORGANIZEMEDIALIBRARY_PLUGIN_URL.'/css/responsive-tabs.css' );
		wp_enqueue_style( 'jquery-responsiveTabs-style', ORGANIZEMEDIALIBRARY_PLUGIN_URL.'/css/style.css' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-responsiveTabs', ORGANIZEMEDIALIBRARY_PLUGIN_URL.'/js/jquery.responsiveTabs.min.js' );

	}

	/* ==================================================
	 * Add Script on footer
	 * @since	2.24
	 */
	function load_custom_wp_admin_style2() {
		echo $this->add_js();
	}

	/* ==================================================
	 * Main
	 */
	function manage_page() {

		$def_max_execution_time = ini_get('max_execution_time');

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		if ( empty($_POST['organizemedialibrary-tabs']) ) {
			$tabs = 1;
		} else {
			$tabs = intval($_POST['organizemedialibrary-tabs']);
		}

		$this->options_updated($tabs);

		include_once ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR.'/inc/OrganizeMediaLibrary.php';
		$organizemedialibrary = new OrganizeMediaLibrary();
		$organizemedialibrary_settings = get_option('organizemedialibrary_settings');
		$max_execution_time = intval($organizemedialibrary_settings['max_execution_time']);

		set_time_limit($max_execution_time);

		$adddb = FALSE;
		if (!empty($_POST['adddb'])){
			$adddb = $_POST['adddb'];
		}

		$scriptname = admin_url('tools.php?page=organizemedialibrary');

		?>
		<div class="wrap">

		<h2>Organize Media Library</h2>

			<div id="organizemedialibrary-tabs">
				<ul>
				<li><a href="#organizemedialibrary-tabs-1"><?php _e('Search & Rebuild & Organize', 'organizemedialibrary'); ?></a></li>
				<li><a href="#organizemedialibrary-tabs-2"><?php _e('Settings'); ?></a></li>
				<li><a href="#organizemedialibrary-tabs-3"><?php _e('Donate to this plugin &#187;'); ?></a></li>
				</ul>
				<div id="organizemedialibrary-tabs-1">

		<h3><?php _e('Thumbnails rebuild and organize uploads into month- and year-based folders.', 'organizemedialibrary'); ?></h3>

		<?php

		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1
			);
		$attachments = get_posts($args);

		$count = 0;
		$this->postcount = 0;

		if ( $adddb <> 'TRUE' ) {
			foreach ( $attachments as $attachment ){

				++$count;
				if ( $count == 1 ) {

					?>
					<form method="post" action="<?php echo $scriptname; ?>">
					<div class="submit">
						<input type="hidden" name="organizemedialibrary-tabs" value="1" />
						<input type="hidden" name="adddb" value="TRUE">
						<input type="submit" value="<?php _e('Update Media'); ?>" />
					</div>
					<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
					<input type="checkbox" id="group_organize-media-library" class="organizemedialibrary-checkAll"><?php _e('Select all'); ?>
					</div>
					<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
					<?php _e('Select'); ?> & <?php _e('Thumbnail'); ?> & <?php _e('Metadata'); ?>
					</div>
					<?php
				}

				$attach_id = $attachment->ID;

				$title = $attachment->post_title;
				$url_attach = wp_get_attachment_url( $attach_id );
				$exts = explode('.', $url_attach);
				$ext = end($exts);

				list($imagethumburls, $mimetype, $length, $thumbnail_img_url, $stamptime, $file_size, $filetype) = $organizemedialibrary->getmeta($ext, $attach_id);

				$input_html = NULL;
				$input_html .= '<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">';
				$input_html .= '<input name="re_id_attaches['.$this->postcount.'][id]" type="checkbox" value="'.$attach_id.'" class="group_organize-media-library" style="float: left; margin: 5px;">';
				$input_html .= '<img width="40" height="40" src="'.$thumbnail_img_url.'">';
				$input_html .= '<div>'.__('Title').': '.$title.'</div>';
				$input_html .= '<div>'.__('Permalink:').' <a href="'.get_attachment_link($attach_id).'" target="_blank" style="text-decoration: none; word-break: break-all;">'.get_attachment_link($attach_id).'</a></div>';
				$input_html .= '<div>URL: <a href="'.$url_attach.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$url_attach.'</a></div>';
				$url_attachs = explode('/', $url_attach);
				$input_html .= '<div>'.__('File name:').' '.end($url_attachs).'</div>';

				if ( wp_ext2type($ext) === 'image' ) {
					$input_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
					$input_html .= '<div>'.__('Images').': ';
					foreach ( $imagethumburls as $thumbsize => $imagethumburl ) {
						$input_html .= '[<a href="'.$imagethumburl.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$thumbsize.'</a>]';
					}
					$input_html .= '</div>';
				} else if ( wp_ext2type($ext) === 'video' ) {
					$input_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
					$input_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
					$input_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
					$input_html .= '<div>'.__('Length:').' '.$length.'</div>';
				} else if ( wp_ext2type($ext) === 'audio' ) {
					$input_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
					$input_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
					$input_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
					$input_html .= '<div>'.__('Length:').' '.$length.'</div>';
				} else {
					$input_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
					$input_html .= '<div>'.__('File type:').' '.$filetype.'</div>';
					$input_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
				}

				$input_html .= '</div>';

				echo $input_html;

				++$this->postcount;

			}

			?>
			<form method="post" action="<?php echo $scriptname; ?>">
			<div class="submit">
				<input type="hidden" name="organizemedialibrary-tabs" value="1" />
				<input type="hidden" name="adddb" value="TRUE">
				<input type="submit" value="<?php _e('Update Media'); ?>" />
			</div>
			</form>
			<?php
		}

		if ( $adddb === 'TRUE' ) {
			$re_id_attaches = $_POST["re_id_attaches"];
			if (!empty($re_id_attaches)) {
				?>
				<form method="post" action="<?php echo $scriptname; ?>">
					<div class="submit">
						<input type="hidden" name="organizemedialibrary-tabs" value="1" />
						<input type="submit" value="<?php _e('Back'); ?>" />
					</div>
				</form>
				<?php
				echo'<div class="error"><ul><li>'.__('Please try again pressing Back button, if the processing is stopped on the way.', 'organizemedialibrary').'</li></ul></div>';

				$yearmonth_folders = get_option('uploads_use_yearmonth_folders');

				foreach ( $re_id_attaches as $postkey1 => $postval1 ){
					foreach ( $postval1 as $postkey2 => $postval2 ){
						if ( $postkey2 === 'id' ) {
							$re_id_attache = intval($postval1[$postkey2]);

							// Rebuild
							list($ext, $new_attach_title, $new_url_attach) = $organizemedialibrary->regist($re_id_attache, $yearmonth_folders);

							list($imagethumburls, $mimetype, $length, $thumbnail_img_url, $stamptime, $file_size, $filetype) = $organizemedialibrary->getmeta($ext, $re_id_attache);

							$output_html = NULL;
							$output_html .= '<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">';
							$output_html .= '<img width="40" height="40" src="'.$thumbnail_img_url.'">';
							$output_html .= '<div>'.__('Title').': '.$new_attach_title.'</div>';
							$output_html .= '<div>'.__('Permalink:').' <a href="'.get_attachment_link($re_id_attache).'" target="_blank" style="text-decoration: none; word-break: break-all;">'.get_attachment_link($re_id_attache).'</a></div>';
							$output_html .= '<div>URL: <a href="'.$new_url_attach.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$new_url_attach.'</a></div>';
							$re_id_attaches = explode('/', $new_url_attach);
							$output_html .= '<div>'.__('File name:').' '.end($re_id_attaches).'</div>';

							if ( wp_ext2type($ext) === 'image' ) {
								$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
								$output_html .= '<div>'.__('Images').': ';
								foreach ( $imagethumburls as $thumbsize => $imagethumburl ) {
									$output_html .= '[<a href="'.$imagethumburl.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$thumbsize.'</a>]';
								}
								$output_html .= '</div>';
							} else if ( wp_ext2type($ext) === 'video' ) {
								$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
								$output_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
								$output_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
								$output_html .= '<div>'.__('Length:').' '.$length.'</div>';
							} else if ( wp_ext2type($ext) === 'audio' ) {
								$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
								$output_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
								$output_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
								$output_html .= '<div>'.__('Length:').' '.$length.'</div>';
							} else {
								$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
								$output_html .= '<div>'.__('File type:').' '.$filetype.'</div>';
								$output_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
							}

							$output_html .= '</div>';

							echo $output_html;

						}
					}
				}
				echo '<div class="updated"><ul><li>'.__('The following media has been rebuild organize.', 'organizemedialibrary').'</li></ul></div>';
			}

			?>
			<div class="submit">
			<form method="post" style="float: left;" action="<?php echo $scriptname; ?>">
				<input type="hidden" name="organizemedialibrary-tabs" value="1" />
				<input type="submit" value="<?php _e('Back'); ?>" />
			</form>
			<form method="post" action="<?php echo admin_url( 'upload.php'); ?>">
				<input type="submit" value="<?php _e('Media Library'); ?>" />
			</form>
			</div>
			<div style="clear:both"></div>
			<?php
		}

		?>
		</div>

		<div id="organizemedialibrary-tabs-2">
		<div class="wrap">
		<form method="post" action="<?php echo $scriptname; ?>">
			<h3><?php _e('Settings'); ?></h3>
			<div style="display:block;padding:5px 0">
			<input type="checkbox" name="move_yearmonth_folders" value="1" <?php checked('1', get_option('uploads_use_yearmonth_folders')); ?> />
			<?php _e('Organize my uploads into month- and year-based folders'); ?>
			</div>
			<div style="display:block;padding:5px 0">
				<?php
					$max_execution_time_text = __('Set the number of seconds a script is allowed to run.', 'organizemedialibrary').'('.__('The max_execution_time value defined in the php.ini.', 'organizemedialibrary').'[<font color="red">'.$def_max_execution_time.'</font>]'.')';
					echo $max_execution_time_text;
					$target_organizemedialibrary_max_execution_time = $organizemedialibrary_settings['max_execution_time'];
				?>
				<select id="organizemedialibrary_max_execution_time" name="organizemedialibrary_max_execution_time">
					<option <?php if ('30' == $target_organizemedialibrary_max_execution_time)echo 'selected="selected"'; ?>>30</option>
					<option <?php if ('60' == $target_organizemedialibrary_max_execution_time)echo 'selected="selected"'; ?>>60</option>
					<option <?php if ('120' == $target_organizemedialibrary_max_execution_time)echo 'selected="selected"'; ?>>120</option>
					<option <?php if ('180' == $target_organizemedialibrary_max_execution_time)echo 'selected="selected"'; ?>>180</option>
					<option <?php if ('240' == $target_organizemedialibrary_max_execution_time)echo 'selected="selected"'; ?>>240</option>
					<option <?php if ('300' == $target_organizemedialibrary_max_execution_time)echo 'selected="selected"'; ?>>300</option>
					<option <?php if ('600' == $target_organizemedialibrary_max_execution_time)echo 'selected="selected"'; ?>>600</option>
				</select>
			</div>
			<div class="submit">
				<input type="hidden" name="organizemedialibrary-tabs" value="2" />
				<input type="submit" name="Submit" value="<?php _e('Save Changes'); ?>" />
			</div>
		</form>
		</div>
		</div>

		<div id="organizemedialibrary-tabs-3">
		<div class="wrap">
			<h3><?php _e('I need a donation. This is because, I want to continue the development and support of plugins.', 'organizemedialibrary'); ?></h3>
			<div align="right">Katsushi Kawamori</div>
			<h3 style="float: left;"><?php _e('Donate to this plugin &#187;'); ?></h3>
<a href='https://pledgie.com/campaigns/28307' target="_blank"><img alt='Click here to lend your support to: Various Plugins for WordPress and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/28307.png?skin_name=chrome' border='0' ></a>
		</div>
		</div>

		</div>
		</div>
		<?php

	}

	/* ==================================================
	 * Update	wp_options table.
	 * @param	string	$tabs
	 * @since	1.0
	 */
	function options_updated($tabs){

		$organizemedialibrary_settings = get_option('organizemedialibrary_settings');

		switch ($tabs) {
			case 1:
				$organizemedialibrary_tbl = array(
									'max_execution_time' => $organizemedialibrary_settings['max_execution_time']
									);
				update_option( 'organizemedialibrary_settings', $organizemedialibrary_tbl );
				break;
			case 2:
				if ( !empty($_POST['organizemedialibrary_max_execution_time']) ) {
					$organizemedialibrary_tbl = array(
										'max_execution_time' => $_POST['organizemedialibrary_max_execution_time']
										);
					update_option( 'organizemedialibrary_settings', $organizemedialibrary_tbl );
					if ( !empty($_POST['move_yearmonth_folders']) ) {
						update_option( 'uploads_use_yearmonth_folders', $_POST['move_yearmonth_folders'] );
					} else {
						update_option( 'uploads_use_yearmonth_folders', '0' );
					}
					echo '<div class="updated"><ul><li>'.__('Settings').' --> '.__('Changes saved.').'</li></ul></div>';
				}
				break;
		}

	}

	/* ==================================================
	 * Add js
	 * @since	1.0
	 */
	function add_js(){

// JS
$organizemedialibrary_add_js = <<<ORGANIZEMEDIALIBRARY

<!-- BEGIN: Organize Media Library -->
<script type="text/javascript">
jQuery('#organizemedialibrary-tabs').responsiveTabs({
  startCollapsed: 'accordion'
});
</script>
<script type="text/javascript">
jQuery(function(){
  jQuery('.organizemedialibrary-checkAll').on('change', function() {
    jQuery('.' + this.id).prop('checked', this.checked);
  });
});
</script>
<!-- END: Organize Media Library -->

ORGANIZEMEDIALIBRARY;

		return $organizemedialibrary_add_js;

	}

	function modify_attachment_link($markup) {
	    return preg_replace('/^<a([^>]+)>(.*)$/', '<a\\1 target="_blank">\\2', $markup);
	}

}

?>