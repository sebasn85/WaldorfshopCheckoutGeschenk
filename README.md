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

Entwicklungsstand 0.1.0. Keine Freigabe für den Livebetrieb.
Ein realer Testauftrag und die Prüfung der gespeicherten 0-Euro-Position stehen aus.
Der Auftragseingriff ist auf den regulären `/place-order`-Checkout beschränkt.
Express-Checkout-Flows ohne diese Route sind noch nicht unterstützt.
Bestandsverfügbarkeit wird beim Angebot und vor Auftragserstellung geprüft;
konkurrierende Bestellungen erfordern zusätzlich den nativen Bestandstest.
