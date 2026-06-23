<?php
/**
 * WebP Server Diagnostic
 *
 * Run this page in your browser to see exactly what your server supports.
 * URL: http://localhost/rebininfotech/wp-content/themes/smarthome-pro/webp-diagnostic.php
 *
 * DELETE THIS FILE after you are done diagnosing.
 *
 * @package SmartHome_Pro
 */

// Very basic protection — localhost only.
if ( ! in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ], true ) ) {
	http_response_code( 403 );
	exit( 'Access denied.' );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>WebP Diagnostic</title>
<style>
body { font-family: monospace; padding: 24px; background: #f9f9f9; }
h1 { font-size: 1.4rem; }
h2 { font-size: 1.1rem; margin-top: 28px; }
table { border-collapse: collapse; margin: 12px 0; }
td, th { padding: 6px 16px; text-align: left; border: 1px solid #ddd; }
th { background: #eee; }
.yes { color: #1a7f37; font-weight: bold; }
.no  { color: #cf222e; font-weight: bold; }
.warn { color: #9a6700; }
pre { background: #fff; border: 1px solid #ddd; padding: 12px; }
</style>
</head>
<body>
<h1>WebP Server Diagnostic</h1>

<?php

function diag_row( $label, $value, $ok = null ) {
	$class = '';
	if ( $ok === true )  $class = ' class="yes"';
	if ( $ok === false ) $class = ' class="no"';
	echo "<tr><td>$label</td><td$class>$value</td></tr>\n";
}

echo '<h2>PHP</h2><table><tr><th>Check</th><th>Result</th></tr>';
diag_row( 'PHP Version', PHP_VERSION );
diag_row( 'OS', PHP_OS );
echo '</table>';

// ── GD ──────────────────────────────────────────────────────────────────────
echo '<h2>GD Extension</h2><table><tr><th>Check</th><th>Result</th></tr>';
$gd_loaded = extension_loaded( 'gd' );
diag_row( 'extension_loaded("gd")', $gd_loaded ? 'YES' : 'NO', $gd_loaded );

if ( $gd_loaded ) {
	$gd_info = gd_info();
	diag_row( 'GD Version', $gd_info['GD Version'] ?? 'unknown' );
	$gd_webp = ! empty( $gd_info['WebP Support'] );
	diag_row( 'gd_info WebP Support', $gd_webp ? 'YES' : 'NO', $gd_webp );

	$img_types   = imagetypes();
	$webp_bit    = defined( 'IMG_WEBP' ) ? IMG_WEBP : 32;
	$img_webp    = ( $img_types & $webp_bit ) !== 0;
	diag_row( 'imagetypes() & IMG_WEBP', $img_webp ? "YES (bit=$webp_bit)" : 'NO', $img_webp );

	$fn_from = function_exists( 'imagecreatefromwebp' );
	$fn_to   = function_exists( 'imagewebp' );
	$fn_str  = function_exists( 'imagecreatefromstring' );
	diag_row( 'imagecreatefromwebp()', $fn_from ? 'exists' : 'MISSING', $fn_from );
	diag_row( 'imagewebp()',            $fn_to   ? 'exists' : 'MISSING', $fn_to );
	diag_row( 'imagecreatefromstring()', $fn_str ? 'exists' : 'MISSING', $fn_str );
}
echo '</table>';

// ── Imagick ─────────────────────────────────────────────────────────────────
echo '<h2>Imagick Extension</h2><table><tr><th>Check</th><th>Result</th></tr>';
$imagick = class_exists( 'Imagick' );
diag_row( 'class_exists("Imagick")', $imagick ? 'YES' : 'NO', $imagick );
if ( $imagick ) {
	try {
		$formats     = Imagick::queryFormats( 'WEBP' );
		$ick_webp    = ! empty( $formats );
		diag_row( 'Imagick WebP formats', $ick_webp ? implode( ', ', $formats ) : 'NONE', $ick_webp );
	} catch ( Exception $e ) {
		diag_row( 'Imagick::queryFormats()', 'ERROR: ' . $e->getMessage(), false );
	}
}
echo '</table>';

// ── MIME detection ───────────────────────────────────────────────────────────
echo '<h2>MIME Detection</h2><table><tr><th>Check</th><th>Result</th></tr>';
$finfo_ok = function_exists( 'finfo_open' );
$exif_ok  = function_exists( 'exif_imagetype' );
$mime_ok  = function_exists( 'mime_content_type' );
diag_row( 'finfo extension',      $finfo_ok ? 'Available' : 'NOT available', $finfo_ok );
diag_row( 'exif extension',       $exif_ok  ? 'Available' : 'NOT available', $exif_ok );
diag_row( 'mime_content_type()',  $mime_ok  ? 'Available' : 'NOT available' );
echo '</table>';

// ── Live round-trip test ──────────────────────────────────────────────────────
echo '<h2>Live WebP Round-Trip Test</h2>';

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'shp_webp_test_' . time() . '.webp';
$created = false;

if ( function_exists( 'imagecreatetruecolor' ) && function_exists( 'imagewebp' ) ) {
	$img = imagecreatetruecolor( 100, 60 );
	$bg  = imagecolorallocate( $img, 37, 99, 235 );
	imagefill( $img, 0, 0, $bg );

	$created = @imagewebp( $img, $tmp, 80 );
	imagedestroy( $img );

	if ( $created ) {
		echo '<p class="yes">✓ Created test WebP file at: ' . htmlspecialchars( $tmp ) . '</p>';
	} else {
		echo '<p class="no">✗ imagewebp() returned false — GD cannot write WebP files on this server.</p>';
	}
} else {
	echo '<p class="no">✗ imagecreatetruecolor or imagewebp not available — skipping creation test.</p>';
}

if ( $created && file_exists( $tmp ) ) {
	echo '<table><tr><th>Method</th><th>Result</th></tr>';

	// 1. imagecreatefromwebp
	if ( function_exists( 'imagecreatefromwebp' ) ) {
		$res = @imagecreatefromwebp( $tmp );
		$ok  = ( $res instanceof GdImage ) || is_resource( $res );
		diag_row( 'imagecreatefromwebp()', $ok ? 'OK' : 'FAILED', $ok );
		if ( $ok ) { imagedestroy( $res ); }
	}

	// 2. imagecreatefromstring
	if ( function_exists( 'imagecreatefromstring' ) ) {
		$fc  = file_get_contents( $tmp );
		$res = @imagecreatefromstring( $fc );
		$ok  = ( $res instanceof GdImage ) || is_resource( $res );
		diag_row( 'imagecreatefromstring()', $ok ? 'OK' : 'FAILED', $ok );
		if ( $ok ) { imagedestroy( $res ); }
	}

	// 3. exif_imagetype
	if ( function_exists( 'exif_imagetype' ) ) {
		$type    = @exif_imagetype( $tmp );
		$webp_t  = defined( 'IMAGETYPE_WEBP' ) ? IMAGETYPE_WEBP : 18;
		$ok      = ( $type === $webp_t );
		diag_row( 'exif_imagetype()', $ok ? "IMAGETYPE_WEBP ($type) ✓" : "$type (expected $webp_t)", $ok );
	}

	// 4. finfo_file
	if ( function_exists( 'finfo_open' ) ) {
		$fi   = finfo_open( FILEINFO_MIME_TYPE );
		$mime = finfo_file( $fi, $tmp );
		finfo_close( $fi );
		$ok   = ( 'image/webp' === $mime );
		diag_row( 'finfo_file() MIME', $ok ? "$mime ✓" : "$mime (expected image/webp)", $ok );
	}

	// 5. mime_content_type
	if ( function_exists( 'mime_content_type' ) ) {
		$mime = @mime_content_type( $tmp );
		$ok   = ( 'image/webp' === $mime );
		diag_row( 'mime_content_type()', $ok ? "$mime ✓" : "$mime (expected image/webp)", $ok );
	}

	echo '</table>';
	@unlink( $tmp );
}

// ── GD info dump ────────────────────────────────────────────────────────────
echo '<h2>Full gd_info() Dump</h2><pre>';
if ( $gd_loaded ) {
	print_r( gd_info() );
} else {
	echo 'GD not loaded.';
}
echo '</pre>';

echo '<hr><p class="warn">⚠ Delete this file (<code>webp-diagnostic.php</code>) after use!</p>';
?>
</body>
</html>
