<?php
/** Approved bilingual product explanations and seasonal identity. */
namespace Deckerweb\Tuerchenzeit;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Keeps product explanations consistent across existing admin screens. */
final class Branding {
	/** Get a translated product text in the active interface language.
	 * @param string $key Known product text key.
	 * @return string Translated plain text, or an empty string for an unknown key.
	 */
	public static function text( string $key ): string {
		$texts = array(
			'landing_tip_page' => __( 'The prepared landing page is a normal WordPress page. You can edit it in the block editor (Gutenberg) and set it as your website’s static homepage under Settings → Reading.', 'tuerchenzeit' ),
			'landing_tip_light' => __( 'A small Advent website often needs only this one page, plus your legal and privacy information. If you create your daily contents under Own door content, you do not need additional blog posts or content pages. This keeps your website lean and easy to manage.', 'tuerchenzeit' ),
			'landing_tip_patterns' => __( 'Headers and footers are optional. The included patterns give you a ready-made starting point for your own colors, text, and logo. You can also add navigation to the header, for example to further information or contact details.', 'tuerchenzeit' ),
			'network_websites' => __( 'Network activation lets you run independent Advent websites for different topics: recipes, music, reflections, or a local community project. Each website keeps its own calendars, contents, landing pages, settings, and WordPress timezone. Türchenzeit is also available on websites added later. Authorized administrators configure each website separately; network activation creates no pages or calendars automatically.', 'tuerchenzeit' ),
			'descriptor' => __( 'An Advent calendar for WordPress', 'tuerchenzeit' ),
			'slogan' => __( 'A little light. Every day.', 'tuerchenzeit' ),
			'origin' => __( 'Inspired by Christmas in Germany’s Ore Mountains.', 'tuerchenzeit' ),
			'short' => __( 'A thoughtfully designed Advent calendar for WordPress, with 24 daily doors, your own content, and an optional Advent landing page.', 'tuerchenzeit' ),
			'welcome' => __( 'Welcome to Türchenzeit. Create 24 little moments for the Christmas season, with a calendar on your existing website or an Advent landing page of its own. Start here for a simple setup, inspiration, and a little room to make it yours.', 'tuerchenzeit' ),
			'meaning' => __( 'Türchenzeit is a German name that evokes the season of opening little Advent calendar doors: one new door each day, from December 1 to 24.', 'tuerchenzeit' ),
			'website' => __( 'Start your Advent website with a ready-made page. Make the text, colors, and images your own in the block editor (Gutenberg). Optional header and footer patterns give the page a complete setting. You can also add just the calendar to an existing page.', 'tuerchenzeit' ),
			'heritage_short' => __( 'Inspired by the Ore Mountains, a region in southeastern Germany known for its Christmas woodcraft and warmly lit windows. Its candle arches and carved decorations helped shape Türchenzeit’s quiet, welcoming atmosphere.', 'tuerchenzeit' ),
			'about' => __( 'Türchenzeit brings 24 little moments of Advent to your WordPress website. Combine dedicated daily content with existing posts or pages, choose a design, and let a new door open each day. You can also create a fully editable Advent landing page, ready to return with fresh content each year. The German name evokes the season of opening little Advent calendar doors. Part of the Inspiration series, Türchenzeit draws on the Christmas warmth of Germany’s Ore Mountains.', 'tuerchenzeit' ),
			'heritage' => __( 'Türchenzeit is a personal project by David Decker, who comes from the Erzgebirge, or Ore Mountains, in southeastern Germany. The region has a rich tradition of Christmas woodcraft: carved figures, wooden Christmas pyramids, and Schwibbögen, the candle arches that light up windows during the festive season. That sense of light, craftsmanship, and welcome is the inspiration behind the plugin’s atmosphere.', 'tuerchenzeit' ),
		);
		return $texts[ $key ] ?? '';
	}
	/** Resolve the local full-documentation page for the active admin language.
	 * @return string Local URL, including formal German when requested.
	 */
	public static function documentation_url(): string {
		$locale = determine_locale();
		$language = 'de_DE_formal' === $locale ? 'de-formal' : ( str_starts_with( $locale, 'de_' ) ? 'de' : 'en' );
		return plugins_url( 'documentation-' . $language . '.html', FILE );
	}

	/** Return introductory FAQ pairs in the active interface language.
	 * @return array List of translated question and answer pairs.
	 */
	public static function questions(): array {
		return array(
			array( __( 'Can visitors find door content in the WordPress search?', 'tuerchenzeit' ), __( 'Own door content is excluded from the standard WordPress search and has no public single-entry page. Visitors see it through an opened calendar door. Linked posts and pages remain searchable unless original-content protection is enabled in a published calendar; this also hides them from the standard WordPress search until their opening.', 'tuerchenzeit' ) ),
			array( __( 'What does “Türchenzeit” mean?', 'tuerchenzeit' ), __( 'It is a German name combining Türchen, a little door, with Zeit, meaning time or season. It evokes the familiar ritual of opening a little Advent calendar door each day. We keep the German name in English, too. The calendar runs from December 1 to 24, with room for whatever you would like to share behind each door.', 'tuerchenzeit' ) ),
			array( __( 'What are the Ore Mountains?', 'tuerchenzeit' ), __( 'The Erzgebirge, known in English as the Ore Mountains, is a mountain region along the German–Czech border. David comes from the German side, where Christmas woodcraft and illuminated window decorations are deeply rooted traditions. You do not need to know the region to enjoy the plugin; it is the source of its warm, unhurried Advent atmosphere.', 'tuerchenzeit' ) ),
			array( __( 'Can I create a Christian Advent calendar?', 'tuerchenzeit' ), __( 'Yes. Advent means arrival. For Christians, it is a season of expectation and preparation for Christmas, the celebration of the birth of Jesus Christ, the Savior. The Star of Bethlehem and Arrival designs reflect that meaning. Share Bible passages, reflections, or daily devotions, or choose another design for a calendar with a theme of your own.', 'tuerchenzeit' ) ),
			array( __( 'Does my calendar have to look like a German Christmas scene?', 'tuerchenzeit' ), __( 'No. The regional inspiration shapes the warmth of the included designs. Your content, colors, and page patterns are yours to change. Add the calendar to your current website or give it an Advent landing page of its own.', 'tuerchenzeit' ) ),
		);
	}
}
