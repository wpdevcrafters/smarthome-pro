<?php
/**
 * SVG Upload Support
 *
 * Enables SVG file uploads in the WordPress Media Library.
 * - Adds SVG to the list of allowed MIME types.
 * - Fixes the media-library thumbnail so SVGs render as images.
 * - Sanitizes SVG files on upload to strip scripts and event handlers.
 *
 * @package SmartHome_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ==========================================================================
   1. ALLOW SVG MIME TYPE
   ========================================================================== */

/**
 * Add SVG to the list of allowed upload MIME types.
 *
 * @param  array $mimes Existing allowed MIME types.
 * @return array        Modified MIME types including SVG.
 */
function shp_allow_svg_upload( $mimes ) {
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'shp_allow_svg_upload' );


/* ==========================================================================
   2. FIX SVG MIME TYPE CHECK (WP 4.7.1+ extra validation)
   ========================================================================== */

/**
 * Override the extra MIME-type check introduced in WP 4.7.1 for SVG files.
 * Without this, WordPress rejects SVG uploads even when the MIME is allowed.
 *
 * @param  array  $data     Validated data array.
 * @param  string $file     Full path to uploaded file.
 * @param  string $filename Original filename.
 * @param  array  $mimes    Allowed MIME types.
 * @return array            Corrected validated data.
 */
function shp_fix_svg_mime_check( $data, $file, $filename, $mimes ) {
	$file_ext  = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	$file_type = wp_check_filetype( $filename, $mimes );

	if ( 'svg' === $file_ext || 'svgz' === $file_ext ) {
		$data['ext']  = $file_type['ext'];
		$data['type'] = $file_type['type'];
	}

	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'shp_fix_svg_mime_check', 10, 4 );


/* ==========================================================================
   3. SVG THUMBNAIL IN MEDIA LIBRARY
   ========================================================================== */

/**
 * Display SVG files as images in the media library grid and list views.
 * WordPress uses a generic icon for SVGs by default; this overrides that.
 *
 * @param  string $response   JSON-encoded attachment data.
 * @param  object $attachment WP_Post object for the attachment.
 * @param  array  $meta       Attachment meta.
 * @return string             Modified JSON response.
 */
function shp_svg_media_thumbnail( $response, $attachment, $meta ) {
	if ( 'image/svg+xml' === $response['mime'] ) {
		// Use the attachment URL directly as the thumbnail src.
		$response['sizes'] = array(
			'full' => array(
				'url'         => $response['url'],
				'width'       => 800,
				'height'      => 600,
				'orientation' => 'landscape',
			),
		);
	}
	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'shp_svg_media_thumbnail', 10, 3 );


/**
 * Output inline CSS in the WP admin to render SVG thumbnails correctly
 * in both the grid view and the attachment detail panel.
 */
function shp_svg_admin_styles() {
	echo '<style>
		/* Media library grid — SVG thumbnails */
		.attachment-preview[data-mime-type="image/svg+xml"] .thumbnail img,
		.attachment[data-type="image"] .thumbnail img[src$=".svg"],
		.attachment[data-type="image"] .thumbnail img[src$=".svgz"] {
			width: 100%;
			height: 100%;
			object-fit: contain;
		}

		/* Media library list view */
		tr.type-image td.column-icon img[src$=".svg"],
		tr.type-image td.column-icon img[src$=".svgz"] {
			width: 60px;
			height: 60px;
			object-fit: contain;
		}

		/* Attachment detail sidebar thumbnail */
		.attachment-details .thumbnail img[src$=".svg"],
		.attachment-details .thumbnail img[src$=".svgz"] {
			max-width: 100%;
			height: auto;
		}
	</style>';
}
add_action( 'admin_head', 'shp_svg_admin_styles' );


/* ==========================================================================
   4. SANITIZE SVG ON UPLOAD (Security)
   ========================================================================== */

/**
 * Sanitize an SVG file after it is uploaded.
 *
 * Strips <script> elements, javascript: URIs, and on* event-handler
 * attributes so that uploaded SVGs cannot be used for XSS attacks.
 *
 * @param  array $file Array of upload data including 'tmp_name' and 'type'.
 * @return array       The same upload data (file is sanitized in-place).
 */
function shp_sanitize_svg_upload( $file ) {
	// Only process SVG files.
	if ( ! isset( $file['type'] ) || 'image/svg+xml' !== $file['type'] ) {
		return $file;
	}

	$svg_content = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( false === $svg_content ) {
		$file['error'] = esc_html__( 'Could not read the uploaded SVG file.', 'smarthome-pro' );
		return $file;
	}

	$sanitized = shp_do_sanitize_svg( $svg_content );

	if ( false === $sanitized ) {
		$file['error'] = esc_html__( 'The uploaded SVG file could not be parsed. Please ensure it is a valid SVG.', 'smarthome-pro' );
		return $file;
	}

	// Write the sanitized content back to the temp file.
	file_put_contents( $file['tmp_name'], $sanitized ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'shp_sanitize_svg_upload' );


/**
 * Core SVG sanitization routine.
 *
 * Uses PHP's DOMDocument to parse the SVG and walk every element,
 * removing dangerous tags, attributes, and URIs.
 *
 * @param  string $svg_content Raw SVG string.
 * @return string|false        Sanitized SVG string, or false on parse error.
 */
function shp_do_sanitize_svg( $svg_content ) {
	// Suppress libxml errors — we handle them manually.
	$previous_errors = libxml_use_internal_errors( true );

	$dom = new DOMDocument();
	$dom->formatOutput = true;

	// Load as XML; SVG is XML-based.
	$loaded = $dom->loadXML( $svg_content, LIBXML_NONET );

	libxml_clear_errors();
	libxml_use_internal_errors( $previous_errors );

	if ( ! $loaded ) {
		return false;
	}

	// ---- Tags that are never allowed ----
	$forbidden_tags = array(
		'script',
		'use',       // can load external resources
		'set',
		'animate',
		'animatetransform',
		'animatemotion',
		'animatecolor',
		'discard',
	);

	// ---- Attribute prefixes / values that are never allowed ----
	$forbidden_attr_prefixes = array( 'on' ); // onclick, onload, onmouseover …

	// Walk and collect all elements first (avoid modifying DOM during traversal).
	$all_elements = iterator_to_array(
		( new DOMXPath( $dom ) )->query( '//*' ),
		false
	);

	foreach ( $all_elements as $element ) {
		// Remove forbidden tags entirely.
		if ( in_array( strtolower( $element->localName ), $forbidden_tags, true ) ) {
			$element->parentNode->removeChild( $element );
			continue;
		}

		// Scrub attributes on allowed tags.
		$attrs_to_remove = array();

		if ( $element->hasAttributes() ) {
			foreach ( $element->attributes as $attr ) {
				$attr_name  = strtolower( $attr->nodeName );
				$attr_value = $attr->nodeValue;

				// Remove on* event handlers.
				foreach ( $forbidden_attr_prefixes as $prefix ) {
					if ( str_starts_with( $attr_name, $prefix ) ) {
						$attrs_to_remove[] = $attr->nodeName;
						continue 2;
					}
				}

				// Remove href / xlink:href that use javascript: URIs.
				if (
					in_array( $attr_name, array( 'href', 'xlink:href', 'src', 'action', 'formaction' ), true ) &&
					preg_match( '/^\s*javascript:/i', $attr_value )
				) {
					$attrs_to_remove[] = $attr->nodeName;
					continue;
				}

				// Remove style attributes containing javascript: or expression().
				if ( 'style' === $attr_name ) {
					if (
						preg_match( '/javascript:/i', $attr_value ) ||
						preg_match( '/expression\s*\(/i', $attr_value )
					) {
						$attrs_to_remove[] = $attr->nodeName;
						continue;
					}
				}
			}
		}

		foreach ( $attrs_to_remove as $attr_name ) {
			$element->removeAttribute( $attr_name );
		}
	}

	// Export sanitized SVG back to string.
	$sanitized = $dom->saveXML( $dom->documentElement );

	return $sanitized;
}


/* ==========================================================================
   5. SVG DIMENSIONS FALLBACK
   ========================================================================== */

/**
 * Prevent PHP warnings for SVG "image size" calls.
 * WordPress cannot determine pixel dimensions of SVGs via getimagesize(),
 * so we return a safe default when the file is an SVG.
 *
 * @param  array|false $size  Result of getimagesize(), or false.
 * @param  string      $file  Path to the file.
 * @return array|false        Original size or safe SVG defaults.
 */
function shp_svg_getimagesize( $size, $file ) {
	if ( false === $size && shp_is_svg_file( $file ) ) {
		// Return a generic size tuple so downstream code doesn't break.
		return array( 0, 0, IMAGETYPE_UNKNOWN, '', 'mime' => 'image/svg+xml' );
	}
	return $size;
}
// Hook the actual getimagesize fallback for SVG files.
// NOTE: Do NOT filter getimagesize_mimes_to_exts to return empty — that
// breaks WordPress's MIME validation map for ALL image types (including WebP).

/**
 * Helper: check if a file path points to an SVG.
 *
 * @param  string $file File path.
 * @return bool
 */
function shp_is_svg_file( $file ) {
	$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return in_array( $ext, array( 'svg', 'svgz' ), true );
}
