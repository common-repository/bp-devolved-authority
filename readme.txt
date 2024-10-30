=== BP Devolved Authority ===
Contributors:
Donate link: paypal.me/GeorgeChaplin
Tags: buddypress, bp_moderate, administrator, devolved authority, groups, members, xprofile, activity
Requires at least: PHP 7.2, WordPress 5.2 BuddyPress 1.5.1
Tested up to: 6.6.1
Stable tag: 1.2.0
Plugin URI: www.wordpress.org/plugins/bp-devolved-authority/

This plugin allows key aspects of BuddyPress administration to be devolved to non admin users.

== Description ==

BP Devolved Authority allows site administrators to devolve the ability to manage Groups, Activity, Members and Emails to other site members. For Xprofile information, you can also give specific individuals the ability to edit the xprofile fields for one or more named individuals.



BuddyPress has not traditionally allowed site administrators to grant authority to manage aspect of BuddyPress to users without 'manage_options' capability. This plugin changes that and allows each of the BP Components to be managed by site members so long as they have the 'edit_posts' capability.

Note that roles allowed to manage members should also have the 'list_users' capability, this allows the users list menu item to be displayed in the dashboard for that member.

The  new feature - individual xprofile management delegation, creates a simple, secure delegation system whereby a privileged user (such as an administrator) can assign other registered BuddyPress members to be "delegates" for a given user. A delegate has the capability to view and edit Extended Profile (XProfile) fields for the delegated user. This is useful on sites where certain relationships exist between one user and another, such as legal guardianship by an adult over a child. Using delegation reduces the need to share passwords or log in to shared accounts.

**Roles and capabilities for the delegated xprofile feature**

This plugin uses the built-in capabilities system as part of WordPress core, along with core BuddyPress hooks (`bp_current_user_can`) to check for appropriate permissions, making it both simple to customize and as secure as WP and BP core code. The custom capabilities are:

* `edit_user_delegates` - Users with this capability can assign delegates for users they can edit (determined by `edit_users`).

Additionally, the following core capabilities are required:

* `list_users` - The delegation options implicitly enumerate all registered users, so a user must also have the `list_users` capability to be granted access to the Delegation user interface.
* `edit_users` - If you cannot `edit_users`, you cannot `edit_user_delegates`, either.

When this option is enabled, the admin can go to the users extended profile tab from the Dashboard>>Users list and choose to delegate authority over the xprofile data to another site member. That site member will then find in their Dashboard>>Users menu and option to see a list of "Devolved Profiles".

Once the plugin is activated, visit the Settings>>BP Devolved Authority page to choose which roles can manage your BP Components. 

This plugin needs BuddyPress to work.

== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Activate the plugin at the plugin administration page
3. Adjust settings via the BP Devolved Authority admin page

== Frequently Asked Questions ==




== Changelog ==

= 1.2.0 =

* 29/07/2024

* New feature: Now includes the features of the old plugin BP Delegated xprofile, for additional user management delegation options.

= 1.1.0 =

* 20/07/2024

* Update: Improved translation and escaping of outputs.

= 1.0.2 =

* 10/02/2021

* Fix: Translation improvements

= 1.0.1 =

* 07/01/2021

* Fix: Corrected error with text_domain.

= 1.0.0 =

* Initial Release.


== Upgrade Notice ==


== Screenshots ==

1. screenshot-1.png - Plugin Settings page.
