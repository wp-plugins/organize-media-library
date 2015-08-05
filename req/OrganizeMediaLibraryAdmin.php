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
			$links[] = '<a href="'.admin_url('admin.php?page=organizemedialibrary').'">Organize Media Library</a>';
		}
			return $links;
	}

	/* ==================================================
	 * Settings page
	 * @since	1.0
	 */
	function add_pages() {
		add_menu_page(
				'Organize Media Library',
				'Organize Media Library',
				'manage_options',
				'organizemedialibrary',
				array($this, 'manage_page')
		);
		add_submenu_page(
				'organizemedialibrary',
				__('Settings'),
				__('Settings'),
				'manage_options',
				'organizemedialibrary-settings',
				array($this, 'settings_page')
		);
		add_submenu_page(
				'organizemedialibrary',
				__('Search & Rebuild & Organize', 'organizemedialibrary'),
				__('Search & Rebuild & Organize', 'organizemedialibrary'),
				'manage_options',
				'organizemedialibrary-search-register',
				array($this, 'search_register_page')
		);
		add_submenu_page(
				'organizemedialibrary',
				__('Move Uploads folder & Rebuild & Organize', 'organizemedialibrary'),
				__('Move Uploads folder & Rebuild & Organize', 'organizemedialibrary'),
				'manage_options',
				'organizemedialibrary-move-uploads-folder-register',
				array($this, 'move_uploads_folder_register_page')
		);
	}

	/* ==================================================
	 * Add Css and Script
	 * @since	2.23
	 */
	function load_custom_wp_admin_style() {
		wp_enqueue_style( 'organizemedialibrary',  ORGANIZEMEDIALIBRARY_PLUGIN_URL.'/css/organizemedialibrary.css' );
		wp_enqueue_script( 'jquery' );
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

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$plugin_datas = get_file_data( ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR.'/organizemedialibrary.php', array('version' => 'Version') );
		$plugin_version = __('Version:').' '.$plugin_datas['version'];

		?>

		<div class="wrap">

		<h2 style="float: left;">Organize Media Library</h2>
		<div style="display: block; padding: 10px 10px;">
			<form method="post" style="float: left; margin-right: 0.2em;" action="<?php echo admin_url('admin.php?page=organizemedialibrary-settings'); ?>">
				<input type="submit" class="button" value="<?php _e('Settings'); ?>" />
			</form>
			<form method="post" style="float: left; margin-right: 0.2em;" action="<?php echo admin_url('admin.php?page=organizemedialibrary-search-register'); ?>" />
				<input type="submit" class="button" value="<?php _e('Search & Rebuild & Organize', 'organizemedialibrary'); ?>" />
			</form>
			<form method="post" action="<?php echo admin_url('admin.php?page=organizemedialibrary-move-uploads-folder-register'); ?>" />
				<input type="submit" class="button" value="<?php _e('Move Uploads folder & Rebuild & Organize', 'organizemedialibrary'); ?>" />
			</form>
		</div>
		<div style="clear: both;"></div>

		<h3><?php _e('Thumbnails rebuild and organize uploads into month- and year-based folders or specified folders. Move the upload folder, it is possible to move all uploaded files. URL in the content, replace with the new URL.', 'organizemedialibrary'); ?></h3>
		<h4 style="margin: 5px; padding: 5px;">
		<?php echo $plugin_version; ?> |
		<a style="text-decoration: none;" href="https://wordpress.org/support/plugin/organize-media-library" target="_blank"><?php _e('Support Forums') ?></a> |
		<a style="text-decoration: none;" href="https://wordpress.org/support/view/plugin-reviews/organize-media-library" target="_blank"><?php _e('Reviews', 'organizemedialibrary') ?></a>
		</h4>

		<div style="width: 250px; height: 170px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php _e('Please make a donation if you like my work or would like to further the development of this plugin.', 'organizemedialibrary'); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
<a style="margin: 5px; padding: 5px;" href='https://pledgie.com/campaigns/28307' target="_blank"><img alt='Click here to lend your support to: Various Plugins for WordPress and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/28307.png?skin_name=chrome' border='0' ></a>
		</div>

		</div>
		<?php
	}

	/* ==================================================
	 * Sub Menu
	 */
	function settings_page() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$submenu = 1;
		$this->options_updated($submenu);

		include_once ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR.'/inc/OrganizeMediaLibrary.php';
		$organizemedialibrary = new OrganizeMediaLibrary();
		$organizemedialibrary_settings = get_option('organizemedialibrary_settings');
		$targetdir = $organizemedialibrary_settings['targetdir'];
		$def_max_execution_time = ini_get('max_execution_time');
		$scriptname = admin_url('admin.php?page=organizemedialibrary-settings');

		?>
		<div class="wrap">

		<h2>Organize Media Library <?php _e('Settings'); ?>
			<form method="post" style="float: right; margin-right: 0.2em;" action="<?php echo admin_url('admin.php?page=organizemedialibrary-move-uploads-folder-register'); ?>" />
				<input type="submit" class="button" value="<?php _e('Move Uploads folder & Rebuild & Organize', 'organizemedialibrary'); ?>" />
			</form>
			<form method="post" style="float: right; margin-right: 0.2em;" action="<?php echo admin_url('admin.php?page=organizemedialibrary-search-register'); ?>" />
				<input type="submit" class="button" value="<?php _e('Search & Rebuild & Organize', 'organizemedialibrary'); ?>" />
			</form>
		</h2>
		<div style="clear: both;"></div>

		<form method="post" action="<?php echo $scriptname; ?>">
			<div class="item-organizemedialibrary-settings">
				<div style="display: block; padding:5px 5px">
					<h3><?php _e('Execution time', 'organizemedialibrary'); ?></h3>
					<?php
						$max_execution_time_text = __('The number of seconds a script is allowed to run.', 'organizemedialibrary').'('.__('The max_execution_time value defined in the php.ini.', 'organizemedialibrary').'[<font color="red">'.$def_max_execution_time.'</font>]'.')';
						?>
						<div style="float: left;"><?php echo $max_execution_time_text; ?>:<input type="text" name="organizemedialibrary_max_execution_time" value="<?php echo $organizemedialibrary_settings['max_execution_time']; ?>" size="3" /></div>
				</div>
				<div style="clear: both;"></div>
			</div>
			<div class="item-organizemedialibrary-settings">
				<h3><?php _e('Folder Settings', 'organizemedialibrary'); ?></h3>
				<div style="display: block; padding:5px 5px;">
				<input type="radio" name="organizemedialibrary_folderset" value="rebuildonly" <?php if ($organizemedialibrary_settings['folderset'] === 'rebuildonly') echo 'checked'; ?>>
				<?php _e('Only rebuild of metadata.', 'organizemedialibrary'); ?>
				</div>
				<div style="display: block; padding:5px 5px;">
				<input type="radio" name="organizemedialibrary_folderset" value="movefolder" <?php if ($organizemedialibrary_settings['folderset'] === 'movefolder') echo 'checked'; ?>>
				<?php _e('Organize files into a folder.', 'organizemedialibrary'); ?>
				</div>
				<div style="display: block;padding:5px 20px;">
				<input type="checkbox" name="move_yearmonth_folders" value="1" <?php checked('1', get_option('uploads_use_yearmonth_folders')); ?> />
				<?php _e('Organize my uploads into month- and year-based folders'); ?>
				</div>
				<?php
				$disable_form = NULL;
				if ( get_option('uploads_use_yearmonth_folders') === '1' ){
					$disable_form = 'disabled="disabled"';
				}
				?>
				<div style="display: block; padding:5px 20px;">
					<?php _e('Select a folder to organize', 'organizemedialibrary'); ?>
					<select <?php echo $disable_form; ?> name="targetdir" style="width: 250px;">
					<?php echo $organizemedialibrary->dir_selectbox($targetdir); ?>
					</select>
				</div>
				<div style="display: block; padding:5px 40px;">
					<?php _e('Make folder', 'organizemedialibrary'); ?><input <?php echo $disable_form; ?> type="text" name="newdir">
				</div>
				<div style="clear: both;"></div>
			</div>
			<div class="submit">
				<input type="submit" class="button" value="<?php _e('Save Changes'); ?>" />
			</div>
		</form>
		</div>
		<?php
	}

	/* ==================================================
	 * Sub Menu
	 */
	function search_register_page() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$def_max_execution_time = ini_get('max_execution_time');

		$submenu = 2;
		$this->options_updated($submenu);

		include_once ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR.'/inc/OrganizeMediaLibrary.php';
		$organizemedialibrary = new OrganizeMediaLibrary();
		$organizemedialibrary_settings = get_option('organizemedialibrary_settings');
		$pagemax = $organizemedialibrary_settings['pagemax'];
		$folderset = $organizemedialibrary_settings['folderset'];
		$target_folder = $organizemedialibrary_settings['targetdir'];
		$max_execution_time = $organizemedialibrary_settings['max_execution_time'];

		set_time_limit($max_execution_time);

		$adddb = FALSE;
		if (!empty($_POST['adddb'])){
			$adddb = $_POST['adddb'];
		}

		$mimefilter = NULL;
		if( !empty($_GET['mime']) ) {
			$mimefilter = $_GET['mime'];
		}
		if( !empty($_POST['mime']) ) {
			$mimefilter = $_POST['mime'];
		}

		$searchdir = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH;
		if (!empty($_POST['searchdir'])){
			$searchdir = urldecode($_POST['searchdir']);
		}

		if( get_option('WPLANG') === 'ja' ) {
			mb_language('Japanese');
		} else if( get_option('WPLANG') === 'en' ) {
			mb_language('English');
		} else {
			mb_language('uni');
		}

		if (DIRECTORY_SEPARATOR === '\\' && mb_language() === 'Japanese') {
			$searchdir = mb_convert_encoding($searchdir, "sjis-win", "auto");
		} else {
			$searchdir = mb_convert_encoding($searchdir, "UTF-8", "auto");
		}

		$scriptname = admin_url('admin.php?page=organizemedialibrary-search-register');

		?>
		<div class="wrap">

		<h2>Organize Media Library <?php _e('Search & Rebuild & Organize', 'organizemedialibrary'); ?>
			<form method="post" style="float: right; margin-right: 0.2em;" action="<?php echo admin_url('admin.php?page=organizemedialibrary-move-uploads-folder-register'); ?>" />
				<input type="submit" class="button" value="<?php _e('Move Uploads folder & Rebuild & Organize', 'organizemedialibrary'); ?>" />
			</form>
			<form method="post" style="float: right; margin-right: 0.2em;" action="<?php echo admin_url('admin.php?page=organizemedialibrary-settings'); ?>">
				<input type="submit" class="button" value="<?php _e('Settings'); ?>" />
			</form>
		</h2>
		<div style="clear: both;"></div>

		<div id="organizemedialibrary-loading"><img src="<?php echo ORGANIZEMEDIALIBRARY_PLUGIN_URL; ?>/css/loading.gif"></div>
		<div id="organizemedialibrary-loading-container">

		<?php

		$searchdir_db = str_replace('../', '', $searchdir);
		global $wpdb;
		$postmimetype = NULL;
		if ( !empty($mimefilter) ) {
			$postmimetype = "and post_mime_type = '".$mimefilter."'";
		}
		$attachments = $wpdb->get_results("
						SELECT	ID, post_title
						FROM	$wpdb->posts
						WHERE	post_type = 'attachment'
								and guid LIKE '%%$searchdir_db%%'
								$postmimetype
								ORDER BY post_date DESC
						");

		$pageallcount = 0;
		// pagenation
		foreach ( $attachments as $attachment ) {
			++$pageallcount;
		}
		if (!empty($_GET['p'])){
			$page = $_GET['p'];
		} else if (!empty($_POST['p'])){
			$page = $_POST['p'];
		} else {
			$page = 1;
		}
		$count = 0;
		$pagebegin = (($page - 1) * $pagemax) + 1;
		$pageend = $page * $pagemax;
		$pagelast = ceil($pageallcount / $pagemax);

		$count = 0;
		$this->postcount = 0;

		if ( $adddb <> 'TRUE' ) {
			$wordpress_path = wp_normalize_path(ABSPATH);
			?>
			<form method="post" action="<?php echo $scriptname; ?>">
				<input type="hidden" name="adddb" value="FALSE">
				<div style="float:left;"><?php _e('Number of items per page:'); ?><input type="text" name="organizemedialibrary_pagemax" value="<?php echo $pagemax; ?>" size="3" /></div>
				<input type="submit" class="button" name="ShowToPage" value="<?php _e('Save') ?>" />
				<div style="clear: both;"></div>
				<div style="font-size: small; font-weight: bold;"><code><?php echo $wordpress_path; ?></code></div>
				<div>
					<select name="searchdir" style="width: 250px;">
					<?php echo $organizemedialibrary->dir_selectbox($searchdir); ?>
					</select>
					<select name="mime" style="width: 180px;">
					<option value=""><?php echo esc_attr( __( 'All Mime types', 'organizemedialibrary' ) ); ?></option>
					<?php
					foreach ( wp_get_mime_types() as $exts => $mime ) {
						?>
						<option value="<?php echo esc_attr($mime); ?>"<?php if ($mimefilter === $mime) echo ' selected';?>><?php echo esc_attr($mime); ?></option>
						<?php
					}
					?>
					</select>
					<input type="submit" class="button" value="<?php _e('Filter'); ?>">
				</div>
			</form>
			<div style="clear: both;"></div>
			<?php
			if ( $pageallcount > 0 ) {
				if ( $pagelast > 1 ) {
					$this->pagenation($page, $pagebegin, $pageend, $pagelast, $scriptname, $mimefilter);
				}
				?>
				<form method="post" action="<?php echo $scriptname; ?>">
					<input type="hidden" name="adddb" value="TRUE">
					<input type="hidden" name="p" value="<?php echo $page; ?>" />
					<input type="hidden" name="mime" value="<?php echo $mimefilter; ?>" />
					<div style="padding-top: 5px; padding-bottom: 5px;">
					<input type="submit" class="button-primary button-large" value="<?php _e('Update Media'); ?>" />
					</div>
					<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
					<input type="checkbox" id="group_organize-media-library" class="organizemedialibrary-checkAll"><?php _e('Select all'); ?>
					</div>
					<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
					<?php _e('Select'); ?> & <?php _e('Thumbnail'); ?> & <?php _e('Metadata'); ?>
					</div>
				<?php
				foreach ( $attachments as $attachment ){
					++$count;
					if ( $pagebegin <= $count && $count <= $pageend ) {
						$attach_id = $attachment->ID;

						$title = $attachment->post_title;
						$url_attach = wp_get_attachment_url( $attach_id );
						$exts = explode('.', $url_attach);
						$ext = end($exts);

						$metadata = NULL;
						list($imagethumburls, $mimetype, $length, $thumbnail_img_url, $stamptime, $file_size) = $organizemedialibrary->getmeta($ext, $attach_id, $metadata, ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_URL);

						$input_html = NULL;
						$input_html .= '<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">';
						$input_html .= '<input name="re_id_attaches['.$this->postcount.'][id]" type="checkbox" value="'.$attach_id.'" class="group_organize-media-library" style="float: left; margin: 5px;">';
						$input_html .= '<img width="40" height="40" src="'.$thumbnail_img_url.'" style="float: left; margin: 5px;">';
						$input_html .= '<div style="overflow: hidden;">';
						$input_html .= '<div>'.__('Title').': '.$title.'</div>';
						$input_html .= '<div>'.__('Permalink:').' <a href="'.get_attachment_link($attach_id).'" target="_blank" style="text-decoration: none; word-break: break-all;">'.get_attachment_link($attach_id).'</a></div>';
						$input_html .= '<div>URL: <a href="'.$url_attach.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$url_attach.'</a></div>';
						$url_attachs = explode('/', $url_attach);
						$input_html .= '<div>'.__('File name:').' '.end($url_attachs).'</div>';

						$input_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
						if ( wp_ext2type($ext) === 'image' ) {
							$input_html .= '<div>'.__('Images').': ';
							foreach ( $imagethumburls as $thumbsize => $imagethumburl ) {
								$input_html .= '[<a href="'.$imagethumburl.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$thumbsize.'</a>]';
							}
							$input_html .= '</div>';
						} else {
							$input_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
							$input_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
							if ( wp_ext2type($ext) === 'video' || wp_ext2type($ext) === 'audio' ) {
								$input_html .= '<div>'.__('Length:').' '.$length.'</div>';
							}
						}

						$input_html .= "</div></div>\n";

						echo $input_html;

						++$this->postcount;
					}
				}
				?>
					<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">
					<?php _e('Select'); ?> & <?php _e('Thumbnail'); ?> & <?php _e('Metadata'); ?>
					</div>
					<div style="padding-top: 5px; padding-bottom: 5px;">
					<input type="checkbox" id="group_organize-media-library" class="organizemedialibrary-checkAll"><?php _e('Select all'); ?>
					</div>
					<div style="padding-top: 5px; padding-bottom: 5px;">
					<input type="submit" class="button-primary button-large" value="<?php _e('Update Media'); ?>" />
					</div>
				</form>
				<?php
				if ( $pagelast > 1 ) {
					$this->pagenation($page, $pagebegin, $pageend, $pagelast, $scriptname, $mimefilter);
				}
			}
		} else { // $adddb === 'TRUE'
			$re_id_attaches = $_POST["re_id_attaches"];
			if (!empty($re_id_attaches)) {
				?>
				<div class="submit">
				<form method="post" style="float: left;" action="<?php echo $scriptname; ?>">
					<input type="hidden" name="p" value="<?php echo $page; ?>" />
					<input type="hidden" name="mime" value="<?php echo $mimefilter; ?>" />
					<input type="submit" class="button" value="<?php _e('Search'); ?>" />
				</form>
				<form method="post" action="<?php echo admin_url( 'upload.php'); ?>">
					<input type="submit" class="button" value="<?php _e('Media Library'); ?>" />
				</form>
				</div>
				<div style="clear: both;"></div>
				<?php

				$yearmonth_folders = get_option('uploads_use_yearmonth_folders');

				foreach ( $re_id_attaches as $postkey1 => $postval1 ){
					foreach ( $postval1 as $postkey2 => $postval2 ){
						if ( $postkey2 === 'id' ) {
							$re_id_attache = intval($postval1[$postkey2]);

							// Rebuild
							list($ext, $new_attach_title, $new_url_attach, $url_replace_contents, $metadata) = $organizemedialibrary->regist($re_id_attache, $yearmonth_folders, $folderset, $target_folder);

							list($imagethumburls, $mimetype, $length, $thumbnail_img_url, $stamptime, $file_size) = $organizemedialibrary->getmeta($ext, $re_id_attache, $metadata, ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_URL);

							$output_html = NULL;
							$output_html .= '<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">';
							$output_html .= '<img width="40" height="40" src="'.$thumbnail_img_url.'" style="float: left; margin: 5px;">';
							$output_html .= '<div style="overflow: hidden;">';
							$output_html .= '<div>'.__('Title').': '.$new_attach_title.'</div>';
							$output_html .= '<div>'.__('Permalink:').' <a href="'.get_attachment_link($re_id_attache).'" target="_blank" style="text-decoration: none; word-break: break-all;">'.get_attachment_link($re_id_attache).'</a></div>';
							$output_html .= '<div>URL: <a href="'.$new_url_attach.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$new_url_attach.'</a></div>';
							$re_id_attaches = explode('/', $new_url_attach);
							$output_html .= '<div>'.__('File name:').' '.end($re_id_attaches).'</div>';
							$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
							if ( wp_ext2type($ext) === 'image' ) {
								$output_html .= '<div>'.__('Images').': ';
								foreach ( $imagethumburls as $thumbsize => $imagethumburl ) {
									$output_html .= '[<a href="'.$imagethumburl.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$thumbsize.'</a>]';
								}
								$output_html .= '</div>';
							} else {
								$output_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
								$output_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
								if ( wp_ext2type($ext) === 'video' ||  wp_ext2type($ext) === 'audio' ) {
									$output_html .= '<div>'.__('Length:').' '.$length.'</div>';
								}
							}
							if ( !empty($url_replace_contents) ) {
								$output_html .= '<div>'.__('Replaced URL:', 'organizemedialibrary').' '.$url_replace_contents.'</div>';
							}

							$output_html .= '</div></div>';

							echo $output_html;

						}
					}
				}
				echo '<div class="updated"><ul><li>'.__('The following media has been rebuild organize.', 'organizemedialibrary').'</li></ul></div>';
			}

			?>
			<div class="submit">
			<form method="post" style="float: left;" action="<?php echo $scriptname; ?>">
				<input type="hidden" name="p" value="<?php echo $page; ?>" />
				<input type="submit" class="button" value="<?php _e('Search'); ?>" />
			</form>
			<form method="post" action="<?php echo admin_url( 'upload.php'); ?>">
				<input type="submit" class="button" value="<?php _e('Media Library'); ?>" />
			</form>
			</div>
			<div style="clear: both;"></div>
			<?php
		}

		?>
		</div>
		</div>

		<?php
	}

	/* ==================================================
	 * Pagenation
	 * @since	1.7
	 * string	$page
	 * string	$pagebegin
	 * string	$pageend
	 * string	$pagelast
	 * string	$scriptname
	 * string	$mimefilter
	 * return	$html
	 */
	function pagenation($page, $pagebegin, $pageend, $pagelast, $scriptname, $mimefilter){

			$pageprev = $page - 1;
			$pagenext = $page + 1;
			$scriptnamefirst = add_query_arg( array('p' => '1', 'mime' => $mimefilter ),  $scriptname);
			$scriptnameprev = add_query_arg( array('p' => $pageprev, 'mime' => $mimefilter ),  $scriptname);
			$scriptnamenext = add_query_arg( array('p' => $pagenext, 'mime' => $mimefilter ),  $scriptname);
			$scriptnamelast = add_query_arg( array('p' => $pagelast, 'mime' => $mimefilter ),  $scriptname);
			?>
			<div class="organizemedialibrary-pages">
			<span class="organizemedialibrary-links">
			<?php
			if ( $page <> 1 ){
				?><a title='<?php _e('Go to the first page'); ?>' href='<?php echo $scriptnamefirst; ?>'>&laquo;</a>
				<a title='<?php _e('Go to the previous page'); ?>' href='<?php echo $scriptnameprev; ?>'>&lsaquo;</a>
			<?php
			}
			echo $page; ?> / <?php echo $pagelast;
			?>
			<?php
			if ( $page <> $pagelast ){
				?><a title='<?php _e('Go to the next page'); ?>' href='<?php echo $scriptnamenext; ?>'>&rsaquo;</a>
				<a title='<?php _e('Go to the last page'); ?>' href='<?php echo $scriptnamelast; ?>'>&raquo;</a>
			<?php
			}
			?>
			</span>
			</div>
			<?php

	}

	/* ==================================================
	 * Sub Menu
	 */
	function move_uploads_folder_register_page() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$submenu = 3;
		$this->options_updated($submenu);

		include_once ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR.'/inc/OrganizeMediaLibrary.php';
		$organizemedialibrary = new OrganizeMediaLibrary();
		$organizemedialibrary_settings = get_option('organizemedialibrary_settings');
		$max_execution_time = $organizemedialibrary_settings['max_execution_time'];
		$prev_upload_dir = $organizemedialibrary_settings['prev_upload_dir'];
		$prev_upload_url = $organizemedialibrary_settings['prev_upload_url'];
		$prev_upload_path = $organizemedialibrary_settings['prev_upload_path'];

		list($new_upload_dir, $new_upload_url, $new_upload_path) = $organizemedialibrary->upload_dir_url_path();

		set_time_limit($max_execution_time);

		$adddb = FALSE;
		if (!empty($_POST['adddb'])){
			$adddb = $_POST['adddb'];
		}

		if( get_option('WPLANG') === 'ja' ) {
			mb_language('Japanese');
		} else if( get_option('WPLANG') === 'en' ) {
			mb_language('English');
		} else {
			mb_language('uni');
		}

		$scriptname = admin_url('admin.php?page=organizemedialibrary-move-uploads-folder-register');

		?>
		<div class="wrap">

		<h2>Organize Media Library <?php _e('Move Uploads folder & Rebuild & Organize', 'organizemedialibrary'); ?>
			<form method="post" style="float: right; margin-right: 0.2em;" action="<?php echo admin_url('admin.php?page=organizemedialibrary-search-register'); ?>" />
				<input type="submit" class="button" value="<?php _e('Search & Rebuild & Organize', 'organizemedialibrary'); ?>" />
			</form>
			<form method="post" style="float: right; margin-right: 0.2em;" action="<?php echo admin_url('admin.php?page=organizemedialibrary-settings'); ?>">
				<input type="submit" class="button" value="<?php _e('Settings'); ?>" />
			</form>
		</h2>
		<div style="clear: both;"></div>

		<div id="organizemedialibrary-loading"><img src="<?php echo ORGANIZEMEDIALIBRARY_PLUGIN_URL; ?>/css/loading.gif"></div>
		<div id="organizemedialibrary-loading-container">

		<?php

		global $wpdb;
		$attachments = $wpdb->get_results("
						SELECT	ID, post_title
						FROM	$wpdb->posts
						WHERE	post_type = 'attachment'
								ORDER BY post_date DESC
						");

		if ( $adddb <> 'TRUE' || $prev_upload_path === $new_upload_path ) {
			$wordpress_path = wp_normalize_path(ABSPATH);
			?>
			<form method="post" action="<?php echo $scriptname; ?>">
				<h3><?php _e('Caution:'); ?></h3>
				<div style="display:block; padding: 0px 5px; font-size: medium; font-weight: bold;"><?php _e('It will move the upload folder.', 'organizemedialibrary'); ?></div>
				<div style="display:block; padding: 0px 5px; font-size: medium; font-weight: bold;"><?php _e('It will move all of the upload files to a new folder.', 'organizemedialibrary'); ?></div>
				<div style="display:block; padding: 0px 5px; font-size: medium; font-weight: bold;"><?php _e('It will convert the URL of the uploaded files in all published content.', 'organizemedialibrary'); ?></div>
				<div style="display:block; padding: 5px 0;">
					<div><?php _e('Store uploads in this folder'); ?></div>
					<div style="font-size: small; font-weight: bold;"><code><?php echo $wordpress_path; ?></code></div>
					<input name="upload_path" type="text" id="upload_path" value="<?php echo esc_attr(get_option('upload_path')); ?>" />
					<input type="hidden" name="upload_path_button" value="1">
					<input type="hidden" name="adddb" value="TRUE">
					<div><?php _e('Default is <code>wp-content/uploads</code>'); ?></div>
					<div style="display:block; padding:5px 0; color:red;"><?php _e('Specified in the relative path', 'organizemedialibrary'); ?></div>
					<div style="display:block; padding:5px 0; color:red;">
					<?php _e('When you want to restore the original settings of the above, please be blank.', 'organizemedialibrary'); ?>
					</div>
				</div>
				<div style="padding-top: 5px; padding-bottom: 5px;">
					<input type="submit" class="button-primary button-large" value="<?php _e('Update Media'); ?>" />
				</div>
			</form>
			<?php
			if ( !empty($_POST) && $prev_upload_path === $new_upload_path ) {
				echo '<div class="error"><ul><li>'.__('Media has not been rebuild organize.', 'organizemedialibrary').'</li></ul></div>';
			}
		} else { // $adddb === 'TRUE'
			echo '<div class="updated"><ul><li>'.__('Store uploads in this folder').' --> '.__('Changes saved.').'</li></ul></div>';
			?>
			<div class="submit">
			<form method="post" style="float: left;" action="<?php echo $scriptname; ?>">
				<input type="submit" class="button" value="<?php _e('Move Uploads folder', 'organizemedialibrary'); ?>" />
			</form>
			<form method="post" action="<?php echo admin_url( 'upload.php'); ?>">
				<input type="submit" class="button" value="<?php _e('Media Library'); ?>" />
			</form>
			</div>
			<div style="clear: both;"></div>
			<?php

			$yearmonth_folders = get_option('uploads_use_yearmonth_folders');

			foreach ( $attachments as $attachment ){
				$attach_id = $attachment->ID;
				// Rebuild
				list($ext, $new_attach_title, $new_url_attach, $url_replace_contents, $metadata) = $organizemedialibrary->regist_all_move($attach_id, $prev_upload_dir, $new_upload_dir, $prev_upload_url, $new_upload_url, $prev_upload_path);

				list($imagethumburls, $mimetype, $length, $thumbnail_img_url, $stamptime, $file_size) = $organizemedialibrary->getmeta($ext, $attach_id, $metadata, $new_upload_url);

				$output_html = NULL;
				$output_html .= '<div style="border-bottom: 1px solid; padding-top: 5px; padding-bottom: 5px;">';
				$output_html .= '<img width="40" height="40" src="'.$thumbnail_img_url.'" style="float: left; margin: 5px;">';
				$output_html .= '<div style="overflow: hidden;">';
				$output_html .= '<div>'.__('Title').': '.$new_attach_title.'</div>';
				$output_html .= '<div>'.__('Permalink:').' <a href="'.get_attachment_link($attach_id).'" target="_blank" style="text-decoration: none; word-break: break-all;">'.get_attachment_link($attach_id).'</a></div>';
				$output_html .= '<div>URL: <a href="'.$new_url_attach.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$new_url_attach.'</a></div>';
				$attach_ids = explode('/', $new_url_attach);
				$output_html .= '<div>'.__('File name:').' '.end($attach_ids).'</div>';
				$output_html .= '<div>'.__('Date/Time').': '.$stamptime.'</div>';
				if ( wp_ext2type($ext) === 'image' ) {
					$output_html .= '<div>'.__('Images').': ';
					foreach ( $imagethumburls as $thumbsize => $imagethumburl ) {
						$output_html .= '[<a href="'.$imagethumburl.'" target="_blank" style="text-decoration: none; word-break: break-all;">'.$thumbsize.'</a>]';
					}
					$output_html .= '</div>';
				} else {
					$output_html .= '<div>'.__('File type:').' '.$mimetype.'</div>';
					$output_html .= '<div>'.__('File size:').' '.size_format($file_size).'</div>';
					if ( wp_ext2type($ext) === 'video' ||  wp_ext2type($ext) === 'audio' ) {
						$output_html .= '<div>'.__('Length:').' '.$length.'</div>';
					}
				}
				if ( !empty($url_replace_contents) ) {
					$output_html .= '<div>'.__('Replaced URL:', 'organizemedialibrary').' '.$url_replace_contents.'</div>';
				}

				$output_html .= '</div></div>';

				echo $output_html;
			}
			echo '<div class="updated"><ul><li>'.__('The following media has been rebuild organize.', 'organizemedialibrary').'</li></ul></div>';

			?>
			<div class="submit">
			<form method="post" style="float: left;" action="<?php echo $scriptname; ?>">
				<input type="submit" class="button" value="<?php _e('Move Uploads folder', 'organizemedialibrary'); ?>" />
			</form>
			<form method="post" action="<?php echo admin_url( 'upload.php'); ?>">
				<input type="submit" class="button" value="<?php _e('Media Library'); ?>" />
			</form>
			</div>
			<div style="clear: both;"></div>
			<?php
		}

		?>
		</div>
		</div>

		<?php

	}

	/* ==================================================
	 * Update	wp_options table.
	 * @param	string	$submenu
	 * @since	1.0
	 */
	function options_updated($submenu){

		$organizemedialibrary_settings = get_option('organizemedialibrary_settings');

		switch ($submenu) {
			case 1:
				if ( !empty($_POST) ) {
					if ( !empty($_POST['organizemedialibrary_max_execution_time']) ) {
						$max_execution_time = intval($_POST['organizemedialibrary_max_execution_time']);
					} else {
						$max_execution_time = $organizemedialibrary_settings['max_execution_time'];
					}
					if ( !empty($_POST['organizemedialibrary_folderset']) ) {
						$folderset = $_POST['organizemedialibrary_folderset'];
					} else {
						$folderset = $organizemedialibrary_settings['folderset'];
					}
					$basedir = $organizemedialibrary_settings['basedir'];
					if ( $folderset === 'movefolder' ) {
						if (!empty($_POST['targetdir'])){
							$targetdir = urldecode($_POST['targetdir']);
							if( get_option('WPLANG') === 'ja' ) {
								mb_language('Japanese');
							} else if( get_option('WPLANG') === 'en' ) {
								mb_language('English');
							} else {
								mb_language('uni');
							}
							$newdir = NULL;
							if (!empty($_POST['newdir'])){
								$newdir = urldecode($_POST['newdir']);
								$target_realdir = wp_normalize_path(ABSPATH).$targetdir.'/'.$newdir;
								$targetdir = $targetdir.'/'.$newdir;
								if (DIRECTORY_SEPARATOR === '\\' && mb_language() === 'Japanese') {
									$mkdir_targetdir = mb_convert_encoding($target_realdir, "sjis-win", "auto");
								} else {
									$mkdir_targetdir = mb_convert_encoding($target_realdir, "UTF-8", "auto");
								}
								if ( !file_exists($mkdir_targetdir) ) {
									mkdir($mkdir_targetdir, 0757, true);
								}
								$targetdir = mb_convert_encoding($targetdir, "UTF-8", "auto");
							}
						} else {
							$targetdir = $organizemedialibrary_settings['targetdir'];
							if ( ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH <> $basedir ) {
								$basedir = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH;
								$targetdir = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH;
							}
						}
					} else {
						$targetdir = $organizemedialibrary_settings['targetdir'];
					}
					$organizemedialibrary_tbl = array(
										'pagemax' => $organizemedialibrary_settings['pagemax'],
										'basedir' => $basedir,
										'folderset' => $folderset,
										'targetdir' => $targetdir,
										'max_execution_time' => $max_execution_time
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
			case 2:
				if ( !empty($_POST['organizemedialibrary_pagemax']) ) {
					$pagemax = intval($_POST['organizemedialibrary_pagemax']);
				} else {
					$pagemax = $organizemedialibrary_settings['pagemax'];
				}
				$organizemedialibrary_tbl = array(
									'pagemax' => $pagemax,
									'basedir' => $organizemedialibrary_settings['basedir'],
									'folderset' => $organizemedialibrary_settings['folderset'],
									'targetdir' => $organizemedialibrary_settings['targetdir'],
									'max_execution_time' => $organizemedialibrary_settings['max_execution_time']
									);
				update_option( 'organizemedialibrary_settings', $organizemedialibrary_tbl );
				break;
			case 3:
				if ( !empty($_POST) ) {
					include_once ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR.'/inc/OrganizeMediaLibrary.php';
					$organizemedialibrary = new OrganizeMediaLibrary();
					list($upload_dir, $upload_url, $upload_path) = $organizemedialibrary->upload_dir_url_path();
					update_option( 'upload_path', $_POST['upload_path'] );
					$organizemedialibrary_tbl = array(
										'pagemax' => $organizemedialibrary_settings['pagemax'],
										'basedir' => $organizemedialibrary_settings['basedir'],
										'prev_upload_dir' => $upload_dir,
										'prev_upload_url' => $upload_url,
										'prev_upload_path' => $upload_path,
										'folderset' => $organizemedialibrary_settings['folderset'],
										'targetdir' => ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH,
										'max_execution_time' => $organizemedialibrary_settings['max_execution_time']
										);
					update_option( 'organizemedialibrary_settings', $organizemedialibrary_tbl );
					unset($organizemedialibrary ,$upload_dir, $upload_url, $upload_path);
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
<script type="text/javascript">
window.addEventListener( "load", function(){
  jQuery("#organizemedialibrary-loading").delay(2000).fadeOut();
  jQuery("#organizemedialibrary-loading-container").delay(2000).fadeIn();
}, false );
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