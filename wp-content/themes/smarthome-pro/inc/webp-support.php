<?php
/**
 * WebP Image Upload & Processing Support
 *
 * Fixes WebP upload failures on Windows XAMPP / WordPress environments.
 *
 * Root causes addressed:
 *  A. Windows does not have image/webp in its MIME registry, so PHP's
 *     finfo/mime_content_type returns "application/octet-stream". WordPress
 *     then treats the file as an unknown type and rejects the upload OR sets
 *     $data['type'] to the wrong value before our filter runs.
 *
 *  B. WordPress's GD image editor may not declare WebP support on older
 *     PHP/GD builds, causing "cannot generate responsive image sizes" error.
 *
 * Fixes applied:
 *  1. Add image/webp to allowed upload MIME types.
 *  2. Force correct ext/type in filetype check using magic-byte detection
 *     (reads RIFF....WEBP header) — ALWAYS overrides for .webp files,
 *     even when WordPress has already set a wrong type.
 *  3. Inject image/webp into WordPress's getimagesize MIME→ext map so the
 *     internal validation cross-check passes.
 *  4. Prioritise Imagick (has native WebP) over GD when available.
 *  5. Register a custom GD editor subclass with explicit WebP support,
 *     loaded safely via the wp_image_editors filter (no early require_once).
 *  6. Keep WebP output as WebP (prevent silent JPEG downgrade).
 *  7. Ensure WebP shows correctly in the media library.
 *  8. Mark WebP as a displayable image type.
 *
 * @package SmartHome_Pro
 * @since   1.0.1
 */

defined( 'ABSPATH' ) || exit;


/* ==========================================================================
   1. ALLOW WEBP MIME TYPE
   ========================================================================== */

/**
 * Add image/webp to WordPress's allowed upload MIME types list.
 *
 * @param  array $mimes Existing allowed MIME types.
 * @return array        Modified list including WebP.
 */
function shp_allow_webp_upload( $mimes ) {
	$mimes['webp'] = 'image/webp';
	return $mimes;
}
add_filter( 'upload_mimes', 'shp_allow_webp_upload' );


/* ==========================================================================
   2. FORCE CORRECT MIME FOR WEBP — MAGIC BYTES (Windows fix)
   ========================================================================== */

/**
 * Always force ext='webp' and type='image/webp' for files whose:
 *   (a) client-side name ends in .webp, AND
 *   (b) binary header matches the RIFF....WEBP magic signature.
 *
 * This runs UNCONDITIONALLY — we do NOT skip when $data already has values
 * because Windows XAMPP often sets type='application/octet-stream' (not
 * empty, but wrong), which an early-return guard would silently let through.
 *
 * @param  array  $data     Validated file data (ext, type, proper_filename).
 * @param  string $file     Absolute path to the uploaded temp file.
 * @param  string $filename Original client-side filename.
 * @param  array  $mimes    Currently allowed MIME types.
 * @return array            $data with corrected ext/type for WebP files.
 */
function shp_fix_webp_mime_check( $data, $file, $filename, $mimes ) {

	// Only process files whose extension is .webp.
	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	if ( 'webp' !== $ext ) {
		return $data;
	}

	// Verify it is truly a WebP file by reading the 12-byte magic header.
	// WebP layout: bytes 0-3 = "RIFF", bytes 8-11 = "WEBP".
	$confirmed = false;

	if ( ! empty( $file ) && file_exists( $file ) && is_readable( $file ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$fh = fopen( $file, 'rb' );
		if ( $fh ) {
			$header    = fread( $fh, 12 );
			fclose( $fh );
			$confirmed = (
				strlen( $header ) >= 12
				&& 'RIFF' === substr( $header, 0, 4 )
				&& 'WEBP' === substr( $header, 8, 4 )
			);
		}
	}

	if ( $confirmed ) {
		// Override whatever WordPress (or the OS) detected — force correct values.
		$data['ext']             = 'webp';
		$data['type']            = 'image/webp';
		$data['proper_filename'] = $filename;
	}

	return $data;
}
// Priority 20 — run AFTER WordPress's own detection (priority 10) so we
// can override whatever wrong value it set.
add_filter( 'wp_check_filetype_and_ext', 'shp_fix_webp_mime_check', 20, 4 );


/* ==========================================================================
   2b. FIX wp_get_image_mime FOR WEBP (used during resize/sub-size generation)
   ========================================================================== */

/**
 * WordPress calls wp_get_image_mime() when an image editor loads a file for
 * resizing. It uses exif_imagetype() or finfo_file() internally. On Windows
 * XAMPP either of these may return false/wrong for WebP, causing the editor
 * to refuse to process the file (even though the upload succeeded).
 *
 * This filter detects WebP by reading magic bytes and returns the correct
 * MIME type when the built-in detection fails or returns the wrong value.
 *
 * @param  string|false $mime  MIME type detected by WordPress (may be wrong).
 * @param  string       $file  Absolute path to the image file.
 * @return string|false        Corrected MIME type for WebP files.
 */
function shp_fix_webp_get_image_mime( $mime, $file ) {
	// Only act on files with .webp extension when detected MIME is wrong.
	if ( 'image/webp' === $mime ) {
		return $mime; // Already correct — nothing to do.
	}

	if ( 'webp' !== strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) ) {
		return $mime;
	}

	// Confirm it's a genuine WebP by checking the binary header.
	if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
		return $mime;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	$fh = fopen( $file, 'rb' );
	if ( ! $fh ) {
		return $mime;
	}
	$header = fread( $fh, 12 );
	fclose( $fh );

	if (
		strlen( $header ) >= 12
		&& 'RIFF' === substr( $header, 0, 4 )
		&& 'WEBP' === substr( $header, 8, 4 )
	) {
		return 'image/webp';
	}

	return $mime;
}
add_filter( 'wp_get_image_mime', 'shp_fix_webp_get_image_mime', 10, 2 );


/* ==========================================================================
   3. ADD WEBP TO WORDPRESS'S INTERNAL MIME→EXT VALIDATION MAP
   ========================================================================== */

/**
 * WordPress uses getimagesize_mimes_to_exts internally to cross-check that a
 * detected real MIME type maps to the expected file extension. If image/webp
 * is missing from this map the cross-check fails and the upload is rejected
 * even though we've allowed it in upload_mimes.
 *
 * @param  array $map Existing MIME-type → extension map.
 * @return array      Map with image/webp added.
 */
function shp_add_webp_to_mime_map( $map ) {
	if ( ! isset( $map['image/webp'] ) ) {
		$map['image/webp'] = 'webp';
	}
	return $map;
}
add_filter( 'getimagesize_mimes_to_exts', 'shp_add_webp_to_mime_map' );


/* ==========================================================================
   4. PRIORITISE IMAGICK FOR WEBP
   ========================================================================== */

/**
 * Move WP_Image_Editor_Imagick ahead of WP_Image_Editor_GD when Imagick
 * supports WebP. Imagick almost universally has WebP; GD requires libwebp
 * compiled in (not guaranteed on all XAMPP builds).
 *
 * @param  string[] $editors Ordered list of image editor class names.
 * @return string[]          Modified list with Imagick first if suitable.
 */
function shp_webp_prioritise_imagick( $editors ) {
	if ( ! class_exists( 'Imagick' ) ) {
		return $editors;
	}

	try {
		$supported = Imagick::queryFormats( 'WEBP' );
	} catch ( Exception $e ) {
		return $editors;
	}

	if ( empty( $supported ) ) {
		return $editors;
	}

	// Remove existing Imagick entry (whatever position it is), then prepend.
	$editors = array_values(
		array_filter(
			$editors,
			static function ( $e ) {
				return 'WP_Image_Editor_Imagick' !== $e;
			}
		)
	);
	array_unshift( $editors, 'WP_Image_Editor_Imagick' );

	return $editors;
}
add_filter( 'wp_image_editors', 'shp_webp_prioritise_imagick', 9 );


/* ==========================================================================
   5. CUSTOM GD EDITOR — EXPLICIT WEBP SUPPORT
   ========================================================================== */

/**
 * Register a custom GD image editor subclass that explicitly declares WebP
 * support by testing for the actual PHP functions (imagecreatefromwebp /
 * imagewebp) rather than relying on the IMG_WEBP constant check, which can
 * fail on some XAMPP-bundled GD builds despite WebP being functional.
 *
 * The class is defined INSIDE the wp_image_editors filter callback so that
 * WordPress's WP_Image_Editor_GD is guaranteed to already be loaded (WP
 * loads image editor classes before firing wp_image_editors).
 */
add_filter(
	'wp_image_editors',
	static function ( $editors ) {

		// Only define our class once.
		if ( ! class_exists( 'SHP_Image_Editor_GD' ) && class_exists( 'WP_Image_Editor_GD' ) ) {

			// phpcs:ignore Generic.Files.OneClassPerFile.MultipleFound
			class SHP_Image_Editor_GD extends WP_Image_Editor_GD {

				/**
				 * Report WebP as supported when GD has the required functions,
				 * bypassing the sometimes-broken IMG_WEBP constant check.
				 *
				 * @param  string $mime_type MIME type to test.
				 * @return bool              True when this editor can handle the type.
				 */
				public static function supports_mime_type( $mime_type ) {
					if ( 'image/webp' === $mime_type ) {
						return function_exists( 'imagecreatefromwebp' )
							&& function_exists( 'imagewebp' );
					}
					return parent::supports_mime_type( $mime_type );
				}
			}
		}

		// Insert SHP_Image_Editor_GD immediately before WP_Image_Editor_GD.
		if ( class_exists( 'SHP_Image_Editor_GD' ) ) {
			$gd_pos = array_search( 'WP_Image_Editor_GD', $editors, true );
			if ( false !== $gd_pos ) {
				array_splice( $editors, (int) $gd_pos, 0, array( 'SHP_Image_Editor_GD' ) );
			} else {
				$editors[] = 'SHP_Image_Editor_GD';
			}
		}

		return $editors;
	},
	15 // After Imagick prioritisation (priority 9) but before default.
);


/* ==========================================================================
   6. KEEP WEBP OUTPUT AS WEBP
   ========================================================================== */

/**
 * Prevent WordPress from silently converting WebP → JPEG when generating
 * responsive image sub-sizes.
 *
 * @param  array  $formats   Source-MIME → output-MIME map.
 * @param  string $filename  Source image filename.
 * @param  string $mime_type Source MIME type.
 * @return array             Modified format map.
 */
function shp_webp_keep_output_format( $formats, $filename, $mime_type ) {
	if ( 'image/webp' === $mime_type ) {
		$formats['image/webp'] = 'image/webp';
	}
	return $formats;
}
add_filter( 'image_editor_output_format', 'shp_webp_keep_output_format', 10, 3 );


/* ==========================================================================
   7. MARK WEBP AS A DISPLAYABLE IMAGE
   ========================================================================== */

/**
 * Tell WordPress that .webp files are displayable images so it generates
 * attachment metadata and shows the file in the media library.
 *
 * @param  bool   $result Whether the file is considered displayable.
 * @param  string $path   Absolute path to the file.
 * @return bool           True for WebP, original value for everything else.
 */
function shp_webp_is_displayable( $result, $path ) {
	if ( ! $result && 'webp' === strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) ) {
		return true;
	}
	return $result;
}
add_filter( 'file_is_displayable_image', 'shp_webp_is_displayable', 10, 2 );


/* ==========================================================================
   8. WEBP THUMBNAIL IN MEDIA LIBRARY
   ========================================================================== */

/**
 * Ensure WebP attachments always show a preview thumbnail in the media
 * library grid/list. When sub-sizes couldn't be generated, WordPress leaves
 * the sizes array empty and shows a broken preview; we fall back to the
 * original full-size URL.
 *
 * @param  array   $response   Attachment data for JavaScript.
 * @param  WP_Post $attachment Attachment post object.
 * @param  array   $meta       Attachment post meta.
 * @return array               Modified response.
 */
function shp_webp_media_thumbnail( $response, $attachment, $meta ) {
	if ( ! isset( $response['mime'] ) || 'image/webp' !== $response['mime'] ) {
		return $response;
	}

	if ( ! empty( $response['sizes'] ) ) {
		return $response; // Sub-sizes already generated — nothing to fix.
	}

	$response['sizes'] = array(
		'full' => array(
			'url'         => $response['url'],
			'width'       => isset( $response['width'] ) ? (int) $response['width'] : 800,
			'height'      => isset( $response['height'] ) ? (int) $response['height'] : 600,
			'orientation' => (
				isset( $response['height'], $response['width'] )
				&& (int) $response['height'] > (int) $response['width']
			) ? 'portrait' : 'landscape',
		),
	);

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'shp_webp_media_thumbnail', 10, 3 );


/* ==========================================================================
   9. SERVER CAPABILITY NOTICE (WP_DEBUG only)
   ========================================================================== */

/**
 * Show an admin notice when neither GD nor Imagick can process WebP,
 * so the site admin knows a server-level fix is required.
 * Hidden in production (only visible when WP_DEBUG === true).
 */
function shp_webp_server_notice() {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}

	$gd_ok      = function_exists( 'imagecreatefromwebp' ) && function_exists( 'imagewebp' );
	$imagick_ok = false;
	if ( class_exists( 'Imagick' ) ) {
		try {
			$imagick_ok = ! empty( Imagick::queryFormats( 'WEBP' ) );
		} catch ( Exception $e ) {
			$imagick_ok = false;
		}
	}

	if ( $gd_ok || $imagick_ok ) {
		return;
	}

	printf(
		'<div class="notice notice-warning is-dismissible"><p><strong>SmartHome Pro:</strong> %s</p></div>',
		esc_html__(
			'Neither GD nor Imagick on this server supports WebP. '
			. 'WebP files can be uploaded but responsive image sizes will not be generated. '
			. 'Enable WebP in GD (compiled with --with-webp) or install the Imagick PHP extension.',
			'smarthome-pro'
		)
	);
}
add_action( 'admin_notices', 'shp_webp_server_notice' );
