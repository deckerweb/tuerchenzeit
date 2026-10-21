# Importing a day set with images

[Deutsch](Import-Deutsch)

In **Türchenzeit → Help & inspiration**, choose a JSON export or a ZIP and select **Import as new drafts**. The ZIP contains `calendar.json` directly at its root and images inside `media/`. Do not ZIP an enclosing folder. The existing calendar editor exports JSON; add the images when assembling a portable ZIP.

JPEG, PNG, WebP and GIF are supported. SVG is not supported. Limits: 20 MB for the ZIP, 50 MB unpacked, 100 images, 5 MB and 20 million pixels per image (no dimension above 10,000 pixels). WordPress/PHP upload limits and the site's allowed media types may lower these limits. The server needs the PHP ZIP extension.

The JSON uses the existing Türchenzeit schema. A minimal example with one door:

```json
{
  "format": "tuerchenzeit",
  "schema": 1,
  "title": "Advent",
  "settings": { "year": 2026, "theme": "evergreen" },
  "doors": [
    { "day": 1, "source": "own", "content": "day-1", "mode": "dialog", "image": "media/light.jpg" }
  ],
  "contents": {
    "day-1": {
      "title": "A light of hope",
      "content": "<!-- wp:image --><figure class=\"wp-block-image\"><img src=\"media/light.jpg\" alt=\"A warm light\"/></figure><!-- /wp:image --><!-- wp:paragraph --><p>A moment of hope.</p><!-- /wp:paragraph -->",
      "image": "media/light.jpg"
    }
  },
  "media": [
    { "file": "media/light.jpg", "alt": "A warm light", "url": "https://old.example/light.jpg" }
  ]
}
```

Extend `doors` with unique days 1–24 and add the matching entries to `contents`. Classic HTML and standard blocks from the block editor (Gutenberg) are supported. Image filenames must contain only letters, numbers, periods, hyphens and underscores; use lowercase extensions. The optional `media` list supplies media-library alt text and an old URL to replace. References can use the package path or that old URL. Include separate entries for image variants used by content (for example resized URLs); no remote files are downloaded.

All bundled images are imported once per ZIP import. Matching door images, featured images and embedded URLs are replaced with local media URLs. Core Image blocks also receive the new attachment ID. Existing content is never overwritten; importing the same ZIP again creates another independent set, including new media. New calendars and own contents are drafts. Review them before publishing. Original-content protection starts disabled. Existing posts/pages are only matched by local URL and are not copied into the package.

On failure, images created during this import are removed; newly created content drafts are moved to Trash if content creation fails. Existing media and contents are preserved. Public media URLs are not protected by the calendar's door opening time.
