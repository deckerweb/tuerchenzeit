# Tagessätze mit Bildern importieren

[English](IMPORT-en.md)

Unter **Türchenzeit → Hilfe & Inspiration** eine JSON-Datei oder ein ZIP auswählen und **Als neue Entwürfe importieren** anklicken. Das ZIP enthält `calendar.json` direkt im Hauptverzeichnis und die Bilder im Ordner `media/`. Keinen zusätzlichen übergeordneten Ordner einpacken. Der Kalendereditor exportiert weiterhin JSON; für ein übertragbares ZIP die zugehörigen Bilder hinzufügen.

JPEG, PNG, WebP und GIF werden unterstützt. SVG wird nicht unterstützt. Grenzen: 20 MB pro ZIP, 50 MB entpackt, 100 Bilder, je Bild 5 MB und 20 Millionen Pixel (keine Kante über 10.000 Pixel). Upload-Grenzen von WordPress/PHP sowie die erlaubten Medientypen der Website können diese Grenzen verringern. Der Server benötigt die PHP-ZIP-Erweiterung.

Das JSON verwendet das bestehende Türchenzeit-Format. Ein minimales Beispiel mit einem Türchen:

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

Für einen kompletten Tagessatz `doors` mit den eindeutigen Tagen 1–24 und `contents` mit den zugehörigen Inhalten ergänzen. Classic-HTML und Standardblöcke des Block-Editors (Gutenberg) werden unterstützt. Bilddateinamen dürfen nur Buchstaben, Zahlen, Punkte, Bindestriche und Unterstriche enthalten; die Dateiendung kleinschreiben. Die optionale Liste `media` enthält den Alternativtext für die Mediathek und eine alte URL zum Ersetzen. Verweise können den Paketpfad oder diese alte URL verwenden. Verwendete Bildvarianten, etwa verkleinerte Bilder mit eigener URL, separat eintragen und beilegen. Es werden keine externen Dateien heruntergeladen.

Jedes enthaltene Bild wird pro ZIP-Import einmal übernommen. Passende Türchenbilder, Beitragsbilder und eingebettete URLs werden auf die neuen lokalen Medien-URLs umgestellt. Native Bildblöcke erhalten auch die neue Anhang-ID. Bestehende Inhalte werden nicht überschrieben; ein erneuter Import erzeugt einen weiteren unabhängigen Satz samt neuen Bildern. Neue Kalender und eigene Türcheninhalte sind Entwürfe und sollten vor dem Veröffentlichen geprüft werden. Der Originalschutz startet ausgeschaltet. Vorhandene Beiträge/Seiten werden nur über lokale URLs zugeordnet und nicht in das Paket kopiert.

Bei Fehlern werden die während dieses Imports angelegten Bilder entfernt; falls die Inhaltserstellung scheitert, werden bereits angelegte Inhaltsentwürfe in den Papierkorb verschoben. Vorhandene Medien und Inhalte bleiben erhalten. Öffentliche Medien-URLs werden durch die Öffnungszeit der Türchen nicht geschützt.
