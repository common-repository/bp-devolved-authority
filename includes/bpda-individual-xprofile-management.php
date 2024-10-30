<?php
/**
 * The BP Devolved Authority individual xprofile management.
 * Forked from the BP Devolved Xprofile plugin
 * From: 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Disallow direct HTTP access.

/**
 * Base class that WordPress uses to register and initialize plugin.
 */
class BPDA_XProfile_Individual {

    /**
     * String to prefix option names, settings, etc. in shared spaces.
     *
     * Some WordPress data storage areas are basically one globally
     * shared namespace. For example, names of options saved in WP's
     * options table must be globally unique. When saving data in any
     * such shared space, we need to prefix the name we use.
     *
     * @var string
     */
    const prefix = 'bp_dxp_';

    /**
     * Entry point for the WordPress framework into plugin code.
     *
     * This is the method called when WordPress loads the plugin file.
     * It is responsible for "registering" the plugin's main functions
     * with the {@see https://codex.wordpress.org/Plugin_API WordPress Plugin API}.
     *
     * @uses add_action()
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     *
     * @return void
     */
    public static function register () {
        add_action( 'bp_include', array( __CLASS__, 'bp_include' ) );
        add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
        add_action( 'bp_members_admin_user_metaboxes', array( __CLASS__, 'bp_members_admin_user_metaboxes' ), 10, 2 );
        add_action( 'bp_members_admin_update_user', array( __CLASS__, 'bp_members_admin_update_user' ), 10, 4 );
        //add_action( 'xprofile_field_after_sidebarbox', array( __CLASS__, 'xprofile_field_delegate_metabox' ) );

        add_filter( 'user_has_cap', array( __CLASS__, 'user_has_cap' ), 5, 4 ); // priority 5 so other plugins can override
        add_filter( 'bp_current_user_can', array( __CLASS__, 'bp_current_user_can' ), 10, 4 );

        register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
    }

    /**
     * Method to run when the plugin is deactivated by a user in the
     * WordPress Dashboard admin screen.
     *
     * @return void
     */
    public static function deactivate () {
        // TODO
    }

    /**
     * Loads BuddyPress-specific codebase.
     *
     * @see https://codex.buddypress.org/plugindev/checking-buddypress-is-active/
     */
    public static function bp_include () {
    }

    /**
     * Filters a user capability.
     *
     * Used for adding the `edit_user_delegates` capability to all
     * users granted the `edit_users` capability, by default. This
     * method runs very early when hooked, so by default other plugins
     * that want to hook the `user_has_cap` filter and use the default
     * priority of 10 will automatically override this setting.
     *
     * @param array $allcaps
     * @param array $caps
     * @param array $args
     * @param WP_User $user
     *
     * return array
     */
    public static function user_has_cap ( $allcaps, $caps, $args, $user ) {
        // If you can `edit_users` you can `edit_user_delegates`, too.
        if ( isset( $allcaps['edit_users'] ) ) {
            $allcaps['edit_user_delegates'] = true;
        }

        return $allcaps;
    }

    /**
     * Filters the BuddyPress capability checks.
     *
     * Used for allowing delegates to edit their charge's XProfile.
     *
     * @param bool   $retval
     * @param string $capability
     * @param int    $blog_id
     * @param array  $args
     *
     * @return bool
     */
    public static function bp_current_user_can ( $retval, $capability, $blog_id, $args ) {
        // BuddyPress XProfile edit relies on the `bp_moderate`
        // capability. There are code comments in BuddyPress that say
        // this might become more granular later on but for now that
        // is the one and only capability BuddyPress actually checks.
        if ( 'bp_moderate' === $capability
            && isset( $_GET['page'] ) && 'bp-profile-edit' === $_GET['page']
            && isset( $_GET['user_id'] )
        ) {
            if ( in_array( get_current_user_id(), self::delegates_for( (int) $_GET['user_id'] ) ) ) {
                // Current user is a delegate for the given user, so
                // should be granted permission to edit the profile.
                return true;
            }
        }

        return $retval;
    }

    /**
     * Whether or not a given user is the delegate of other users.
     *
     * @param WP_User|int $user
     *
     * @return bool
     */
    public static function user_is_delegate ( $user ) {
        $id = ( is_int( $user ) ) ? absint( $user ) : $user->ID;
        $q = new WP_User_Query( array(
            'meta_key' => self::prefix . 'user_delegate',
            'meta_value' => $id
        ) );
        $r = $q->get_results();
        return ! empty( $r );
    }

    /**
     * Gets delegates of the given user.
     *
     * @param int $user_id
     *
     * @return int[]
     */
    public static function delegates_for ( $user_id ) {
        return bp_get_user_meta( $user_id, self::prefix . 'user_delegate' );
    }

    /**
     * Gets the users the given user has been delegated to.
     *
     * @param int $user_id
     *
     * @return WP_User[]
     */
    public static function delegated_to ( $user_id ) {
        $q = new WP_User_Query( array(
            'meta_key' => self::prefix . 'user_delegate',
            'meta_value' => $user_id
        ) );
        return $q->get_results();
    }

    /**
     * Adds the Devolved Profiles menu item to the WP Dashboard admin menu.
     *
     * @link https://developer.wordpress.org/reference/hooks/admin_menu/
     */
    public static function admin_menu () {
        if ( ! self::user_is_delegate( get_current_user_id() ) ) {
            return; // bail if this user is not a delegate
        }
        add_submenu_page(
            'profile.php',
            sanitize_text_field(esc_attr__( 'Devolved Profiles' , 'bp-devolved-authority')),
            sanitize_text_field(esc_attr__( 'Devolved Profiles' , 'bp-devolved-authority')),
            'read', // WordPress users with `subscriber` role can be delegates.
            self::prefix . 'delegated-profiles',
            array( __CLASS__, 'renderEditDevolvedProfiles' )
        );
    }

    /**
     * Registers the Delegation metaboxes on an XProfile edit screen.
     *
     * @param bool $is_self_profile Whether or not the loaded profile is for the current user.
     * @param WP_User $wp_user
     */
    public static function bp_members_admin_user_metaboxes ( $is_self_profile, $wp_user ) {
        if ( ! bp_is_active( 'xprofile' ) ) {
            return; // Bail if Extended Profile component is not active.
        }
        
        // The meta box will enumerate users, which is sensitive and should only be permitted
        // to users with the `list_users` capability. Similarly, it implies that a user might
        // edit the properties associated with a user, so we check the `edit_user_delegates`
        // capability before registering the metabox, as well.
        if ( current_user_can( 'list_users' ) && current_user_can( 'edit_user_delegates' ) )  {
            add_meta_box(
                self::prefix . 'admin_user_delegate',
                sanitize_text_field(esc_attr_x('Delegation', 'members user-admin edit screen', 'bp-devolved-authority')),
                array( __CLASS__, 'renderDelegationMetabox' ),
                get_current_screen()->id,
                'side',
                'default',
                array( $wp_user )
            );
        }
    }

    /**
     * Prints the "Delegation" metabox HTML.
     *
     * @param WP_User $wp_user
     */
    public static function renderDelegationMetabox ( $wp_user ) {
        $delegate_ids = self::delegates_for( $wp_user->ID );
        $delegates = array_map( 'get_userdata', $delegate_ids );
?>
<p>
    <strong><?php print sanitize_text_field(esc_attr__( 'Current Delegates', 'bp-devolved-authority' )); ?></strong>
</p>
<?php if ( ! empty( $delegates ) ) : ?>
<ul>
    <?php foreach ( $delegates as $d ) : ?>
    <li>
        <label>
            <input id="<?php print esc_attr( self::prefix . 'user_delegate-' . $d->ID ); ?>"
                name="<?php print esc_attr( self::prefix . 'user_delegate' ); ?>[]"
                value="<?php print esc_attr( $d->ID ); ?>"
                type="checkbox"
                checked="checked"
            />
            <?php print esc_html( $d->display_name ); ?> (<?php print esc_html( $d->user_login ); ?>)
        </label>
        <a href="<?php print esc_attr( admin_url( "user-edit.php?user_id={$d->ID}" ) ); ?>"><?php sanitize_text_field(esc_attr_e( 'Profile', 'bp-devolved-authority' )); ?></a>
        |
        <a style="vertical-align: middle;" href="<?php print esc_attr( admin_url( "users.php?page=bp-profile-edit&user_id={$d->ID}" ) ); ?>"><?php sanitize_text_field(esc_attr__( 'Extended', 'bp-devolved-authority' )); ?></a>
    </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
<p>
    <label>
        <strong><?php print sanitize_text_field(esc_attr__( 'Add Delegate', 'bp-devolved-authority' )); ?></strong>
        <select id="<?php print esc_attr( self::prefix . 'add_delegate' ); ?>"
            name="<?php print esc_attr( self::prefix . 'add_delegate' ); ?>"
        >
            <option value=""></option><!-- Do not add a user Delegate. -->
        <?php foreach ( get_users() as $user ) : if ( $user->ID !== $wp_user->ID && ! in_array( $user->ID, $delegate_ids ) ) :  ?>
            <option value="<?php print esc_attr( $user->ID ); ?>"><?php print esc_attr( $user->display_name ); ?> (<?php print esc_html( $user->user_login ); ?>)</option>
        <?php endif; endforeach; ?>
        </select>
    </label>
</p>
<p class="description"><?php print sanitize_text_field(esc_attr__( 'Delegates are other users that are allowed to modify Extended Profile fields belonging to this user.', 'bp-devolved-authority')); ?></p>
<?php
    }

    /**
     * Renders the Edit Devolved Profiles screen.
     *
     * @todo Visual display of this page should be nicer. Use a WP List Table?
     */
    public static function renderEditDevolvedProfiles () {
        $delegates = self::delegated_to( get_current_user_id() );
?>
<div id="delegated-profiles-page">
    <h1 class="wp-heading-inline">Devolved Profiles</h1>
    <hr class="wp-header-end" />
    <ul>
        <?php foreach ( $delegates as $d ) : ?>
        <li><a href="<?php print esc_attr( admin_url( "users.php?page=bp-profile-edit&user_id={$d->ID}" ) ); ?>"><?php print esc_attr( $d->display_name ); ?> (<?php print esc_html( $d->user_login ); ?>)</a></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php
    }

    /**
     * Saves a profile's user delegate when updating their XProfile.
     *
     * @param string $doaction Current bulk action being processed.
     * @param int    $user_id  The user ID to update.
     * @param array  $req      Current $_REQUEST global.
     * @param string $redirect Determined redirect url to send user to.
     */
    public static function bp_members_admin_update_user ( $doaction, $user_id, $req, $redirect ) {
        if ( ! current_user_can( 'edit_user_delegates' ) ) {
            return;
        }

        // Get current delegates.
        $curr_delegates = self::delegates_for( $user_id );

        // Find delegates to keep.
        $keep_delegates = array();
        if ( ! empty( $req[ self::prefix . 'user_delegate' ] ) ) {
            foreach ( $req[ self::prefix . 'user_delegate' ] as $d ) {
                $keep_delegates[] = $d;
            }
        }

        // Delete delegates not being kept.
        foreach ( $curr_delegates as $d ) {
            if ( ! in_array( $d, $keep_delegates ) ) {
                bp_delete_user_meta( $user_id, self::prefix . 'user_delegate', $d );
            }
        }

        // Add a new user delegate.
        if ( ! empty( $req[ self::prefix . 'add_delegate' ] ) ) {
            // A user cannot be their own delegate,
            // a delegate cannot be added twice,
            // and a delegate ID must be a valid WordPress user ID.
            $add = (int) $req[ self::prefix . 'add_delegate' ];
            if ( $add !== $user_id && ! in_array( $add, $curr_delegates ) && get_userdata( $add ) ) {
                add_user_meta( $user_id, self::prefix . 'user_delegate', $add );
            }
        }
    }

    /**
     * Renders the XProfile field's Delegate metabox.
     */
    public static function xprofile_field_delegate_metabox () {
?>
<div id="<?php print esc_html( self::prefix ); ?>metabox" class="postbox">
    <h2><?php sanitize_text_field(esc_html_e( 'Delegate', 'bp-devolved-authority' )); ?></h2>
   <div class="inside">
       <p>Test</p>
   </div>
</div>
<?php
    }

}

BPDA_XProfile_Individual::register();
