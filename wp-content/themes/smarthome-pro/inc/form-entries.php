<?php
/**
 * SmartHome Pro Form Entries Database and Admin Interface
 *
 * Saves Contact Form 7 submissions to a custom database table
 * and displays them under a custom submenu "Entries" inside Contact Form 7.
 *
 * @package SmartHome_Pro
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * 1. Create Custom Table for Form Entries.
 */
function shp_create_entries_table() {
	global $wpdb;
	$table_name      = $wpdb->prefix . 'shp_form_entries';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		form_id bigint(20) NOT NULL,
		form_title varchar(255) NOT NULL,
		submitted_data longtext NOT NULL,
		submission_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action( 'admin_init', 'shp_create_entries_table' );
add_action( 'rest_api_init', 'shp_create_entries_table' );

/**
 * Skip actual email sending on local host, forcing CF7 to return success status (mail_sent).
 */
add_filter( 'wpcf7_skip_mail', '__return_true' );

/**
 * 2. Save Contact Form 7 Submission to Database.
 */
add_action( 'wpcf7_before_send_mail', 'shp_save_cf7_entry', 10, 3 );
function shp_save_cf7_entry( $contact_form, &$abort, $submission ) {
	if ( ! $submission ) {
		return;
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'shp_form_entries';

	$form_id     = $contact_form->id();
	$form_title  = $contact_form->title();
	$posted_data = $submission->get_posted_data();

	// Exclude standard Contact Form 7 internal parameters from logging
	$exclude_keys  = array( '_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag', '_wpcf7_container_post', '_wpcf7_posted_data_hash' );
	$filtered_data = array_diff_key( $posted_data, array_flip( $exclude_keys ) );

	$wpdb->insert(
		$table_name,
		array(
			'form_id'         => $form_id,
			'form_title'      => $form_title,
			'submitted_data'  => wp_json_encode( $filtered_data ),
			'submission_date' => current_time( 'mysql' ),
		),
		array( '%d', '%s', '%s', '%s' )
	);
}

/**
 * 3. Prevent Duplicate Newsletter Submissions.
 * Uses validation filter hooks in Contact Form 7.
 */
add_filter( 'wpcf7_validate_email', 'shp_validate_newsletter_duplicates', 10, 2 );
add_filter( 'wpcf7_validate_email*', 'shp_validate_newsletter_duplicates', 10, 2 );
function shp_validate_newsletter_duplicates( $result, $tag ) {
	$tag  = new WPCF7_FormTag( $tag );
	$name = $tag->name;

	if ( 'your-email' === $name || 'email-address' === $name ) {
		$value = isset( $_POST[ $name ] ) ? trim( sanitize_email( wp_unslash( $_POST[ $name ] ) ) ) : '';

		$submission = WPCF7_Submission::get_instance();
		if ( $submission && ! empty( $value ) ) {
			$contact_form = $submission->get_contact_form();
			$form_id      = $contact_form->id();

			// Newsletter Form ID is 63
			if ( 63 === (int) $form_id ) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'shp_form_entries';

				// Query to see if this exact email exists in entries under form 63
				$exists = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM $table_name WHERE form_id = %d AND submitted_data LIKE %s",
						63,
						'%' . $wpdb->esc_like( '"your-email":"' . $value . '"' ) . '%'
					)
				);

				if ( $exists > 0 ) {
					$result->invalidate( $tag, 'This email address is already subscribed!' );
				}
			}
		}
	}
	return $result;
}

/**
 * 4. Register Submenu under Contact Form 7.
 */
add_action( 'admin_menu', 'shp_register_cf7_entries_submenu', 25 );
function shp_register_cf7_entries_submenu() {
	add_submenu_page(
		'wpcf7',
		'Form Entries',
		'Entries',
		'wpcf7_read_contact_forms',
		'wpcf7-entries',
		'shp_display_cf7_entries_page'
	);
}

/**
 * 5. Handle CSV Export for Form Entries.
 */
add_action( 'admin_init', 'shp_handle_csv_export' );
function shp_handle_csv_export() {
	if ( isset( $_GET['page'] ) && 'wpcf7-entries' === $_GET['page'] && isset( $_GET['action'] ) && 'export_csv' === $_GET['action'] ) {
		if ( ! current_user_can( 'wpcf7_read_contact_forms' ) ) {
			wp_die( 'You do not have permission to access this page.' );
		}

		$form_id = isset( $_GET['form_id'] ) ? (int) $_GET['form_id'] : 0;
		if ( ! $form_id ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'shp_form_entries';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

		$query  = "SELECT * FROM $table_name WHERE form_id = %d";
		$params = array( $form_id );

		if ( ! empty( $search ) ) {
			$query   .= ' AND submitted_data LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		$query   .= ' ORDER BY submission_date DESC';
		$results  = $wpdb->get_results( $wpdb->prepare( $query, $params ) );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=form-entries-' . $form_id . '-' . gmdate( 'Y-m-d' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );

		if ( 7 === $form_id ) {
			fputcsv( $output, array( 'First Name', 'Last Name', 'Email Address', 'Phone Number', 'Date' ) );
			foreach ( $results as $row ) {
				$data = json_decode( $row->submitted_data, true );
				fputcsv(
					$output,
					array(
						isset( $data['first-name'] ) ? $data['first-name'] : '',
						isset( $data['last-name'] ) ? $data['last-name'] : '',
						isset( $data['email-address'] ) ? $data['email-address'] : '',
						isset( $data['phone-number'] ) ? $data['phone-number'] : '',
						$row->submission_date,
					)
				);
			}
		} elseif ( 63 === $form_id ) {
			fputcsv( $output, array( 'Email Address', 'Date' ) );
			foreach ( $results as $row ) {
				$data = json_decode( $row->submitted_data, true );
				fputcsv(
					$output,
					array(
						isset( $data['your-email'] ) ? $data['your-email'] : '',
						$row->submission_date,
					)
				);
			}
		} else {
			fputcsv( $output, array( 'Form Fields (JSON)', 'Date' ) );
			foreach ( $results as $row ) {
				fputcsv( $output, array( $row->submitted_data, $row->submission_date ) );
			}
		}

		fclose( $output );
		exit;
	}
}

/**
 * 6. Render Submenu Page HTML.
 */
function shp_display_cf7_entries_page() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'shp_form_entries';
	$form_id    = isset( $_GET['form_id'] ) ? (int) $_GET['form_id'] : 0;

	echo '<div class="wrap">';

	if ( ! $form_id ) {
		// SCREEN 1: List forms and their submission counts
		echo '<h1 class="wp-heading-inline">Form Entries</h1>';
		echo '<hr class="wp-header-end">';

		$query   = "SELECT form_id, form_title, COUNT(*) as count FROM $table_name GROUP BY form_id";
		$results = $wpdb->get_results( $query );

		echo '<table class="wp-list-table widefat fixed striped table-view-list mt-4" style="margin-top: 15px;">';
		echo '<thead><tr><th>Name</th><th>Count</th></tr></thead>';
		echo '<tbody>';

		if ( empty( $results ) ) {
			echo '<tr><td colspan="2">No entries found. Submissions from the React website will populate here.</td></tr>';
		} else {
			foreach ( $results as $row ) {
				$form_url = admin_url( 'admin.php?page=wpcf7-entries&form_id=' . $row->form_id );
				echo '<tr>';
				echo '<td><strong><a class="row-title" href="' . esc_url( $form_url ) . '">' . esc_html( $row->form_title ) . '</a></strong></td>';
				echo '<td><a href="' . esc_url( $form_url ) . '">' . esc_html( $row->count ) . '</a></td>';
				echo '</tr>';
			}
		}

		echo '</tbody>';
		echo '<tfoot><tr><th>Name</th><th>Count</th></tr></tfoot>';
		echo '</table>';

	} else {
		// SCREEN 2: List entries for a specific form
		if ( isset( $_POST['action'] ) && 'delete' === $_POST['action'] && isset( $_POST['entry_id'] ) ) {
			if ( ! current_user_can( 'wpcf7_read_contact_forms' ) ) {
				wp_die( 'Permission denied.' );
			}
			$entry_ids = array_map( 'intval', (array) $_POST['entry_id'] );
			if ( ! empty( $entry_ids ) ) {
				$placeholders = implode( ',', array_fill( 0, count( $entry_ids ), '%d' ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE id IN ($placeholders)", $entry_ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				echo '<div class="notice notice-success is-dismissible"><p>Selected entries successfully deleted.</p></div>';
			}
		}

		$form_title = $wpdb->get_var( $wpdb->prepare( "SELECT form_title FROM $table_name WHERE form_id = %d LIMIT 1", $form_id ) );
		if ( ! $form_title ) {
			$form_title = 'Form #' . $form_id;
		}

		echo '<h1 class="wp-heading-inline">Entries for ' . esc_html( $form_title ) . '</h1>';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=wpcf7-entries' ) ) . '" class="page-title-action">Back to List</a>';
		echo '<hr class="wp-header-end">';

		$search = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : ( isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '' );

		$query  = "SELECT * FROM $table_name WHERE form_id = %d";
		$params = array( $form_id );

		if ( ! empty( $search ) ) {
			$query   .= ' AND submitted_data LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		$query   .= ' ORDER BY submission_date DESC';
		$results  = $wpdb->get_results( $wpdb->prepare( $query, $params ) );

		$export_url = admin_url( 'admin.php?page=wpcf7-entries&action=export_csv&form_id=' . $form_id );
		if ( ! empty( $search ) ) {
			$export_url = add_query_arg( 's', $search, $export_url );
		}

		// Search form
		echo '<form method="post" action="" style="float: right; margin-bottom: 15px;">';
		echo '<input type="search" name="s" value="' . esc_attr( $search ) . '" placeholder="Search entries..."> ';
		echo '<input type="submit" class="button" value="Search">';
		echo '</form>';

		echo '<form method="post" action="">';
		echo '<input type="hidden" name="form_id" value="' . esc_attr( $form_id ) . '">';

		// Bulk Actions & Export
		echo '<div class="alignleft actions bulkactions" style="margin-bottom: 15px;">';
		echo '<select name="action">';
		echo '<option value="-1">Bulk actions</option>';
		echo '<option value="delete">Delete</option>';
		echo '</select> ';
		echo '<input type="submit" class="button action" value="Apply"> ';
		echo '<a href="' . esc_url( $export_url ) . '" class="button button-secondary" style="margin-left: 10px;">Export CSV</a>';
		echo '</div>';

		echo '<table class="wp-list-table widefat fixed striped table-view-list">';
		echo '<thead><tr>';
		echo '<td class="manage-column check-column"><input type="checkbox" id="cb-select-all-1"></td>';
		if ( 7 === $form_id ) {
			echo '<th>First Name</th><th>Last Name</th><th>Email Address</th><th>Phone Number</th><th>Date</th>';
		} elseif ( 63 === $form_id ) {
			echo '<th>Email Address</th><th>Date</th>';
		} else {
			echo '<th>Form Data</th><th>Date</th>';
		}
		echo '</tr></thead>';

		echo '<tbody>';
		if ( empty( $results ) ) {
			$cols = ( 7 === $form_id ) ? 6 : ( ( 63 === $form_id ) ? 3 : 3 );
			echo '<tr><td colspan="' . (int) $cols . '">No entries found.</td></tr>';
		} else {
			foreach ( $results as $row ) {
				$data = json_decode( $row->submitted_data, true );
				echo '<tr>';
				echo '<th class="check-column"><input type="checkbox" name="entry_id[]" value="' . (int) $row->id . '"></th>';
				if ( 7 === $form_id ) {
					echo '<td>' . esc_html( isset( $data['first-name'] ) ? $data['first-name'] : '' ) . '</td>';
					echo '<td>' . esc_html( isset( $data['last-name'] ) ? $data['last-name'] : '' ) . '</td>';
					echo '<td>' . esc_html( isset( $data['email-address'] ) ? $data['email-address'] : '' ) . '</td>';
					echo '<td>' . esc_html( isset( $data['phone-number'] ) ? $data['phone-number'] : '' ) . '</td>';
					echo '<td>' . esc_html( $row->submission_date ) . '</td>';
				} elseif ( 63 === $form_id ) {
					echo '<td>' . esc_html( isset( $data['your-email'] ) ? $data['your-email'] : '' ) . '</td>';
					echo '<td>' . esc_html( $row->submission_date ) . '</td>';
				} else {
					echo '<td><pre>' . esc_html( wp_json_encode( $data, JSON_PRETTY_PRINT ) ) . '</pre></td>';
					echo '<td>' . esc_html( $row->submission_date ) . '</td>';
				}
				echo '</tr>';
			}
		}
		echo '</tbody>';

		echo '<tfoot><tr>';
		echo '<td class="check-column"><input type="checkbox" id="cb-select-all-2"></td>';
		if ( 7 === $form_id ) {
			echo '<th>First Name</th><th>Last Name</th><th>Email Address</th><th>Phone Number</th><th>Date</th>';
		} elseif ( 63 === $form_id ) {
			echo '<th>Email Address</th><th>Date</th>';
		} else {
			echo '<th>Form Data</th><th>Date</th>';
		}
		echo '</tr></tfoot>';
		echo '</table>';

		echo '<script>
		jQuery(document).ready(function($){
			$("#cb-select-all-1, #cb-select-all-2").change(function(){
				var isChecked = $(this).prop("checked");
				$("input[name=\'entry_id[]\']").prop("checked", isChecked);
				$("#cb-select-all-1, #cb-select-all-2").prop("checked", isChecked);
			});
		});
		</script>';

		echo '</form>';
	}

	echo '</div>';
}
