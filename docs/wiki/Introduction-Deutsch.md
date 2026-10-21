# Türchenzeit

![Türchenzeit – Ein Adventskalender für WordPress](https://raw.githubusercontent.com/deckerweb/tuerchenzeit/main/branding/banner-github-de.png)

[English](Introduction-English) · [Dokumentation](https://deckerweb.github.io/tuerchenzeit/de/) · [FAQ](FAQ-Deutsch) · [Änderungsverlauf](Changelog-Deutsch)

## Kurzvorstellung

Türchenzeit bringt 24 kleine Adventsmomente auf deine WordPress-Website. Kombiniere eigene Türcheninhalte mit vorhandenen Beiträgen oder Seiten, wähle ein passendes Design und lass jeden Tag ein neues Türchen öffnen. Auf Wunsch entsteht daraus eine vollständig bearbeitbare Adventswebsite – einmal eingerichtet, jedes Jahr mit neuen Inhalten. Ein Projekt aus der Serie Inspiration, mit Wärme aus dem Erzgebirge.

**Version 1.0.0 · Teststand** · WordPress ab 6.9 · PHP ab 8.3 · GPL-2.0-or-later. Es wurde noch kein GitHub-Release veröffentlicht.

[Auf einen Blick](#auf-einen-blick) · [Erste Schritte](#erste-schritte) · [Funktionen](#funktionen) · [FAQ](#faq) · [Datenschutz](#datenschutz-und-verbindungen)

## Auf einen Blick

- **Deine eigene Adventswebsite** – bearbeitbare Landingpage mit optionalen Header- und Footer-Vorlagen.
- **24 Türchen, deine Inhalte** – eigene Tagesinhalte und bestehende Beiträge oder Seiten kombinieren.
- **Sechs Designs mit Charakter** – individuelle Akzentfarben und anpassbare Popups.
- **Jeden Tag ein neuer Moment** – zeitgesteuerte Freigabe und optionaler Dezember-Countdown.
- **Einmal gestalten, jedes Jahr nutzen** – neue Jahresausgaben über die bestehende Adresse bereitstellen.
- **Mit Überblick vorbereiten** – Türchenraster, Bereitschaftscheck und private Datumsvorschau.
- **Komplette Tagessätze übernehmen** – JSON-/ZIP-Import samt Bildern.

## Erste Schritte

1. Lade das Plugin-ZIP unter Plugins → Plugin hinzufügen → Plugin hochladen hoch und aktiviere es.
2. Erstelle deine Inhalte unter Türchenzeit → Neuer Türcheninhalt. Der Block-Editor (Gutenberg) ist Standard, solange keine Editor-Auswahl gespeichert wurde. Wenn du möchtest, wähle Classic in den Kalendereinstellungen; die gespeicherte Auswahl gilt für diese Website.
3. Lege unter Türchenzeit → Kalender hinzufügen einen Kalender an. Wähle Titel, Jahr, Design und optional den Countdown-Text.
4. Wähle eine Türchenkarte und suche nach einem Titel. Klicke direkt unter dem Suchfeld auf einen Treffer oder erstelle einen eigenen Entwurf und weise ihn zu. Veröffentliche eigene Inhalte, sobald sie fertig sind; verknüpfte Einträge müssen bereits veröffentlicht sein. Speichere den Kalender.
5. Veröffentliche den Kalender. Verwende seinen Shortcode oder klicke auf Landingpage vorbereiten. Die neue Seite startet als Entwurf aus nativen Blöcken. Passe Texte, Logo, Kontakt- und rechtliche Links an und veröffentliche die Seite.

## Funktionen

### Deine eigene Adventswebsite

Bereite eine normale WordPress-Seite aus Standardblöcken vor. Passe Texte, Logo und Farben an, ergänze bei Bedarf Header- und Footer-Vorlagen und nutze sie als eigenständige Advents-Mini-Website oder statische Startseite. Der Kalender lässt sich ebenso in eine vorhandene Website einfügen.

![Die eigene Adventswebsite](https://raw.githubusercontent.com/deckerweb/tuerchenzeit/main/docs/screenshots/advent-website-de.jpg)

*Normale Besucherseite vor dem 1. Dezember: Countdown und 24 geschlossene Türchen.*

### Tägliche Inhalte mit Raum zum Lesen

Verwende eigene geschützte Türcheninhalte, vorhandene Beiträge und Seiten oder andere öffentliche Inhaltstypen. Eigene Inhalte erscheinen nicht in der normalen WordPress-Suche und haben keine öffentlich aufrufbare Einzelansicht. Allgemeine Shortcodes werden im Popup nicht ausgeführt; *Daily Scripture* ist eine optionale, gezielt unterstützte Ausnahme.

![Ein geöffnetes Türchen](https://raw.githubusercontent.com/deckerweb/tuerchenzeit/main/docs/screenshots/open-door-de.jpg)

*Eigener Türcheninhalt in der privaten Datumsvorschau für den 8. Dezember; die öffentlichen Öffnungszeiten bleiben unverändert.*

### Mit Übersicht vorbereiten

Weise die Inhalte im 24-Türchen-Raster zu. Fertige Tage, unveröffentlichte Inhalte und fehlende Zuordnungen sind klar unterscheidbar. Prüfe ein zukünftiges Datum privat, ohne die öffentlichen Öffnungszeiten zu verändern. Besucher erhalten Inhalte erst nach der serverseitigen Prüfung.

![Die 24 Türchen verwalten](https://raw.githubusercontent.com/deckerweb/tuerchenzeit/main/docs/screenshots/calendar-grid-de.jpg)

*Das Raster zeigt 20 fertige Tage, zwei unveröffentlichte Inhalte und zwei noch fehlende Zuordnungen.*

### Sechs Designs und deine persönliche Note

Wähle Tannengrün, Lichterdorf, Winterlicht, Stern von Bethlehem, Ankunft oder Sternenpost. Akzentfarben, Popup-Hintergründe, Overlay und Button-Texte sowie -Stile sind anpassbar. Eine schematische Live-Vorschau zeigt Änderungen vor dem Speichern. Leere Anpassungsfelder übernehmen die Design-Vorgaben.

### Mit Standardblöcken beginnen

Zehn Inhaltsvorlagen geben Anregungen für Impulse, Bibelverse, Videos, Bildergeschichten, Rezepte, gute Taten, Briefe, Lieder, Bastelideen und Gebet oder Segen. Die optionalen lokalen Schriften Source Serif 4 und Inter sind in den unterstützten Block-Editoren auswählbar. Die Theme-Typografie bleibt erhalten, bis eine Schrift gewählt wird.

### Wiederverwenden, übertragen und wachsen

Bereite unabhängige Jahresausgaben vor, importiere JSON- oder ZIP-Tagessätze mit Rasterbildern und betreibe unabhängige Kalender-Websites in Multisite. Beim Deaktivieren und Deinstallieren bleiben Kalender, Inhalte, Zuordnungen und Landingseiten erhalten.

[Bebilderte Anleitung](Guide-Deutsch) · [Importanleitung](Import-Deutsch)

## FAQ

### Wie füge ich den Kalender ein?

Verwende [tuerchenzeit id="123"] und ersetze 123 durch die im Editor angezeigte Kalender-ID. [adventskalender id="123"] ist eine alternative Schreibweise. JavaScript ist zum Öffnen der Türchen und für den Countdown erforderlich.

### Kann der Countdown separat stehen?

Verwende [tuerchenzeit_countdown id="123"]. Mit countdown="0" im Kalender-Shortcode blendest du dessen eigenen Countdown aus. Das Ziel ist der 1. Dezember um 00:00 Uhr im Kalenderjahr und in der WordPress-Zeitzone.

### Wie prüfe ich zukünftige Türchen?

Der Bereitschaftscheck zeigt fehlende Zuordnungen, nicht verfügbare Inhalte, Jahr und Zeitzone. Ein gespeicherter Kalender bietet eine private Datumsvorschau in der WordPress-Zeitzone. Nur berechtigte Administratoren können sie aufrufen; Besucheröffnungszeiten bleiben unverändert. Alternativ öffnet [tuerchenzeit id="123" preview="1"] alle zugewiesenen Inhalte einschließlich eigener Entwürfe.

### Sind zukünftige Inhalte geschützt?

Eigene Türcheninhalte sind von der normalen WordPress-Suche ausgeschlossen und haben keine öffentlich aufrufbare Einzelansicht. Besucher sehen sie über ein geöffnetes Kalendertürchen; REST-Zugriffe sind auf Administratoren beschränkt. Verknüpfte Beiträge und Seiten bleiben normalerweise öffentlich erreichbar und auffindbar. Die Zusatzoption schützt sie in veröffentlichten Kalendern bis zum Öffnungstermin, auch über direkte URLs, die normale WordPress-Suche und weitere Standardzugriffswege von WordPress. Bei Mehrfachzuweisungen gilt der früheste Termin aller schützenden Kalender; Entwürfe sperren nichts. Nach Entfernen der letzten schützenden Zuordnung entfällt die Sperre. Vor Einschalten Caches leeren und betroffene Original-URLs vom Seiten-Cache ausnehmen. Schutz setzt ein aktives Plugin voraus und umfasst keine öffentlichen Mediendateien oder externen Kopien.

### Was passiert nach Weihnachten oder bei fehlenden Inhalten?

Geöffnete Türchen bleiben zugänglich. Optional erscheint ab dem 25. Dezember ein bearbeitbarer Weihnachtsgruß. Der Tagesbutton erscheint nur vom 1. bis 24. Dezember im Kalenderjahr. Gelöschte, private, passwortgeschützte oder unveröffentlichte verknüpfte Einträge sind nicht verfügbar. Besuchsmarkierungen sind freiwillig, lokal je Browser und werden erst nach Einschalten gespeichert.

### Welche Inhalte und Daten werden unterstützt?

Wähle Classic oder Block-Editor (Gutenberg) für eigene Inhalte. Standardblöcke für Text, Bilder, Galerien, Spalten und Videos werden im Dialog dargestellt. YouTube-URLs in einem eigenen Absatz und YouTube-Einbetten-Blöcke werden zu Playern, die erst per Klick laden. Beliebige fremde Iframes sind nicht erlaubt. Verknüpfte Vorschauen zeigen Auszug und Link. Deaktivierung und Deinstallation erhalten Kalender, Inhalte, Seiten, Editor-Auswahl und Zuordnungen. Daily Scripture unterstützt feste lokale Bibelstellen als „Bibelstelle“-Block und [daily_scripture_passage ...], wenn Plugin und gewünschte Bibelausgabe eingerichtet sind. Andere Shortcodes werden nicht ausgeführt.

### Wie bereite ich das nächste Jahr vor, ohne die laufende Website zu verändern?

Verwende im gespeicherten Kalender Nächste Jahresausgabe vorbereiten. Es entstehen ein Kalenderentwurf für das nächste Jahr und unabhängige Entwürfe seiner eigenen Inhalte; verknüpfte Einträge werden wiederverwendet. Veröffentliche neue Inhalte und Kalender, sobald sie fertig sind. Stelle im ursprünglichen Kalender den Wechseltermin in der WordPress-Zeitzone ein (Standard: 1. November des nächsten Jahres). Der ursprüngliche Shortcode und die Landingpage-Adresse zeigen dann die neue veröffentlichte Ausgabe. Entwürfe ersetzen den laufenden Kalender nie; ein leeres Wechseldatum deaktiviert die Umschaltung. Unter „Popup und Button gestalten“ lassen sich Farben, Overlay-Deckkraft, Button-Text, Umriss/Füllung und Ecken ändern. Leere Farbfelder übernehmen das Design. „Hinweise für geschlossene oder leere Türchen“ bietet getrennte Texte mit {tuerchen}, {tag}, {datum} und {uhrzeit}; Absätze, Fett und Kursiv sind erlaubt. Im Seiteneditor unter Vorlagen → Türchenzeit lassen sich optional Header oder Footer einfügen und bestehende Bereiche ersetzen. Die äußere Gruppe muss auf „Volle Breite“ stehen; die bisherige Landingseite bleibt Standard.

[Alle Fragen nach Themen](FAQ-Deutsch)

## Datenschutz und Verbindungen

Kalenderanfragen im Frontend gehen nur an die eigene WordPress-Website. Keine Telemetrie oder externen Schriften. YouTube-Player kontaktieren youtube-nocookie.com erst nach dem Klick auf YouTube-Video laden; externe Vorschaubilder werden nicht geladen. Nach dem Laden erhält YouTube trotzdem Verbindungsdaten des Besuchers. Beim Bearbeiten kann Block-Editor (Gutenberg) die WordPress-Vorschau des Anbieters verwenden. Die eingebettete deckerweb Library bietet einen optionalen, anfangs lokalen Plugin-Katalog. Der deckerweb Updater prüft GitHub-Releases über den WordPress-Updateablauf. Andere Medien behalten ihre ursprünglichen Anbieter. Besuchsmarkierungen verwenden optional localStorage, erst nach bewusster Aktivierung durch den Besucher. Gespeichert werden nur Türchennummern je Website, Kalender und Jahresausgabe; nichts wird übertragen. Abschalten löscht den gespeicherten Zustand, ein zusätzlicher Button löscht die Markierungen. Daily Scripture bleibt freiwillig; Türchenzeit lädt dafür keine Texte aus dem Netz.

Die beiden Schriften sind optional und werden lokal unter der SIL Open Font License mitgeliefert. Eine Installation in der Schriftbibliothek oder ein externer Schriftdienst ist nicht erforderlich. Die Theme-Typografie bleibt erhalten, bis du eine Schrift auswählst. Der Browser lädt Schriftdateien erst für Text, der sie verwendet. Die Plugin-Schriften stehen zur Verfügung, solange Türchenzeit aktiv ist.

## Die Idee hinter Türchenzeit

Türchenzeit möchte Raum für Vorfreude schaffen – mit Inhalten, die Freude bereiten, zum Nachdenken einladen und Herz und Seele ansprechen. Die Gestaltung ist bewusst warm und zurückhaltend. Kitsch gehört ebenso wenig zum Konzept wie Halloween; auch eine reine Winter- oder Schneekulisse ist nicht die Idee hinter dem Plugin. Sein Zuhause ist die Advents- und Weihnachtszeit.

Natürlich dürfen hinter den Türchen auch Gutscheine, Angebote oder Produktideen stehen. Türchenzeit ist jedoch kein auf Verkauf und schnelle Aufmerksamkeit ausgerichtetes Marketing-Plugin. Es bietet ein sorgfältig gestaltetes Werkzeug; die persönliche Note entsteht durch das, was du damit teilst. Ein guter Adventskalender braucht keine lauten Effekte – sondern etwas, auf das sich Menschen jeden Tag freuen können.

## Änderungsverlauf

### 1.0.0 · 2026-10-21

**Neu:**

- 24 tägliche Türchen mit eigenen geschützten Inhalten oder verknüpften Beiträgen und Seiten.
- Sechs Adventsdesigns, Akzentfarben sowie anpassbare Popups, Buttons und Hinweise.
- Bearbeitbare eigenständige Adventswebsite, optionale Header-/Footer-Vorlagen und lokale Schriften.
- Bereitschaftscheck, private Datumsvorschau und unabhängige Jahresausgaben.
- JSON- und ZIP-Import für Tagessätze einschließlich Rasterbildern.
- Optionaler Originalschutz, YouTube-Videos zum bewussten Laden und Daily-Scripture-Integration.
- Einzelinstallation und Multisite mit unabhängigen Einstellungen je Website.

## Entwickler und Unterstützung

Türchenzeit ist ein kleines Herzensprojekt von David Decker, der aus dem Erzgebirge stammt. Die Region wird liebevoll Weihnachtsland genannt: Lichter in den Fenstern, hölzerne Schwibbögen und kunstvolles Handwerk prägen dort die Adventszeit. Diese Wärme soll auch im Plugin spürbar werden – ruhig, einladend und mit Freude an den kleinen Dingen.

Entwickelt und gepflegt von David Decker – DECKERWEB.

[Fragen und Fehler](https://github.com/deckerweb/tuerchenzeit/issues) · [Austausch](https://github.com/deckerweb/tuerchenzeit/discussions) · [Sicherheitslücke vertraulich melden](https://github.com/deckerweb/tuerchenzeit/security/advisories/new)

[Ko-fi](https://ko-fi.com/deckerweb) · [Buy Me a Coffee](https://buymeacoffee.com/daveshine) · [PayPal](https://paypal.me/deckerweb)

## Copyright und Lizenzen

Copyright © 2026 David Decker – DECKERWEB. [GPL-2.0-or-later](https://github.com/deckerweb/tuerchenzeit/blob/main/LICENSE).

Mitgelieferte Komponenten: deckerweb Library 1.1.0 und deckerweb Updater 2.2.0 (GPL-2.0-or-later). Source Serif 4 und Inter werden lokal unter SIL Open Font License 1.1 mitgeliefert. Die freigegebenen SVG-Grafiken verwenden Vektorformen und Schriftpfade, ohne eingebettete Rasterbilder oder externe Ressourcen.

[Font licenses and provenance](https://github.com/deckerweb/tuerchenzeit/blob/main/licenses/FONTS.txt) · [Source Serif 4](https://github.com/deckerweb/tuerchenzeit/blob/main/licenses/Source-Serif-4-OFL.txt) · [Inter](https://github.com/deckerweb/tuerchenzeit/blob/main/licenses/Inter-OFL.txt)
