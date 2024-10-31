<?php
/**
 * Plugin Name: Optin Comment Notifications
 * Version:     1.4
 * Plugin URI:  http://coffee2code.com/wp-plugins/optin-comment-notifications/
 * Author:      Scott Reilly
 * Author URI:  http://coffee2code.com/
 * Text Domain: optin-comment-notifications
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: Allows users to opt into receiving a notification whenever a comment is made to the site.
 *
 * Compatible with WordPress 4.6 through 5.1+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/optin-comment-notifications/
 *
 * @package Optin_Comment_Notifications
 * @author  Scott Reilly
 * @version 1.4
 */

/**
 * TODO:
 * - Change email content via 'comment_notification_text' to replace text that
 *   refer to "your post" with "the post", since it's not the post author being
 *   notified. Also, to omit links to post author functionality (trash, spam)
 *   for users who don't have 'edit_comment' capabilities.
 * - Actually, since 'comment_notification_text' is the content sent to all
 *   recipients, including the post author who legitimately needs the comment
 *   handling links, this plugin is better off not relying on
 *   wp_notify_postauthor(). Instead, hook into 'comment_notification_headers'.
 *   Reuse the headers passed to it. Replicate code to determine subject, and
 *   customize copy of code that formulates content to refer to "the post" and
 *   to not include admin links. (Future version can define the admin and
 *   non-admin versions of the email, then decide on per-user basis which they
 *   get.) Then send the emails itself. (Might need to add check so post author
 *   isn't notified twice about a comment to their own post since
 *   wp_notify_postauthor() is now separate and will do its own thing.)
 * - Add method last_emailed() that stores the list of emails returned by
 *   add_comment_notification_recipients(). Primarily for unit testing.
 */

/*
	Copyright (c) 2014-2019 by Scott Reilly (aka coffee2code)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'c2c_Optin_Comment_Notifications' ) ) :

class c2c_Optin_Comment_Notifications {

	/**
	 * Meta key name for flag to indicate if user has opted into being notified of
	 * all new comments.
	 *
	 * @var string
	 * @access public
	 */
	public static $option_name = 'c2c_comment_notification_optin';

	/**
	 * Value for the meta value to indicate the user has opted into comment notifications.
	 *
	 * @var string
	 * @access public
	 */
	public static $yes_option_value = '1';

	/**
	 * Name for capability that permits user to subscribe to all comments.
	 *
	 * @var string
	 * @access public
	 */
	public static $cap_name = 'c2c_subscribe_to_all_comments';

	/**
	 * Name for capability that permits user to edit setting to subscribe to all comments for other users.
	 *
	 * @var string
	 * @access public
	 */
	public static $cap_edit_others = 'c2c_subscribe_to_all_comments_edit_others';

	/**
	 * Original value passed to 'notify_post_author' filter.
	 *
	 * @since 1.3
	 * @var bool|null
	 */
	private static $orig_notify_post_author = null;

	/**
	 * Original value passed to 'notify_moderator' filter.
	 *
	 * @since 1.3
	 * @var bool|null
	 */
	private static $orig_notify_moderator = null;

	/**
	 * Returns version of the plugin.
	 *
	 * @since 1.0
	 */
	public static function version() {
		return '1.4';
	}

	/**
	 * Initializer
	 *
	 * @access public
	 */
	public static function init() {
		// Load textdomain
		load_plugin_textdomain( 'optin-comment-notifications' );

		// Restrict ability to subscribe to comments.
		add_filter( 'user_has_cap',                    array( __CLASS__, 'assign_subscribe_caps' ) );

		// Ensure notification emails can be sent in the first place.
		add_filter( 'notify_post_author',              array( __CLASS__, 'notify_post_author' ), 10, 2 );
		add_filter( 'notify_moderator',                array( __CLASS__, 'notify_moderator' ), 10, 2 );

		// Add users who opted to be notified.
		add_filter( 'comment_notification_recipients', array( __CLASS__, 'add_comment_notification_recipients' ), 10, 2 );
		add_filter( 'comment_moderation_recipients',   array( __CLASS__, 'add_comment_notification_recipients' ), 10, 2 );

		// Adds the checkbox to user profiles.
		add_action( 'personal_options',                array( __CLASS__, 'add_comment_notification_checkbox' ) );

		// Saves the user preference for comment notifications.
		add_action( 'personal_options_update',         array( __CLASS__, 'option_save' ) );
		add_action( 'edit_user_profile_update',        array( __CLASS__, 'option_save' ) );
	}

	/**
	 * Adjusts capabilities for administrators and editors to allow subscribing to
	 * all comments.
	 *
	 * All users have the capability by default.
	 *
	 * @access public
	 *
	 * @param  array $caps Array of user capabilities.
	 * @return array
	 */
	public static function assign_subscribe_caps( $caps ) {

		/**
		 * Filter the capability determining if comment notifications is available.
		 *
		 * @since 1.0
		 *
		 * @param bool  $default The default. Default true.
		 * @param array $caps    Array of user capabilities.
		 */
		$caps[ self::$cap_name ] = apply_filters( 'c2c_optin_comment_notifications_has_cap', true, $caps );


		remove_filter( 'user_has_cap', array( __CLASS__, 'assign_subscribe_caps' ) );
		$can_edit_others = current_user_can( 'edit_users' );
		add_filter( 'user_has_cap', array( __CLASS__, 'assign_subscribe_caps' ) );

		/**
		 * Filter the capability determining if user can edit comment notifications setting for other users.
		 *
		 * @since 1.2
		 *
		 * @param bool  $default The default. True if current user has 'edit_users' capability..
		 * @param array $caps    Array of user capabilities.
		 */
		$caps[ self::$cap_edit_others ] = apply_filters( 'c2c_optin_comment_notifications_has_cap_edit_others', $can_edit_others, $caps );

		return $caps;
	}

	/**
	 * Ensure that comment notifications are sent in the first place.
	 *
	 * The plugin basically amends 'comment_notification_recipients' to add more
	 * email addresses to the list notified of new comments. However, WP never
	 * gets that far if 'comments_notify' setting is not checked. Since the plugin
	 * should work regardless of that setting, it should filter its value to be
	 * true. It also needs to retain the original value to potentially remove post
	 * authors who haven't opted into comment notifications to be excluded (honor
	 * the core setting).
	 *
	 * @since 1.3
	 *
	 * @param bool $maybe_notify Should comment notifications be sent? Based on 'comments_notify' setting.
	 * @param int  $comment_id   The comment ID.
	 * @return bool Always true.
	 */
	public static function notify_post_author( $maybe_notify, $comment_id ) {
		// Maybe note of the original value so the post author can potentially be
		// omitted from the email list.
		self::$orig_notify_post_author = $maybe_notify;

		return true;
	}

	/**
	 * Ensure that moderation notifications are sent in the first place.
	 *
	 * The plugin basically amends 'comment_moderation_recipients' to add more
	 * email addresses to the list notified of new comments. However, WP never
	 * gets that far if 'moderation_notify' setting is not checked. Since the
	 * plugin should work regardless of that setting, it should filter its value
	 * to be true. It also needs to retain the original value to potentially remove
	 * moderators who haven't opted into comment notifications to be excluded
	 * (honor the core setting).
	 *
	 * @since 1.3
	 *
	 * @param bool $maybe_notify Should moderation notifications be sent? Based on 'moderation_notify' setting.
	 * @param int  $comment_id   The comment ID.
	 * @return bool Always true.
	 */
	public static function notify_moderator( $maybe_notify, $comment_id ) {
		// Maybe note of the original value so the moderators can potentially be
		// omitted from the email list.
		self::$orig_notify_moderator = $maybe_notify;

		return true;
	}

	/**
	 * Adds users who opted to be notified.
	 *
	 * @access public
	 *
	 * @param  array $emails     Array of email addresses to be notified.
	 * @param  int   $comment_id The comment ID for the comment just created.
	 * @return array
	 */
	public static function add_comment_notification_recipients( $emails, $comment_id ) {
		global $wpdb;

		// Get the comment.
		$comment = get_comment( $comment_id );

		// Don't notify users about spam.
		if ( 'spam' === $comment->comment_approved ) {
			return $emails;
		}

		// If the site has 'comments_notify' or 'moderation_notify' set to false, the
		// originally intended recipients can be removed from the email list.
		if ( false === self::$orig_notify_post_author || false === self::$orig_notify_moderator ) {
			// While it is possible $emails has been been filtered by other plugins and
			// contains more than just the core generated list of email address, they
			// aren't expecting to run in this situation, so can be discarded and only
			// users opted in via this plugin can be emailed.
			$emails = array();
		}

		self::$orig_notify_post_author = self::$orig_notify_moderator = null;

		// Get users who opted in to comment notifications.
		$blog_prefix = $wpdb->get_blog_prefix( get_current_blog_id() );
		$user_query = new WP_User_Query( array(
			'meta_key'   => $blog_prefix . self::$option_name,
			'meta_value' => self::$yes_option_value,
		) );

		if ( ! empty( $user_query->results ) ) {
			foreach ( $user_query->results as $user ) {
				// Don't notify a user about their own comment.
				if ( $user->ID == $comment->user_id ) {
					continue;
				}

				// Only notify users that currently have the capability to receive notifications.
				if ( ! user_can( $user->ID, self::$cap_name ) ) {
					continue;
				}

				// If the comment is in moderation, only notify users able to moderate comments.
				if ( '0' == $comment->comment_approved && ! user_can( $user->ID, 'moderate_comments' ) ) {
					continue;
				}

				// Add the email address unless it is already set to receive a notification.
				if ( ! in_array( $user->user_email, $emails ) ) {
					$emails[] = $user->user_email;
				}
			}
		}

		return $emails;
	}

	/**
	 * Adds the checkbox to user profiles to allow user to opt into receiving
	 * notifications for all comments.
	 *
	 * @access public
	 *
	 * @param WP_User $profileuser The WP_User object of the profile being viewed.
	 */
	public static function add_comment_notification_checkbox( $profileuser ) {
		$current_user = wp_get_current_user();
		$is_profile_page = ( $profileuser->ID == $current_user->ID );

		$cap_to_check = $is_profile_page ? self::$cap_name : self::$cap_edit_others;
		if ( ! current_user_can( $cap_to_check ) ) {
			return;
		}
		?>
		<tr>
			<th scope="row"><?php _e( 'New Comment Emails', 'optin-comment-notifications' ); ?></th>
			<td>
				<label for="<?php echo esc_attr( self::$option_name ); ?>">
					<?php printf(
						'<input name="%s" type="checkbox" id="%s" value="%s" %s />',
						esc_attr( self::$option_name ),
						esc_attr( self::$option_name ),
						esc_attr( self::$yes_option_value ),
						checked( get_user_option( self::$option_name, $profileuser->ID ), self::$yes_option_value, false )
					); ?>
					<?php $is_profile_page ?
						_e( 'Email me whenever a comment is submitted to the site.', 'optin-comment-notifications' ) :
						_e( 'Email this user whenever a comment is submitted to the site.', 'optin-comment-notifications' ); ?>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * Saves value of checkbox to allow user to opt into receiving
	 * notifications for all comments.
	 *
	 * @access public
	 *
	 * @param  int  $user_id The user ID.
	 * @return bool True if the option saved successfully.
	 */
	public static function option_save( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) || ! current_user_can( self::$cap_name ) ) {
			return false;
		}

		if ( isset( $_POST[ self::$option_name ] ) && self::$yes_option_value === $_POST[ self::$option_name ] ) {
			return update_user_option( $user_id, self::$option_name, self::$yes_option_value );
		} else {
			return delete_user_option( $user_id, self::$option_name );
		}
	}
} // c2c_Optin_Comment_Notifications

add_action( 'plugins_loaded', array( 'c2c_Optin_Comment_Notifications', 'init' ) );

endif; // end if !class_exists()
