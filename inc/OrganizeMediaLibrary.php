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
	 * @return	array	$imagethumburls(array), $mimetype(string), $length(string), $thumbnail_img_url(string), $stamptime(string), $file_size(string), $filetype(string)
	 * @since	1.0
	 */
	function getmeta($ext, $attach_id){

		$imagethumburls = array();
		$mimetype = NULL;
		$length = NULL;

		if ( wp_ext2type($ext) === 'image' ){
			$metadata = wp_get_attachment_metadata( $attach_id );
			if($metadata){
				$imagethumburl_base = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_URL.'/'.rtrim($metadata['file'], wp_basename($metadata['file']));
				foreach ( $metadata as $key1 => $key2 ){
					if ( $key1 === 'sizes' ) {
						foreach ( $metadata[$key1] as $key2 => $key3 ){
							$imagethumburls[$key2] = $imagethumburl_base.$metadata['sizes'][$key2]['file'];
						}
					}
				}
			}
		}else if ( wp_ext2type($ext) === 'video' ){
			$metadata = wp_read_video_metadata( get_attached_file($attach_id) );
			if ($metadata) {
				if(array_key_exists ('fileformat', $metadata)){
					$mimetype = $metadata['fileformat'].'('.$metadata['mime_type'].')';
				}
				if(array_key_exists ('length_formatted', $metadata)){
					$length = $metadata['length_formatted'];
				}
			}
		}else if ( wp_ext2type($ext) === 'audio' ){
			$metadata = wp_read_audio_metadata( get_attached_file($attach_id) );
			if ($metadata) {
				if(array_key_exists ('fileformat', $metadata)){
					$mimetype = $metadata['fileformat'].'('.$metadata['mime_type'].')';
				}
				if(array_key_exists ('length_formatted', $metadata)){
					$length = $metadata['length_formatted'];
				}
			}
		} else {
			$metadata = NULL;
		}

		$image_attr_thumbnail = wp_get_attachment_image_src($attach_id, 'thumbnail', true);
		$thumbnail_img_url = $image_attr_thumbnail[0];

		$stamptime = get_the_time( 'Y-n-j ', $attach_id ).get_the_time( 'G:i', $attach_id );
		if ( isset( $metadata['filesize'] ) ) {
			$file_size = $metadata['filesize'];
		} else {
			$file_size = @filesize( get_attached_file($attach_id) );
		}
		$filetype = strtoupper($ext);

		return array($imagethumburls, $mimetype, $length, $thumbnail_img_url, $stamptime, $file_size, $filetype);

	}

	/* ==================================================
	 * @param	int		$re_id_attache
	 * @param	bool	$yearmonth_folders
	 * @return	array	$ext(string), $new_attach_title(string), $new_url_attach(string)
	 * @since	1.0
	 */
	function regist($re_id_attache, $yearmonth_folders){

		$re_attache = get_post( $re_id_attache );
		$new_attach_title = $re_attache->post_title;
		$new_url_attach = wp_get_attachment_url( $re_id_attache );

		$exts = explode('.', $new_url_attach);
		$ext = end($exts);

		$filename = str_replace(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_URL.'/', '', $new_url_attach);

		$postdategmt = $re_attache->post_modified_gmt;

		// Move YearMonth Folders
		if ( $yearmonth_folders == 1 ) {
			$y = substr( $postdategmt, 0, 4 );
			$m = substr( $postdategmt, 5, 2 );
			$subdir = "/$y/$m";
			if ( ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.'/'.$filename <> ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.$subdir.'/'.wp_basename($filename) ) {

				if ( !file_exists(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.$subdir) ) {
					mkdir(ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.$subdir, 0757, true);
				}
				copy( ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.'/'.$filename, ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.$subdir.'/'.wp_basename($filename) );

				$filedirname = str_replace( wp_basename( $filename ), '', $filename );
				$delfilename = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR.'/'.$filedirname.wp_basename( $filename, '.'.$ext ).'*';
				foreach ( glob($delfilename) as $val ) {
					unlink($val);
				}

				$filename = ltrim($subdir, '/').'/'.wp_basename($filename);
				$new_url_attach = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_URL.'/'.$filename;

				update_post_meta( $re_id_attache, '_wp_attached_file', $filename );

				$up_post = array(
								'ID' => $re_id_attache,
								'guid' => esc_url($new_url_attach)
							);
				wp_update_post( $up_post );

			}
		}

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

		return array($ext, $new_attach_title, $new_url_attach);

	}

}

?>