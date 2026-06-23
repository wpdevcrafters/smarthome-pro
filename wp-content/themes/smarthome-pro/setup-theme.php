<?php
/**
 * SmartHome Pro — One-time Setup Script
 *
 * This script bootstraps WordPress and:
 *   1. Activates the ACF Pro plugin (if not already active)
 *   2. Activates the Contact Form 7 plugin (if not already active)
 *   3. Activates the SmartHome Pro theme
 *   4. Creates the "SmartHome Pro" landing page with the correct template
 *   5. Sets that page as the static front page
 *
 * HOW TO RUN:
 *   Open in your browser:  http://localhost/rebininfotech/wp-content/themes/smarthome-pro/setup-theme.php
 *   Or via CLI:             C:\xampp\php\php.exe setup-theme.php
 *
 * After running, DELETE this file for security.
 *
 * @package SmartHome_Pro
 */

// ---------- Bootstrap WordPress ----------
$wp_load = dirname( __DIR__, 3 ) . '/wp-load.php';

if ( ! file_exists( $wp_load ) ) {
    echo "ERROR: Could not locate wp-load.php at: {$wp_load}\n";
    exit( 1 );
}

require_once $wp_load;

// Only admins (or CLI) can run this.
if ( php_sapi_name() !== 'cli' && ! current_user_can( 'manage_options' ) ) {
    wp_die( 'You must be logged in as an administrator to run this script.', 'Permission Denied', array( 'response' => 403 ) );
}

// Helper to determine if running in CLI.
$is_cli = ( php_sapi_name() === 'cli' );
$br     = $is_cli ? "\n" : '<br>';
$hr     = $is_cli ? str_repeat( '-', 60 ) . "\n" : '<hr>';

if ( ! $is_cli ) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SmartHome Pro Setup</title>';
    echo '<style>body{font-family:Inter,sans-serif;max-width:720px;margin:40px auto;padding:20px;background:#f8fafc;color:#0f172a;}';
    echo 'h1{color:#2563eb;}.ok{color:#10b981;font-weight:600;}.warn{color:#f59e0b;font-weight:600;}.err{color:#ef4444;font-weight:600;}</style>';
    echo '</head><body><h1>🏠 SmartHome Pro Setup</h1>';
}

echo "{$hr}";
echo "SmartHome Pro — Theme Setup Script{$br}";
echo "{$hr}{$br}";


// ==========================================================================
// 1. ACTIVATE ACF PRO PLUGIN
// ==========================================================================

echo "<strong>Step 1:</strong> Checking ACF Pro plugin...{$br}";

// Find the ACF plugin — the folder name may have version suffix.
$acf_plugin_file = '';
$plugin_dir      = WP_PLUGIN_DIR;

// Scan for ACF plugin directory.
$plugin_dirs = glob( $plugin_dir . '/advanced-custom-fields-pro*', GLOB_ONLYDIR );
if ( ! empty( $plugin_dirs ) ) {
    $acf_folder = basename( $plugin_dirs[0] );
    $acf_main   = $acf_folder . '/acf.php';
    if ( file_exists( $plugin_dir . '/' . $acf_main ) ) {
        $acf_plugin_file = $acf_main;
    }
}

// Fallback: standard ACF directory name.
if ( empty( $acf_plugin_file ) && file_exists( $plugin_dir . '/advanced-custom-fields-pro/acf.php' ) ) {
    $acf_plugin_file = 'advanced-custom-fields-pro/acf.php';
}

// Also check for free ACF version.
if ( empty( $acf_plugin_file ) && file_exists( $plugin_dir . '/advanced-custom-fields/acf.php' ) ) {
    $acf_plugin_file = 'advanced-custom-fields/acf.php';
}

if ( ! empty( $acf_plugin_file ) ) {
    if ( ! is_plugin_active( $acf_plugin_file ) ) {
        $result = activate_plugin( $acf_plugin_file );
        if ( is_wp_error( $result ) ) {
            echo "<span class='err'>✗ Failed to activate ACF: " . esc_html( $result->get_error_message() ) . "</span>{$br}";
        } else {
            echo "<span class='ok'>✓ ACF Pro activated successfully ({$acf_plugin_file})</span>{$br}";
        }
    } else {
        echo "<span class='ok'>✓ ACF Pro is already active ({$acf_plugin_file})</span>{$br}";
    }
} else {
    echo "<span class='warn'>⚠ ACF Pro plugin not found in plugins directory. Please install it manually.</span>{$br}";
}

echo $br;


// ==========================================================================
// 2. ACTIVATE CONTACT FORM 7 PLUGIN
// ==========================================================================

echo "<strong>Step 2:</strong> Checking Contact Form 7 plugin...{$br}";

$cf7_plugin_file = 'contact-form-7/wp-contact-form-7.php';

if ( file_exists( $plugin_dir . '/' . $cf7_plugin_file ) ) {
    if ( ! is_plugin_active( $cf7_plugin_file ) ) {
        $result = activate_plugin( $cf7_plugin_file );
        if ( is_wp_error( $result ) ) {
            echo "<span class='err'>✗ Failed to activate CF7: " . esc_html( $result->get_error_message() ) . "</span>{$br}";
        } else {
            echo "<span class='ok'>✓ Contact Form 7 activated successfully</span>{$br}";
        }
    } else {
        echo "<span class='ok'>✓ Contact Form 7 is already active</span>{$br}";
    }
} else {
    echo "<span class='warn'>⚠ Contact Form 7 plugin not found. Please install it manually.</span>{$br}";
}

echo $br;


// ==========================================================================
// 3. ACTIVATE THE SMARTHOME PRO THEME
// ==========================================================================

echo "<strong>Step 3:</strong> Activating SmartHome Pro theme...{$br}";

$current_theme = get_option( 'stylesheet' );

if ( $current_theme === 'smarthome-pro' ) {
    echo "<span class='ok'>✓ SmartHome Pro theme is already active</span>{$br}";
} else {
    // Check if theme exists.
    $theme = wp_get_theme( 'smarthome-pro' );
    if ( $theme->exists() ) {
        switch_theme( 'smarthome-pro' );
        echo "<span class='ok'>✓ SmartHome Pro theme activated successfully</span>{$br}";
    } else {
        echo "<span class='err'>✗ SmartHome Pro theme not found in themes directory</span>{$br}";
    }
}

echo $br;


// ==========================================================================
// 4. CREATE THE LANDING PAGE
// ==========================================================================

echo "<strong>Step 4:</strong> Creating landing page...{$br}";

// Check if a page with the template already exists.
$existing_pages = get_posts(
    array(
        'post_type'      => 'page',
        'posts_per_page' => 1,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'page-smarthome-pro.php',
        'post_status'    => array( 'publish', 'draft', 'private' ),
    )
);

if ( ! empty( $existing_pages ) ) {
    $page_id = $existing_pages[0]->ID;
    echo "<span class='ok'>✓ Landing page already exists: \"{$existing_pages[0]->post_title}\" (ID: {$page_id})</span>{$br}";

    // Make sure it's published.
    if ( $existing_pages[0]->post_status !== 'publish' ) {
        wp_update_post(
            array(
                'ID'          => $page_id,
                'post_status' => 'publish',
            )
        );
        echo "<span class='ok'>  → Published the page</span>{$br}";
    }
} else {
    // Create the landing page.
    $page_id = wp_insert_post(
        array(
            'post_title'   => 'SmartHome Pro',
            'post_name'    => 'smarthome-pro',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id() ?: 1,
            'meta_input'   => array(
                '_wp_page_template' => 'page-smarthome-pro.php',
            ),
        )
    );

    if ( is_wp_error( $page_id ) ) {
        echo "<span class='err'>✗ Failed to create page: " . esc_html( $page_id->get_error_message() ) . "</span>{$br}";
    } else {
        echo "<span class='ok'>✓ Landing page created: \"SmartHome Pro\" (ID: {$page_id})</span>{$br}";
    }
}

echo $br;


// ==========================================================================
// 5. SET LANDING PAGE AS FRONT PAGE
// ==========================================================================

echo "<strong>Step 5:</strong> Setting landing page as static front page...{$br}";

if ( ! empty( $page_id ) && ! is_wp_error( $page_id ) ) {
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $page_id );
    echo "<span class='ok'>✓ Front page set to \"SmartHome Pro\" (ID: {$page_id})</span>{$br}";
} else {
    echo "<span class='warn'>⚠ Could not set front page — page creation may have failed</span>{$br}";
}

echo $br;


// ==========================================================================
// 6. CREATE A BLOG PAGE (if none exists)
// ==========================================================================

echo "<strong>Step 6:</strong> Setting up blog page...{$br}";

$blog_page = get_option( 'page_for_posts' );

if ( ! $blog_page || ! get_post( $blog_page ) ) {
    $blog_page_id = wp_insert_post(
        array(
            'post_title'  => 'Blog',
            'post_name'   => 'blog',
            'post_content' => '',
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_author' => get_current_user_id() ?: 1,
        )
    );

    if ( ! is_wp_error( $blog_page_id ) ) {
        update_option( 'page_for_posts', $blog_page_id );
        echo "<span class='ok'>✓ Blog page created and set (ID: {$blog_page_id})</span>{$br}";
    }
} else {
    echo "<span class='ok'>✓ Blog page already configured (ID: {$blog_page})</span>{$br}";
}

echo $br;


// ==========================================================================
// 7. VERIFY ACF FIELD GROUPS ARE REGISTERED
// ==========================================================================

echo "<strong>Step 7:</strong> Verifying ACF field groups...{$br}";

if ( function_exists( 'acf_get_field_groups' ) ) {
    // Force re-initialization of ACF fields.
    // The theme's acf-fields.php registers fields on 'acf/init' hook.
    // Since we just activated the theme, we need to make sure the hook fires.
    if ( ! did_action( 'acf/init' ) ) {
        // Manually trigger field registration.
        if ( file_exists( get_template_directory() . '/inc/acf-fields.php' ) ) {
            require_once get_template_directory() . '/inc/acf-fields.php';
            if ( function_exists( 'smarthome_pro_acf_options_pages' ) ) {
                smarthome_pro_acf_options_pages();
            }
            if ( function_exists( 'smarthome_pro_acf_register_fields' ) ) {
                smarthome_pro_acf_register_fields();
            }
        }
    }

    $field_groups = acf_get_field_groups();
    $shp_groups   = array();

    foreach ( $field_groups as $group ) {
        if ( strpos( $group['key'], 'group_shp_' ) === 0 ) {
            $shp_groups[] = $group;
        }
    }

    if ( ! empty( $shp_groups ) ) {
        echo "<span class='ok'>✓ Found " . count( $shp_groups ) . " SmartHome Pro ACF field group(s):</span>{$br}";
        foreach ( $shp_groups as $group ) {
            $field_count = count( acf_get_fields( $group['key'] ) );
            echo "  → {$group['title']} ({$field_count} fields){$br}";
        }
    } else {
        echo "<span class='warn'>⚠ No SmartHome Pro field groups found. ";
        echo "They will register on the next page load after theme activation.</span>{$br}";
    }
} else {
    echo "<span class='warn'>⚠ ACF functions not available. Make sure ACF Pro is activated, ";
    echo "then refresh this page.</span>{$br}";
}

echo $br;


// ==========================================================================
// 8. FLUSH REWRITE RULES
// ==========================================================================

echo "<strong>Step 8:</strong> Flushing rewrite rules...{$br}";
flush_rewrite_rules();
echo "<span class='ok'>✓ Rewrite rules flushed</span>{$br}";

echo $br;


// ==========================================================================
// SUMMARY
// ==========================================================================

echo "{$hr}";
echo "<strong>Setup Complete!</strong>{$br}{$br}";

$home_url  = home_url( '/' );
$admin_url = admin_url( '/' );

echo "→ Front page:    {$home_url}{$br}";
echo "→ Admin panel:   {$admin_url}{$br}";
echo "→ Theme settings: " . admin_url( 'admin.php?page=smarthome-pro-settings' ) . "{$br}";

if ( ! empty( $page_id ) && ! is_wp_error( $page_id ) ) {
    echo "→ Edit landing page: " . admin_url( "post.php?post={$page_id}&action=edit" ) . "{$br}";
}

echo "{$br}";
echo "<span class='warn'>⚠ IMPORTANT: Delete this setup file after use for security:</span>{$br}";
echo "   " . __FILE__ . "{$br}";

echo "{$hr}";

if ( ! $is_cli ) {
    echo '</body></html>';
}
