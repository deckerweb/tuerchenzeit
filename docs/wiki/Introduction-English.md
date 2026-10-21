# Türchenzeit

![Türchenzeit – An Advent calendar for WordPress](https://raw.githubusercontent.com/deckerweb/tuerchenzeit/main/branding/banner-github-en.png)

[Deutsch](Introduction-Deutsch) · [Documentation](https://deckerweb.github.io/tuerchenzeit/) · [FAQ](FAQ-English) · [Changelog](Changelog-English)

## About

Türchenzeit brings 24 little moments of Advent to your WordPress website. Combine dedicated daily content with existing posts or pages, choose a design, and let a new door open each day. You can also create a fully editable Advent landing page, ready to return with fresh content each year. The German name evokes the season of opening little Advent calendar doors. Part of the Inspiration series, Türchenzeit draws on the Christmas warmth of Germany’s Ore Mountains.

**Version 1.0.0 · validation build** · WordPress 6.9+ · PHP 8.3+ · GPL-2.0-or-later. No GitHub release has been published.

[At a glance](#at-a-glance) · [Getting started](#getting-started) · [Features](#features) · [FAQ](#faq) · [Privacy](#privacy-and-connections)

## At a glance

- **Your own Advent website** – an editable landing page with optional header and footer patterns.
- **24 doors, your contents** – combine your own daily contents with existing posts or pages.
- **Six designs with character** – custom accent colors and adjustable popups.
- **A new moment every day** – scheduled openings and an optional December countdown.
- **Design once, use every year** – prepare new annual editions at the existing address.
- **Prepare with confidence** – door grid, readiness check and private date preview.
- **Import complete day sets** – JSON/ZIP import including images.

## Getting started

1. Upload the plugin ZIP under Plugins → Add Plugin → Upload Plugin, then activate it.
2. Under Türchenzeit → New door content, create your contents. The block editor (Gutenberg) is the default when no editor preference has been set. Choose Classic in a calendar’s settings if you prefer; a saved choice applies to this website.
3. Under Türchenzeit → Add calendar, set the title, year, design, and optional countdown text.
4. Choose a door card and search for a title. Click a result directly below the search field, or create and assign your own draft. Publish your own contents when ready; linked entries must already be published. Save the calendar.
5. Publish the calendar. Use its shortcode, or click Prepare landing page. The new page starts as a draft with native blocks. Adjust your text, logo, contact and legal links, then publish it.

## Features

### Your own Advent website

Prepare a normal WordPress page made from standard blocks. Edit the text, logo and colors, add optional header/footer patterns, and use it as a standalone Advent mini website or your static homepage. The calendar also works inside an existing website.

![Your own Advent website](https://raw.githubusercontent.com/deckerweb/tuerchenzeit/main/docs/screenshots/advent-website-en.jpg)

*Public page before December 1: a countdown and 24 closed doors.*

### Daily contents, thoughtfully presented

Use dedicated private door content, existing posts or pages, or other public content types. Own content stays out of the standard WordPress search and has no public single-entry page. General shortcodes are not executed inside popups; *Daily Scripture* is an optional, specifically supported exception.

![An opened door](https://raw.githubusercontent.com/deckerweb/tuerchenzeit/main/docs/screenshots/open-door-en.jpg)

*Own door content in the private December 8 date preview; public opening times stay unchanged.*

### Prepare with confidence

Assign contents in the 24-door grid. Ready days, unpublished contents and missing assignments have distinct states. Check a future date privately without changing public opening times. Visitors receive content only after the server checks availability.

![Manage the 24 doors](https://raw.githubusercontent.com/deckerweb/tuerchenzeit/main/docs/screenshots/calendar-grid-en.jpg)

*The grid shows 20 ready days, two unpublished contents, and two missing assignments.*

### Six designs and your own finishing touches

Choose Evergreen, Village lights, Winter glow, Star of Bethlehem, Arrival or Star mail. Adjust accent colors, popup backgrounds, overlays, button labels and styles. A schematic live preview helps you judge changes before saving. Empty overrides inherit the design.

### Start with standard blocks

Ten content patterns provide starting points for reflections, verses, videos, picture stories, recipes, acts of kindness, letters, songs, crafts, and prayer or blessing. Optional local Source Serif 4 and Inter fonts are selectable in supported block editors; theme typography remains unchanged until you choose them.

### Reuse, transfer and grow

Prepare independent annual editions, import JSON or ZIP day sets with raster images, and use separate calendars on independent Multisite websites. Deactivation and uninstallation preserve your calendars, contents, assignments and landing pages.

[Illustrated guide](Guide-English) · [Import instructions](Import-English)

## FAQ

### How do I embed a calendar?

Use [tuerchenzeit id="123"], replacing 123 with the calendar ID shown in the editor. [adventskalender id="123"] is an alias. JavaScript is required for opening doors and showing the countdown.

### Can I place the countdown separately?

Use [tuerchenzeit_countdown id="123"]. Add countdown="0" to the calendar shortcode to hide its own countdown. The countdown ends on December 1 at 00:00 in the calendar year and WordPress timezone.

### How do I preview future doors?

Readiness lists missing assignments, unavailable contents, year and timezone. A saved calendar offers a private date simulation in the WordPress timezone, restricted to authorized administrators. Public opening times never change. Alternatively, [tuerchenzeit id="123" preview="1"] opens all assigned contents including own drafts.

### Are future contents private?

Own door content is excluded from the standard WordPress search and has no public single-entry page. Visitors see it through an opened calendar door; REST access is restricted to administrators. Linked posts and pages normally remain publicly accessible and searchable. The opt-in setting protects originals assigned to published calendars until opening, including direct URLs, the standard WordPress search and other standard access paths. The earliest opening across all protecting calendars wins; drafts lock nothing. Removing the last protecting assignment removes the lock. Purge caches and exclude original URLs from page caching when enabling protection. Protection requires the plugin to stay active and does not cover public media files or external copies.

### What happens after Christmas or if content disappears?

Opened doors stay accessible. An optional editable greeting appears from December 25 onward. The today button appears only December 1–24 in the calendar year. Deleted, private, password-protected and unpublished linked originals are unavailable. Visited markers are optional and saved locally only after enabling them on that browser.

### What content and data are supported?

Choose Classic or Block Editor (Gutenberg) for your own contents. Standard text, image, gallery, column and video blocks are rendered in the dialog. YouTube URLs on their own paragraph and YouTube Embed blocks become click-to-load players. Arbitrary third-party iframes are not permitted. Linked previews show an excerpt and link. Deactivation and uninstallation preserve calendars, contents, pages, editor preference and assignments. Daily Scripture supports fixed local passages via its Bible Passage block and [daily_scripture_passage ...], when the plugin and desired Bible edition are set up. Other shortcodes are not executed.

### How do I prepare the next year without changing the live website?

Use Prepare next year in the saved calendar. It creates a new calendar draft with the next year and independent drafts of its own contents; linked entries are reused. Publish the new contents and calendar when ready. In the original calendar, set the switch time in the WordPress timezone (default: November 1 of the next year). The original shortcode and landing-page URL then display the new published edition. Drafts never replace the live calendar; an empty switch date disables switching. Under Popup and button appearance, change colors, overlay opacity, button label, outline/fill and corners. Empty color fields inherit the design. Messages for closed or empty doors provides separate texts with {tuerchen}, {tag}, {datum} and {uhrzeit}; paragraphs, bold and italic are supported. In the page editor, find optional sections under Patterns → Türchenzeit and replace the existing header or footer. Set the outer group to Full width; the original landing layout stays the default.

[All FAQs by topic](FAQ-English)

## Privacy and connections

Frontend calendar requests go only to your own WordPress website. No telemetry or remote fonts. YouTube players contact youtube-nocookie.com only after the visitor clicks Load YouTube video; no remote preview thumbnail is loaded. YouTube still receives visitor connection data after loading. Block Editor (Gutenberg)’s editor can use WordPress’s provider previews while editing. The bundled deckerweb Library provides an optional plugin catalog, initially offline. The deckerweb Updater checks GitHub releases through WordPress’s update workflow. Other media retain their original hosts. Visited markers optionally use localStorage only after the visitor enables them. Only door numbers scoped to website, calendar and year are stored; nothing is transmitted. Disabling removes the saved state; a separate button clears markers. Daily Scripture is optional; Türchenzeit does not download its Bible texts.

The two fonts are optional and supplied locally under the SIL Open Font License. No Font Library installation or external font service is needed. Theme typography stays unchanged until you choose a font. Browsers download font files only for text that uses them. Plugin font choices require Türchenzeit to remain active.

## The idea behind Türchenzeit

Türchenzeit makes room for anticipation, with content that brings joy, invites reflection, and speaks to the heart and soul. Its design is intentionally warm and understated. Kitsch is outside its scope, as is Halloween; a purely wintry or snowy backdrop is not the idea behind the plugin either. Its home is the Advent and Christmas season.

Of course, a door can also reveal a voucher, an offer, or a product idea. Türchenzeit is not, however, a marketing plugin built around driving sales or grabbing attention. It provides a thoughtfully designed tool; the personal touch comes from what you share. A good Advent calendar does not need flashy effects. It needs something people can look forward to each day.

## Changelog

### 1.0.0 · 2026-10-21

**New:**

- 24 daily doors with dedicated private content or linked posts and pages.
- Six Advent designs, accent colors, adjustable dialogs, buttons and notices.
- An editable standalone Advent landing page, optional header/footer patterns and local fonts.
- Readiness checks, a private date preview and independent annual editions.
- JSON and ZIP imports for day sets, including raster images.
- Optional original-content protection, click-to-load YouTube videos and Daily Scripture integration.
- Single-site and Multisite support, with independent settings for each website.

## Author and support

Türchenzeit is a personal project by David Decker, who comes from the Erzgebirge, or Ore Mountains, in southeastern Germany. The region has a rich tradition of Christmas woodcraft: carved figures, wooden Christmas pyramids, and Schwibbögen, the candle arches that light up windows during the festive season. That sense of light, craftsmanship, and welcome is the inspiration behind the plugin’s atmosphere.

Developed and maintained by David Decker – DECKERWEB.

[Questions and bugs](https://github.com/deckerweb/tuerchenzeit/issues) · [Discussions](https://github.com/deckerweb/tuerchenzeit/discussions) · [Report a vulnerability privately](https://github.com/deckerweb/tuerchenzeit/security/advisories/new)

[Ko-fi](https://ko-fi.com/deckerweb) · [Buy Me a Coffee](https://buymeacoffee.com/daveshine) · [PayPal](https://paypal.me/deckerweb)

## Copyright and licenses

Copyright © 2026 David Decker – DECKERWEB. [GPL-2.0-or-later](https://github.com/deckerweb/tuerchenzeit/blob/main/LICENSE).

Bundled components: deckerweb Library 1.1.0 and deckerweb Updater 2.2.0 (GPL-2.0-or-later). Source Serif 4 and Inter are supplied locally under SIL Open Font License 1.1. Original approved SVG artwork uses vector forms and outlined lettering, without embedded raster images or external resources.

[Font licenses and provenance](https://github.com/deckerweb/tuerchenzeit/blob/main/licenses/FONTS.txt) · [Source Serif 4](https://github.com/deckerweb/tuerchenzeit/blob/main/licenses/Source-Serif-4-OFL.txt) · [Inter](https://github.com/deckerweb/tuerchenzeit/blob/main/licenses/Inter-OFL.txt)
