<?php
/**
 * Organize Media Library
 * 
 * @package    Organize Media Library
 * @subpackage OrganizeMediaLibrary Main Functions
/*  Copyright (c) 2015- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
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

class OrganizeMediaLibrary {

	/* ==================================================
	 * @param	string	$ext
	 * @param	int		$attach_id
	 * @param	array	$metadata
	 * @return	array	$imagethumburls(array), $mimetype(string), $length(string), $thumbnail_img_url(string), $stamptime(string), $file_size(string)
	 * @since	1.0
	 */
	function getmeta($ext, $attach_id, $metadata){

		$imagethumburls = array();
		$mimetype = NULL;
		$length = NULL;

		if(empty($metadata)){
			// for wp_read_audio_metadata and wp_read_video_metadata
			include_once( ABSPATH . 'wp-admin/includes/media.php' );
		}

		if ( wp_ext2type($ext) === 'image' ){
			if(empty($metadata)){
				$metadata = wp_get_attachment_metadata( $attach_id );
			}
			$imagethumburl_base = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_URL.'/'.rtrim($metadata['file'], wp_basename($metadata['file']));
			foreach ( $metadata as $key1 => $key2 ){
				if ( $key1 === 'sizes' ) {
					foreach ( $metadata[$key1] as $key2 => $key3 ){
						$imagethumburls[$key2] = $imagethumburl_base.$metadata['sizes'][$key2]['file'];
					}
				}
			}
		}else if ( wp_ext2type($ext) === 'video' ){
			if(empty($metadata)){
				$metadata = wp_read_video_metadata( get_attached_file($attach_id) );
			}
			if(array_key_exists ('fileformat', $metadata)){
				$mimetype = $metadata['fileformat'].'('.$metadata['mime_type'].')';
			}
			if(array_key_exists ('length_formatted', $metadata)){
				$length = $metadata['length_formatted'];
			}
		}else if ( wp_ext2type($ext) === 'audio' ){
			if(empty($metadata)){
				$metadata = wp_read_audio_metadata( get_attached_file($attach_id) );
			}
			if(array_key_exists ('fileformat', $metadata)){
				$mimetype = $metadata['fileformat'].'('.$metadata['mime_type'].')';
			}
			if(array_key_exists ('length_formatted', $metadata)){
				$length = $metadata['length_formatted'];
			}
		} else {
			$metadata = NULL;
			$filetype = wp_check_filetype( get_attached_file($attach_id) );
			$mimetype =  $filetype['ext'].'('.$filetype['type'].')';
		}

		$image_attr_thumbnail = wp_get_attachment_image_src($attach_id, 'thumbnail', true);
		$thumbnail_img_url = $image_attr_thumbnail[0];

		$stamptime = get_the_time( 'Y-n-j ', $attach_id ).get_the_time( 'G:i', $attach_id );
		if ( isset( $metadata['filesize'] ) ) {
			$file_size = $metadata['filesize'];
		} else {
			$file_size = @filesize( get_attached_file($attach_id) );
		}

		return array($imagethumburls, $mimetype, $length, $thumbnail_img_url, $stamptime, $file_size);

	}

	/* ==================================================
	 * @param	int		$re_id_attache
	 * @param	bool	$yearmonth_folders
	 * @param	string	$folderset
	 * @param	string	$target_folder
	 * @return	array	$ext(string), $new_attach_title(string), $new_url_attach(string), $url_replace_contents(string), $metadata(array)
	 * @since	1.0
	 */
	function regist($re_id_attache, $yearmonth_folders, $folderset, $target_folder){

		$re_attache = get_post( $re_id_attache );
		$new_attach_title = $re_attache->post_title;
		$url_attach = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_URL.'/'.get_post_meta($re_id_attache, '_wp_attached_file', true);
		$new_url_attach = $url_attach;
		$url_replace_contents = NULL;

		$exts = explode('.', $url_attach);
		$ext = end($exts);

		if ( $folderset === 'movefolder' ) {
			$suffix_attach_file = '.'.$ext;
			$filename = str_replace(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_URL.'/', '', $url_attach);
			$postdategmt = $re_attache->post_date_gmt;
			if ( $yearmonth_folders == 1 ) { 		// Move YearMonth Folders
				$y = substr( $postdategmt, 0, 4 );
				$m = substr( $postdategmt, 5, 2 );
				$subdir = "/$y/$m";
			} else {
				$subdir = str_replace(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH, '', $target_folder);
				if (DIRECTORY_SEPARATOR === '\\' && mb_language() === 'Japanese') {
					$subdir = mb_convert_encoding($subdir, "sjis-win", "auto");
				} else {
					$subdir = mb_convert_encoding($subdir, "UTF-8", "auto");
				}
			}
			$filename_base = wp_basename($filename);
			if ( ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.'/'.$filename <> ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.$subdir.'/'.$filename_base ) {

				if ( !file_exists(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.$subdir) ) {
					mkdir(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.$subdir, 0757, true);
				}
				if ( file_exists(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.$subdir.'/'.$filename_base) ) {
					$filename_base = wp_basename($filename, $suffix_attach_file).date_i18n( "dHis", FALSE, FALSE ).$suffix_attach_file;
				}
				copy( ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.'/'.$filename, ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.$subdir.'/'.$filename_base );
				$filedirname = str_replace( wp_basename( $filename ), '', $filename );
				$delfilename = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.'/'.$filedirname.wp_basename( $filename, '.'.$ext ).'*';
				foreach ( glob($delfilename) as $val ) {
					unlink($val);
				}
				$filename = ltrim($subdir, '/').'/'.$filename_base;
				$new_url_attach = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_URL.'/'.$filename;
				update_post_meta( $re_id_attache, '_wp_attached_file', $filename );

				global $wpdb;
				// Change DB contents
				$search_url = str_replace('.'.$ext, '', $url_attach);
				$replace_url = str_replace('.'.$ext, '', $new_url_attach);
				// Search
				$search_posts = $wpdb->get_results(
					"SELECT post_title,post_status,guid FROM $wpdb->posts WHERE instr(post_content, '$search_url') > 0"
				);
				if ( $search_posts ) {
					foreach ($search_posts as $search_post){
						if ( $search_post->post_status === 'publish' ) {
							$url_replace_contents .= '[<a href="'.$search_post->guid.'" target="_blank"> '.$search_post->post_title.'</a>]';
						}
					}
				}

				// Replace
				$sql = $wpdb->prepare(
					"UPDATE `$wpdb->posts` SET post_content = replace(post_content, %s, %s)",
					$search_url,
					$replace_url
				);
				$wpdb->query($sql);

				// Change DB Attachement post guid
				$update_array = array(
								'guid'=> $new_url_attach
							);
				$id_array= array('ID'=> $re_id_attache);
				$wpdb->update( $wpdb->posts, $update_array, $id_array, array('%s'), array('%d') );
				unset($update_array, $id_array);
			}
		}

		// for wp_read_audio_metadata and wp_read_video_metadata
		include_once( ABSPATH . 'wp-admin/includes/media.php' );
		// for wp_generate_attachment_metadata
		include_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Meta data Regist
		if ( wp_ext2type($ext) === 'image' ){
			$metadata = wp_generate_attachment_metadata( $re_id_attache, get_attached_file($re_id_attache) );
			wp_update_attachment_metadata( $re_id_attache, $metadata );
		}else if ( wp_ext2type($ext) === 'video' ){
			$metadata = wp_read_video_metadata( get_attached_file($re_id_attache) );
			wp_update_attachment_metadata( $re_id_attache, $metadata );
		}else if ( wp_ext2type($ext) === 'audio' ){
			$metadata = wp_read_audio_metadata( get_attached_file($re_id_attache) );
			wp_update_attachment_metadata( $re_id_attache, $metadata );
		} else {
			$metadata = NULL;
		}

		return array($ext, $new_attach_title, $new_url_attach, $url_replace_contents, $metadata);

	}

	/* ==================================================
	 * @param	string	$dir
	 * @return	array	$dirlist
	 * @since	3.0
	 */
	function scan_dir($dir) {

		$dirlist = $tmp = array();
		$searchdir = glob($dir . '/*', GLOB_ONLYDIR);
		if ( is_array($searchdir) ) {
		    foreach($searchdir as $child_dir) {
			    if ($tmp = $this->scan_dir($child_dir)) {
		   		    $dirlist = array_merge($dirlist, $tmp);
		       	}
			}

		    foreach($searchdir as $child_dir) {
					$dirlist[] = $child_dir;
			}
		}

		arsort($dirlist);
		return $dirlist;

	}

	/* ==================================================
	 * @param	string	$searchdir
	 * @return	string	$dirlist
	 * @since	3.0
	 */
	function dir_selectbox($searchdir) {

		if( get_option('WPLANG') === 'ja' ) {
			mb_language('Japanese');
		} else if( get_option('WPLANG') === 'en' ) {
			mb_language('English');
		} else {
			mb_language('uni');
		}

		$dirs = $this->scan_dir(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR);
		$linkselectbox = NULL;
		foreach ($dirs as $linkdir) {
			$linkdirenc = mb_convert_encoding(str_replace(ABSPATH, "", $linkdir), "UTF-8", "auto");
			if( $searchdir === $linkdirenc ){
				$linkdirs = '<option value="'.urlencode($linkdirenc).'" selected>'.$linkdirenc.'</option>';
			}else{
				$linkdirs = '<option value="'.urlencode($linkdirenc).'">'.$linkdirenc.'</option>';
			}
			$linkselectbox = $linkselectbox.$linkdirs;
		}
		if( $searchdir ===  ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH ){
			$linkdirs = '<option value="'.urlencode(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH).'" selected>'.ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH.'</option>';
		}else{
			$linkdirs = '<option value="'.urlencode(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH).'">'.ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH.'</option>';
		}
		$linkselectbox = $linkselectbox.$linkdirs;

		return $linkselectbox;

	}

}

?>