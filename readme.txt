=== Türchenzeit ===
Contributors: daveshine
Tags: advent, christmas, calendar, countdown
Tested up to: 7.1
Requires at least: 6.9
Requires PHP: 8.3
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A new door every day. An Advent calendar with six designs, own content, linked entries, and a countdown.

== Description ==

Türchenzeit brings 24 little moments of Advent to your WordPress website. Combine dedicated daily content with existing posts or pages, choose a design, and let a new door open each day. You can also create a fully editable Advent landing page, ready to return with fresh content each year. The German name evokes the season of opening little Advent calendar doors. Part of the Inspiration series, Türchenzeit draws on the Christmas warmth of Germany’s Ore Mountains.

Version 1.0.0. PHP 8.3 or later; WordPress 6.9 or later.

* Your own Advent website – an editable landing page with optional header and footer patterns.
* 24 doors, your contents – combine your own daily contents with existing posts or pages.
* Six designs with character – custom accent colors and adjustable popups.
* A new moment every day – scheduled openings and an optional December countdown.
* Design once, use every year – prepare new annual editions at the existing address.
* Prepare with confidence – door grid, readiness check and private date preview.
* Import complete day sets – JSON/ZIP import including images.

== Installation ==

1. Upload the plugin ZIP under Plugins → Add Plugin → Upload Plugin, then activate it.
2. Under Türchenzeit → New door content, create your contents. The block editor (Gutenberg) is the default when no editor preference has been set. Choose Classic in a calendar’s settings if you prefer; a saved choice applies to this website.
3. Under Türchenzeit → Add calendar, set the title, year, design, and optional countdown text.
4. Choose a door card and search for a title. Click a result directly below the search field, or create and assign your own draft. Publish your own contents when ready; linked entries must already be published. Save the calendar.
5. Publish the calendar. Use its shortcode, or click Prepare landing page. The new page starts as a draft with native Block Editor (Gutenberg) blocks. Adjust your text, logo, contact and legal links, then publish it.

== Frequently Asked Questions ==

= How do I embed a calendar? =

Use [tuerchenzeit id="123"], replacing 123 with the calendar ID shown in the editor. [adventskalender id="123"] is an alias. JavaScript is required for opening doors and showing the countdown.

= Can I place the countdown separately? =

Use [tuerchenzeit_countdown id="123"]. Add countdown="0" to the calendar shortcode to hide its own countdown. The countdown ends on December 1 at 00:00 in the calendar year and WordPress timezone.

= How do I preview future doors? =

Readiness lists missing assignments, unavailable contents, year and timezone. A saved calendar offers a private date simulation in the WordPress timezone, restricted to authorized administrators. Public opening times never change. Alternatively, [tuerchenzeit id="123" preview="1"] opens all assigned contents including own drafts.

= Are future contents private? =

Own door content is excluded from the standard WordPress search and has no public single-entry page. Visitors see it through an opened calendar door; REST access is restricted to administrators. Linked posts and pages normally remain publicly accessible and searchable. The opt-in setting protects originals assigned to published calendars until opening, including direct URLs, the standard WordPress search and other standard access paths. The earliest opening across all protecting calendars wins; drafts lock nothing. Removing the last protecting assignment removes the lock. Purge caches and exclude original URLs from page caching when enabling protection. Protection requires the plugin to stay active and does not cover public media files or external copies.

= What happens after Christmas or if content disappears? =

Opened doors stay accessible. An optional editable greeting appears from December 25 onward. The today button appears only December 1–24 in the calendar year. Deleted, private, password-protected and unpublished linked originals are unavailable. Visited markers are optional and saved locally only after enabling them on that browser.

= What content and data are supported? =

Choose Classic or Block Editor (Gutenberg) for your own contents. Standard text, image, gallery, column and video blocks are rendered in the dialog. YouTube URLs on their own paragraph and YouTube Embed blocks become click-to-load players. Arbitrary third-party iframes are not permitted. Linked previews show an excerpt and link. Deactivation and uninstallation preserve calendars, contents, pages, editor preference and assignments. Daily Scripture supports fixed local passages via its Bible Passage block and [daily_scripture_passage ...], when the plugin and desired Bible edition are set up. Other shortcodes are not executed.

= How do I prepare the next year without changing the live website? =

Use Prepare next year in the saved calendar. It creates a new calendar draft with the next year and independent drafts of its own contents; linked entries are reused. Publish the new contents and calendar when ready. In the original calendar, set the switch time in the WordPress timezone (default: November 1 of the next year). The original shortcode and landing-page URL then display the new published edition. Drafts never replace the live calendar; an empty switch date disables switching. Under Popup and button appearance, change colors, overlay opacity, button label, outline/fill and corners. Empty color fields inherit the design. Messages for closed or empty doors provides separate texts with {tuerchen}, {tag}, {datum} and {uhrzeit}; paragraphs, bold and italic are supported. In the page editor, find optional sections under Patterns → Türchenzeit and replace the existing header or footer. Set the outer group to Full width; the original landing layout stays the default.

== Shortcode output ==

Own door contents and calendar popups do not execute general shortcodes. Daily Scripture is a supported exception: its fixed Bible passage shortcode can show one verse or a longer passage when the plugin and Bible edition are available.

For other shortcodes, assign a post, page or suitable public content type and select Go to entry. The full page uses the normal WordPress output, provided the relevant plugin and template support those shortcodes. Choosing another content type alone does not change the popup rules.

The popup is intended for manageable daily contents. Other shortcodes may require complete layouts, forms, scripts or styles that cannot generally be guaranteed in a popup loaded after the page. Daily Scripture is a deliberately integrated exception.

== Privacy ==

Frontend calendar requests go only to your own WordPress website. No telemetry or remote fonts. YouTube players contact youtube-nocookie.com only after the visitor clicks Load YouTube video; no remote preview thumbnail is loaded. YouTube still receives visitor connection data after loading. Block Editor (Gutenberg)’s editor can use WordPress’s provider previews while editing. The bundled deckerweb Library provides an optional plugin catalog, initially offline. The deckerweb Updater checks GitHub releases through WordPress’s update workflow. Other media retain their original hosts. Visited markers optionally use localStorage only after the visitor enables them. Only door numbers scoped to website, calendar and year are stored; nothing is transmitted. Disabling removes the saved state; a separate button clears markers. Daily Scripture is optional; Türchenzeit does not download its Bible texts.

Optional local fonts: Source Serif 4 and Inter, SIL Open Font License 1.1. License texts and source information are included in licenses/. No external font service is used.

== Changelog ==

= 1.0.0 =

* New: 24 doors, six designs, editable Advent landing pages, private contents, readiness checks, annual editions, JSON/ZIP imports, optional local fonts and Multisite support.

== Copyright and licenses ==

Copyright 2026 David Decker – DECKERWEB. GPL v2 or later. Source Serif 4 and Inter: SIL Open Font License 1.1; license files included in licenses/.
