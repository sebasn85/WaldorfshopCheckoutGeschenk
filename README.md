# WaldorfshopCheckoutGeschenk

Eigenständiges PlentyONE-Plugin. Kein Teil von WaldorfshopErweiterungen.

- Aktivierung, Varianten-ID und Mindestwarenwert werden pro Plugin-Set konfiguriert.
- Nur EUR; Warenwert brutto nach Gutscheinrabatt, ohne Versandkosten.
- Freiwillige Auswahl im Checkout, standardmäßig aus. Kein Geschenk im Warenkorb.
- Auswahl ist an Sitzung, Warenkorb, Variante und Aktionsgrenze gebunden.
- Eine 0-Euro-Variantenposition wird vor der nativen Auftragsvalidierung ergänzt.
- Separate regulär gekaufte Exemplare derselben Variante bleiben kostenpflichtig.
- Deaktivierung entfernt das Angebot; alte Zustimmung wird nicht verwendet.

## Installation

Plugin installieren, aktivieren und in einem nicht live verknüpften Set bereitstellen.
Datenanbieter „Auswahl“ mit `Ceres::Checkout.BeforeBasketTotals`, „Skript“ mit
`Ceres::Checkout.AfterScriptsLoaded` verbinden. Danach aktivieren, Varianten-ID
und Mindestwarenwert setzen und erneut bereitstellen.

## Teststatus

Entwicklungsstand 0.1.1, bereitgestellt im isolierten Test-Set 441. Keine Freigabe für den Livebetrieb.
Ein realer Testauftrag und die Prüfung der gespeicherten 0-Euro-Position stehen aus.
Der Auftragseingriff ist auf den regulären `/place-order`-Checkout beschränkt.
Express-Checkout-Flows ohne diese Route sind noch nicht unterstützt.
Bestandsverfügbarkeit wird beim Angebot und vor Auftragserstellung geprüft;
konkurrierende Bestellungen erfordern zusätzlich den nativen Bestandstest.

## Prüfung am 11.11.2026

- 27 automatisierte Logikprüfungen bestanden; PHP und JavaScript syntaktisch geprüft.
- Vorschau mit Version 0.1.1: Angebot nur im Checkout, standardmäßig abgewählt.
- Opt-in gespeichert und nach Neuladen erhalten; Opt-out erfolgreich.
- Bei 47 EUR Warenwert ausgeblendet; bei 94 EUR wieder sichtbar und abgewählt.
- Keine Zugabe im Warenkorb; Gesamtsumme nach Auswahl unverändert.
- Ursprüngliche vier Artikel und 99,49 EUR Gesamtsumme wiederhergestellt.
- Kein Auftrag abgesendet. Native Auftragserstellung, persistierter Nullpreis,
  Lagerzuordnung und verschiedene Zahlungsabläufe müssen noch end-to-end geprüft werden.
- Live-Set 439 nicht verändert; alter Test 440 und verworfener Entwicklungsbranch entfernt.
