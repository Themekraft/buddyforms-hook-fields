<?php
/*
 * Plugin Name: BuddyForms Hook Fields
 * Plugin URI: https://wordpress.org/plugins/buddyforms-hook-fields/
 * Description: BuddyForms Hook Fields
 * Version: 1.3.20
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Requires Plugins: buddyforms
 * Author: ThemeKraft
 * Author URI: https://themekraft.com/buddyforms/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: buddyforms-hook-fields
 * Svn: buddyforms-hook-fields
 *****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ****************************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BUDDYFORMS_HOOK_FIELDS_VERSION', '1.3.20' );

if ( ! function_exists( 'buddyforms_addon_plugin_dependencies_slug' ) ) {
	/**
	 * Let the running BuddyForms copy (free or premium) satisfy "Requires Plugins: buddyforms".
	 *
	 * @param string $slug Dependency slug.
	 *
	 * @return string
	 */
	function buddyforms_addon_plugin_dependencies_slug( $slug ) {
		if ( 'buddyforms' === $slug && defined( 'BUDDYFORMS_INSTALL_PATH' ) ) {
			return basename( BUDDYFORMS_INSTALL_PATH );
		}

		return $slug;
	}

	add_filter( 'wp_plugin_dependencies_slug', 'buddyforms_addon_plugin_dependencies_slug' );
}

//
// Load the add-on
//
add_action(
	'init',
	function () {
		require dirname( __FILE__ ) . '/includes/list-all-post-fields.php';
		require dirname( __FILE__ ) . '/includes/form-options.php';
		require dirname( __FILE__ ) . '/includes/templates-handler.php';
		require dirname( __FILE__ ) . '/includes/gutenberg/shortcodes-to-blocks.php';
	},
	1,
	1
);

// Create a helper function for easy SDK access.
function buddyforms_hook_fields_fs() {
	global $buddyforms_hook_fields_fs;

	if ( ! isset( $buddyforms_hook_fields_fs ) ) {
		// The Freemius SDK comes with BuddyForms, which loads it before this add-on initializes.
		if ( ! function_exists( 'fs_dynamic_init' ) ) {
			return false;
		}

		try {
			$buddyforms_hook_fields_fs = fs_dynamic_init(
				array(
					'id'             => '412',
					'slug'           => 'buddyforms-hook-fields',
					'type'           => 'plugin',
					'public_key'     => 'pk_834e229dbe701030d3c9d497b9ad0',
					'is_premium'     => false,
					'has_paid_plans' => false,
					'is_org_compliant' => true,
					'parent'         => array(
						'id'         => '391',
						'slug'       => 'buddyforms',
						'public_key' => 'pk_dea3d8c1c831caf06cfea10c7114c',
						'name'       => 'BuddyForms',
					),
					'menu'           => array(
						'slug'       => 'edit.php?post_type=buddyforms-hook-fields',
						'first-path' => 'plugins.php',
						'support'    => false,
					),
					'bundle_license_auto_activation' => true,
				)
			);
		} catch ( Freemius_Exception $e ) {
			return false;
		}
	}

	return $buddyforms_hook_fields_fs;
}

function buddyforms_hook_fields_fs_is_parent_active_and_loaded() {
	// Check if the parent's init SDK method exists.
	return function_exists( 'buddyforms_core_fs' );
}

function buddyforms_hook_fields_fs_is_parent_active() {
	$active_plugins_basenames = get_option( 'active_plugins' );

	foreach ( $active_plugins_basenames as $plugin_basename ) {
		if ( 0 === strpos( $plugin_basename, 'buddyforms/' ) ||
			 0 === strpos( $plugin_basename, 'buddyforms-premium/' )
		) {
			return true;
		}
	}

	return false;
}

function buddyforms_hook_fields_fs_init() {
	if ( buddyforms_hook_fields_fs_is_parent_active_and_loaded() ) {
		// Init Freemius.
		buddyforms_hook_fields_fs();
		// Parent is active, add your init code here.
	}
}

if ( buddyforms_hook_fields_fs_is_parent_active_and_loaded() ) {
	// If parent already included, init add-on.
	buddyforms_hook_fields_fs_init();
} elseif ( buddyforms_hook_fields_fs_is_parent_active() ) {
	// Init add-on only after the parent is loaded.
	add_action( 'buddyforms_core_fs_loaded', 'buddyforms_hook_fields_fs_init' );
} else {
	// Even though the parent is not activated, execute add-on for activation / uninstall hooks.
	buddyforms_hook_fields_fs_init();
}
