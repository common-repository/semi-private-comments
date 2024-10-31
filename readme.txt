=== Semi-Private Comments ===
Contributors: cleek
Donate link: http://ok-cleek.com/blogs
Tags: comments
Requires at least: 2.0.2
Tested up to: 2.7
Stable tag: 1.0.1

This plugin masks comments so that a user can only see comments written by 
himself or by an admin. Admins can see all comments.

== Description ==

This plugin masks comments so that a user can only see comments written by 
himself or by an admin. Admins can see all comments.

This was written to make my trivia contests more interesting; by masking 
the contents of comments from everyone but the person who wrote the comment 
and admins (where "admin" = anyone with the ability to edit users), users 
cannot copy answers from other users. This gives everyone a fair chance at 
answering questions before the contest ends.

But, there is nothing in the plugin that is specific to trivia contests, 
that's just what I use it for.

How it works:
The Create Post page gets a "Make comments Semi-Private" option. This can be 
turned on and off at any time. When a user visits a post with Semi-Priv 
Comments turned on, the plugin compares the user's current IP address with 
that of each comment. If the IP addresses match, the comment is displayed as 
usual. If the IP address doesn't match, a substitute comment message is 
displayed instead. The message text is configurable on the Admin pages, but 
defaults to "This comment is hidden". 

You can also change the plugin to work off of WordPress user IDs, but that 
only makes sense if all your visitors are registered on your blog.


== Installation ==

1. Upload `semiprivate_comments.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Optionally, change the "comment hidden" text in the Admin settings page.

== Frequently Asked Questions ==

= Does this work with previous version of WP? =

I don't know, I've never tried. This was written and tested with v2.6. It's working fine on WP v2.7.

= If a user comments from one computer then comments from a different computer, his first comment is hidden ! = 

Sorry.