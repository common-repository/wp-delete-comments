<?php
/*
	Plugin Name: WP Delete Comments
	Description: WP Delete Comments allows you to delete your Pending, Approved, Spam, Trash or All of your comments.
	Version: 1.0
	Author: Embark Code
	Author URI: https://embarkcode.com
*/


/**
* EC WP Delete Comments Admin Init
*/
add_action( 'admin_init', 'ec_wp_delete_comments_admin_init' );

function ec_wp_delete_comments_admin_init() {
    // register a new setting for "ec_wp_delete_comments" page
    register_setting( 'ec_wp_delete_comments', 'ec_wp_delete_comments_options' );
}
    
/**
* EC WP Delete Comments Custom Option and Settings
* EC WP Delete Comments Callback Functions
*/
function ec_wp_delete_comments_section_developers_cb( $args ) {

}

function ec_wp_delete_comments_select_cb( $args ) {
    ?>
    <select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="<?php echo esc_attr( $args['label_for'] ); ?>">
        <?php foreach ($args['options'] as $key => $value): ?>
            <option value="<?php echo $key; ?>" ><?php echo $value; ?></option>
        <?php endforeach ?>
    </select>
    <?php
}

function ec_wp_delete_comments_label_cb( $args ) {
    ?>
    <div><?php echo $args['value']; ?></div>
    <?php
}

/**
* EC WP Delete Comments Admin Menu
*/
add_action('admin_menu', 'ec_wp_delete_comments_admin_menu');

function ec_wp_delete_comments_admin_menu()
{
    add_menu_page(
        'WP Delete Comments',
        'WP Delete Comments',
        'manage_options',
        'ec_wp_delete_comments',
		'ec_wp_delete_comments_options_page_html',
		'dashicons-admin-comments'
    );
}

function ec_wp_delete_comments_options_page_html()
{
    // check user capabilities
    if (!current_user_can('delete_posts')) {
        die('You dont have access to this page.');
    }

    // add error/update messages
    if ( isset( $_POST['ec_wp_delete_comments_comments_type'] ) ) {
		global $wpdb;
		
		$comment_type = $_POST['ec_wp_delete_comments_comments_type'];

		if($comment_type == 'hold') {
			$wpdb->query("DELETE from wp_comments WHERE comment_approved = '0'");
		}

		else if($comment_type == 'approve') {
			$wpdb->query("DELETE from wp_comments WHERE comment_approved = '1'");
		}

		else if($comment_type == 'spam') {
			$wpdb->query("DELETE from wp_comments WHERE comment_approved = 'spam'");
		}

		else if($comment_type == 'trash') {
			$wpdb->query("DELETE from wp_comments WHERE comment_approved = 'trash'");
		}

		else if($comment_type == 'all') {
			$wpdb->query("DELETE from wp_comments");
		}

		// add settings saved message with the class of "updated"
        add_settings_error( 'ec_wp_delete_comments_messages', 'ec_wp_delete_comments_message', __( 'Your comments have been deleted successfully.', 'ec_wp_delete_comments' ), 'updated' );
    }
    
    // show error/update messages
	settings_errors( 'ec_wp_delete_comments_messages' );

	// register a new section in the "ec_wp_delete_comments" page
    add_settings_section(
        'ec_wp_delete_comments_section_1',
        __( 'Comments Count', 'ec_wp_delete_comments' ),
        'ec_wp_delete_comments_section_developers_cb',
        'ec_wp_delete_comments'
	);

	add_settings_field(
        'ec_wp_delete_comments_hold_comments',
		__( 'Total Unapproved comments', 'ec_wp_delete_comments' ),
		'ec_wp_delete_comments_label_cb',
		'ec_wp_delete_comments',
        'ec_wp_delete_comments_section_1',
		[
            'value' => get_comments(['count' => true, 'status' => 'hold'])
        ]
	);

	add_settings_field(
        'ec_wp_delete_comments_approve_comments',
		__( 'Total Approved comments', 'ec_wp_delete_comments' ),
		'ec_wp_delete_comments_label_cb',
		'ec_wp_delete_comments',
        'ec_wp_delete_comments_section_1',
		[
            'value' => get_comments(['count' => true, 'status' => 'approve'])
        ]
	);

	add_settings_field(
        'ec_wp_delete_comments_spam_comments',
		__( 'Total Spam comments', 'ec_wp_delete_comments' ),
		'ec_wp_delete_comments_label_cb',
		'ec_wp_delete_comments',
        'ec_wp_delete_comments_section_1',
		[
            'value' => get_comments(['count' => true, 'status' => 'spam'])
        ]
	);

	add_settings_field(
        'ec_wp_delete_comments_trash_comments',
		__( 'Total Trash comments', 'ec_wp_delete_comments' ),
		'ec_wp_delete_comments_label_cb',
		'ec_wp_delete_comments',
        'ec_wp_delete_comments_section_1',
		[
            'value' => get_comments(['count' => true, 'status' => 'trash'])
        ]
	);

	add_settings_field(
        'ec_wp_delete_comments_all_comments',
		__( 'Total All comments', 'ec_wp_delete_comments' ),
		'ec_wp_delete_comments_label_cb',
		'ec_wp_delete_comments',
        'ec_wp_delete_comments_section_1',
		[
            'value' => get_comments(['count' => true, 'status' => 'all'])
        ]
	);
	
    // register a new section in the "ec_wp_delete_comments" page
    add_settings_section(
        'ec_wp_delete_comments_section_2',
        __( 'Bulk Delete all you comments', 'ec_wp_delete_comments' ),
        'ec_wp_delete_comments_section_developers_cb',
        'ec_wp_delete_comments'
	);

	add_settings_field(
        'ec_wp_delete_comments_comments_type',
        __( 'Choose comment type', 'ec_wp_delete_comments' ),
        'ec_wp_delete_comments_select_cb',
        'ec_wp_delete_comments',
        'ec_wp_delete_comments_section_2',
        [
            'label_for' => 'ec_wp_delete_comments_comments_type',
            'class' => 'ec_wp_delete_comments_row',
            'options' => [
                'hold' => 'Unapproved',
                'approve' => 'Approved',
                'spam' => 'Spam',
                'trash' => 'Trash',
                'all' => 'All',
            ]
        ]
    );
	
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form method="post">
            <?php
            // output security fields for the registered setting "ec_wp_delete_comments"
            settings_fields( 'ec_wp_delete_comments' );
            // output setting sections and their fields
            // (sections are registered for "ec_wp_delete_comments", each field is registered to a specific section)
            do_settings_sections( 'ec_wp_delete_comments' );
            // output save settings button
            submit_button( 'Delete comments !!! You will lose your comments FOREVER...' );
            ?>
        </form>
    </div>
    <?php
}