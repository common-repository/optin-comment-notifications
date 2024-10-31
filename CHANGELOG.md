# Changelog

## 1.4 _(2019-03-26)_
* New: Add CHANGELOG.md file and move all but most recent changelog entries into it
* Change: Initialize plugin on 'plugins_loaded' action instead of on load
* Change: Merge `do_init()` into `init()`
* Change: Note compatibility through WP 5.1+
* Change: Update copyright date (2019)
* Change: Update License URI to be HTTPS
* Change: Split paragraph in README.md's "Support" section into two

## 1.3 _(2018-05-07)_
* Bugfix: Ensure comment notifications are sent even if core's `comments_notify` or `notify_moderator` settings is false
* Change: Don't notify users of spam comments
* New: Add README.md
* New: Add GitHub link to readme
* Unit tests:
    * Change: Test notifications by invoking `wp_new_comment_notify_postauthor()` rather than plugin class method
    * Change: Add and improve tests relating to `notify_moderator`
    * Change: Remove unnecessary mocking of posts
    * Change: Minor whitespace tweaks to bootstrap
    * Change: Use correct header @package name in bootstrap
* Change: Note compatibility through WP 4.9+
* Change: Update copyright date (2018)

## 1.2 _(2017-01-04)_
* New: Permit admins (or more specifically, those who can `edit_users`) to control the setting for other users.
    * Add new capability `c2c_subscribe_to_all_comments_edit_others`
    * Add `c2c_optin_comment_notifications_has_cap_edit_others` filter to allow customizing capability for editing setting for other users
    * Show checkbox via `personal_options` action instead of `profile_personal_options`
    * Hook `edit_user_profile_update` to potentially save the setting when another user is being edited
* Change: Enable more error output for unit tests.
* Change: Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable.
* Change: Minor tweaks to code documentation.
* Change: Note compatibility through WP 4.7+.
* Change: Remove support for WordPress older than 4.6 (should still work for earlier versions)
* Change: Update copyright date (2017).
* Change: Update installation instruction to prefer built-in installer over .zip file

## 1.1 _(2016-03-19)_
Highlights:

* This release largely consists of minor behind-the-scenes changes.

Details:

* Bugfix: Don't use translation functions to output strings not needing translation.
* Change: Add support for language packs:
    * Don't load textdomain from file.
    * Remove .pot file and /lang subdirectory.
    * Remove 'Domain Path' plugin header.
* New: Add LICENSE file.
* New: Add empty index.php to prevent files from being listed if web server has enabled directory listings.
* Change: Explicitly declare methods in unit tests as public or protected.
* Change: Minor improvements to inline docs and test docs.
* Change: Note compatibility through WP 4.4+.
* Change: Remove support for WordPress older than 4.1.
* Change: Update copyright date (2016).

## 1.0 _(2015-01-12)_
* Initial public release
* Convert into true plugin
* Permit any user to subscribe to all comments
* Add `c2c_optin_comment_notifications_has_cap` filter to enable customizing capability
* Change meta key name to `c2c_comment_notification_optin`
* Change yes_meta_value from 'Y' to '1'
* Change from `*_usermeta()` to `*_user_option()`
* Ensure user being notified has the capability to receive notifications
* For moderated comments, only notify those who have the capability to moderate comments
* Change class name to `c2c_Optin_Comment_Notifications`
* Add unit tests
* Add icon
* Add banner
* Add readme

## 0.9
* Initial release as theme-packaged plugin on developer.wordpress.org
