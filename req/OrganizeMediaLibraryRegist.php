<?php
/**
 * Organize Media Library
 * 
 * @package    Organize Media Library
 * @subpackage OrganizeMediaLibraryRegist registered in the database
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

class OrganizeMediaLibraryRegist {

	/* ==================================================
	 * Settings register
	 * @since	1.0
	 */
	function register_settings(){

		$plugin_datas = get_file_data( ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR.'/organizemedialibrary.php', array('version' => 'Version') );
		$plugin_version = floatval($plugin_datas['version']);

		if ( !get_option('organizemedialibrary_settings') ) {
			$organizemedialibrary_tbl = array(
								'pagemax' => 20,
								'folderset' => 'movefolder',
								'targetdir' => ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH,
								'max_execution_time' => 300
							);
			update_option( 'organizemedialibrary_settings', $organizemedialibrary_tbl );
		} else {
			$organizemedialibrary_settings = get_option('organizemedialibrary_settings');
			if ( $plugin_version < 1.7 ) {
				$organizemedialibrary_tbl = array(
									'max_execution_time' => $organizemedialibrary_settings['max_execution_time']
								);
				update_option( 'organizemedialibrary_settings', $organizemedialibrary_tbl );
			} else if ( $plugin_version >= 1.7 && $plugin_version < 3.0 ) {
				if ( array_key_exists( "pagemax", $organizemedialibrary_settings ) ) {
					$pagemax = $organizemedialibrary_settings['pagemax'];
				} else {
					$pagemax = 20;
				}
				$organizemedialibrary_tbl = array(
									'pagemax' => $pagemax,
									'max_execution_time' => $organizemedialibrary_settings['max_execution_time']
								);
				update_option( 'organizemedialibrary_settings', $organizemedialibrary_tbl );
			} else if ( $plugin_version >= 3.0 ) {
				if ( array_key_exists( "folderset", $organizemedialibrary_settings ) ) {
					$folderset = $organizemedialibrary_settings['folderset'];
				} else {
					$folderset = 'movefolder';
				}
				if ( array_key_exists( "targetdir", $organizemedialibrary_settings ) ) {
					$targetdir = $organizemedialibrary_settings['targetdir'];
				} else {
					$targetdir = ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH;
				}
				$organizemedialibrary_tbl = array(
									'pagemax' => $organizemedialibrary_settings['pagemax'],
									'folderset' => $folderset,
									'targetdir' => $targetdir,
									'max_execution_time' => $organizemedialibrary_settings['max_execution_time']
								);
				update_option( 'organizemedialibrary_settings', $organizemedialibrary_tbl );
			}
		}

	}

}

?>