=== Optin Comment Notifications ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: comment, comments, notifications, email, commenting, coffee2code
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.6
Tested up to: 5.1
Stable tag: 1.4

Allows users to opt into receiving a notification email whenever a comment is made to the site.

== Description ==

This plugin adds a checkbox to the profile page for users which allows them to opt into receiving a notification email whenever a comment is made to the site.

If a comment goes into moderation, only users who have the ability to manage comments on the site will receive the moderation notification email.

By default, all users of the site have the ability to subscribe to notifications about comments. A filter is provided to facilitate use of code to customize the feature's availability to users.

Note: a "user" is a person with an actual login account for the site. The plugin does not facilitate permitting visitors who do not have an account on the site to be able to subscribe to all comments.

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/optin-comment-notifications/) | [Plugin Directory Page](https://wordpress.org/plugins/optin-comment-notifications/) | [GitHub](https://github.com/coffee2code/optin-comment-notifications/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Install via the built-in WordPress plugin installer. Or download and unzip `optin-comment-notifications.zip` inside the plugins directory for your site (typically `wp-content/plugins/`)
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. Users wishing to opt into receiving notifications for new comments should visit their profile page and check the checkbox labeled "Email me whenever a comment is submitted to the site."


== Screenshots ==

1. A screenshot of the checkbox added to user profiles.


== Frequently Asked Questions ==

= Who can sign up to receive notifications about comments to the site? =

Any user account on the site can sign up for comment notifications. Comments that go into moderation will only trigger notifications to users who can moderate comments. Visitors who do not have an account on the site cannot make use of the plugin to subscribe to comments.

= How do I sign up to receive notifications? =

On your profile page, there is a checkbox next to "New Comment Emails" that is labeled "Email me whenever a comment is submitted to the site.". Check the checkbox and click the button to update your profile. If you wish to discontinue receiving such notifications, simply uncheck the checkbox and save the change.

= Does this plugin include unit tests? =

Yes.

= How can I restrict the plugin to only offer the ability to subscribe to all comments to administrators and editors? =

Use the 'c2c_optin_comment_notifications_has_cap' filter to customize the capability as needed. The following code can be used or adapted for that purpose. Such code should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain).

`
/**
 * Only permits administrators and editors to subscribe to comment notifications.
 *
 * @param bool  $default The default. Default true.
 * @param array $caps    Array of user capabilities.
 * @return string
 */
function restrict_optin_comment_notifications( $default, $caps ) {
	// Only administrators and editors can subscribe to all comments.
	return !! array_intersect(
		wp_get_current_user()->roles,      // Get current user's roles.
		array( 'administrator', 'editor' ) // Roles to allow to subscribe to all comments.
	);
}
add_filter( 'c2c_optin_comment_notifications_has_cap', 'restrict_optin_comment_notifications', 10, 2 );
`


= Can an administrator configure the setting for another user? =

Yes. Users with the 'edit_users' capability (administrators, basically) and can edit the profile of another user can configure this plugin for that user. The checkbox is labeled "Email this user whenever a comment is submitted to the site.".


== Changelog ==

= 1.4 (2019-03-26) =
* New: Add CHANGELOG.md file and move all but most recent changelog entries into it
* Change: Initialize plugin on 'plugins_loaded' action instead of on load
* Change: Merge `do_init()` into `init()`
* Change: Note compatibility through WP 5.1+
* Change: Update copyright date (2019)
* Change: Update License URI to be HTTPS
* Change: Split paragraph in README.md's "Support" section into two

= 1.3 (2018-05-07) =
* Bugfix: Ensure comment notifications are sent even if core's 'comments_notify' or 'notify_moderator' settings is false
* Change: Don't notify users of spam comments
* New: Add README.md
* New: Add GitHub link to readme
* Unit tests:
    * Change: Test notifications by invoking `wp_new_comment_notify_postauthor()` rather than plugin class method
    * Change: Add and improve tests relating to 'notify_moderator'
    * Change: Remove unnecessary mocking of posts
    * Change: Minor whitespace tweaks to bootstrap
    * Change: Use correct header @package name in bootstrap
* Change: Note compatibility through WP 4.9+
* Change: Update copyright date (2018)

= 1.2 (2017-01-04) =
* New: Permit admins (or more specifically, those who can 'edit_users') to control the setting for other users.
    * Add new capability 'c2c_subscribe_to_all_comments_edit_others'
    * Add 'c2c_optin_comment_notifications_has_cap_edit_others' filter to allow customizing capability for editing setting for other users
    * Show checkbox via 'personal_options' action instead of 'profile_personal_options'
    * Hook 'edit_user_profile_update' to potentially save the setting when another user is being edited
* Change: Enable more error output for unit tests.
* Change: Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable.
* Change: Minor tweaks to code documentation.
* Change: Note compatibility through WP 4.7+.
* Change: Remove support for WordPress older than 4.6 (should still work for earlier versions)
* Change: Update copyright date (2017).
* Change: Update installation instruction to prefer built-in installer over .zip file

_Full changelog is available in [CHANGELOG.md](https://github.com/coffee2code/optin-comment-notifications/blob/master/CHANGELOG.md)._


== Upgrade Notice ==

= 1.4 =
Minor update: tweaked plugin initialization, dropped compatibility with WP older than 4.6, noted compatibility through WP 5.1+, created CHANGELOG.md to store historical changelog outside of readme.txt, and updated copyright date (2019)

= 1.3 =
Recommended update: bugfix to ensure advertised functionality works even if related core settings are disabled, prevented notifications for spam comments, added README.md, noted compatibility through WP 4.9+, and updated copyright date (2018)

= 1.2 =
Minor feature update: added ability for admins to edit the setting for other users, updated unit test bootstrap file, noted compatibility through WP 4.7+, and updated copyright date (2017)

= 1.1 =
Minor update: improve support for localization; verified compatibility through WP 4.4; removed compatibility with WP earlier than 4.1; updated copyright date (2016)

= 1.0 =
Initial public release.
