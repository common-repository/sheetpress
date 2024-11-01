<?php

// Create a helper function for easy SDK access.
function she_fs() {
    global $she_fs;

    if ( ! isset( $she_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $she_fs = fs_dynamic_init( array(
            'id'                  => '1678',
            'slug'                => 'sheetpress',
            'type'                => 'plugin',
            'public_key'          => 'pk_cd77255e4479298a16d4206cabdaf',
            'is_premium'          => false,
            'has_addons'          => false,
            'has_paid_plans'      => false,
            'menu'                => array(
                'slug'           => 'Sheetpress_manage_your_content_page',
                'account'        => false,
                'support'        => false,
            ),
        ) );
    }

    return $she_fs;
}

// Init Freemius.
she_fs();
// Signal that SDK was initiated.
do_action( 'she_fs_loaded' );

function she_fs_custom_connect_message_on_update(
        $message,
        $user_first_name,
        $plugin_title,
        $user_login,
        $site_link,
        $freemius_link
    ) {
        return sprintf(
            __( 'Hey %1$s' ) . ',<br>' .
            __( 'Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'sheetpress' ),
            $user_first_name,
            '<b>' . $plugin_title . '</b>',
            '<b>' . $user_login . '</b>',
            $site_link,
            $freemius_link
        );
    }

    she_fs()->add_filter('connect_message_on_update', 'she_fs_custom_connect_message_on_update', 10, 6);
	she_fs()->add_filter('connect_message', 'she_fs_custom_connect_message_on_update', 10, 6);

