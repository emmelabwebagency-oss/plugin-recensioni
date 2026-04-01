=== Review Stars Redirect ===
Contributors: emmelabwebagency
Tags: review, stars, rating, redirect, google reviews, elementor
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Mostra 5 stelline cliccabili tramite shortcode. Reindirizza l'utente a link diversi in base alla valutazione selezionata.

== Description ==

**Review Stars Redirect** è un plugin WordPress leggero e compatibile con Elementor che permette di mostrare un sistema di valutazione con 5 stelline cliccabili tramite shortcode.

**Come funziona:**

* L'utente vede 5 stelline gialle classiche stile recensione.
* Se clicca da 1 a 3 stelle, viene reindirizzato a un link configurabile (es. form di recensione interna).
* Se clicca 4 o 5 stelle, viene reindirizzato a un altro link configurabile (es. pagina recensioni Google).

**Caratteristiche:**

* Shortcode semplice: `[review_stars_redirect]`
* Compatibile con Elementor e qualsiasi page builder
* Stelline responsive (desktop e mobile)
* Hover progressivo fluido
* Nessuna dipendenza esterna
* Accessibilità base con aria-label
* Pagina impostazioni nel pannello admin
* CSS e JS separati, caricati solo quando necessario
* Codice pulito e conforme agli standard WordPress

== Installation ==

1. Scarica il file ZIP del plugin.
2. Vai su **Plugin > Aggiungi nuovo > Carica plugin** nel pannello admin di WordPress.
3. Seleziona il file ZIP e clicca **Installa ora**.
4. Attiva il plugin.
5. Vai su **Impostazioni > Review Stars Redirect** per configurare i link di redirect.
6. Copia lo shortcode `[review_stars_redirect]` e incollalo in qualsiasi pagina, post o widget.

== Come usare lo shortcode ==

= In una pagina o post WordPress =
1. Apri l'editor della pagina/post.
2. Aggiungi un blocco "Shortcode" (o "HTML personalizzato").
3. Incolla: `[review_stars_redirect]`
4. Pubblica o aggiorna la pagina.

= In Elementor =
1. Apri la pagina con Elementor.
2. Trascina il widget **Shortcode** nella posizione desiderata.
3. Nel campo shortcode, incolla: `[review_stars_redirect]`
4. Salva e visualizza la pagina.

== Frequently Asked Questions ==

= Posso usare più shortcode nella stessa pagina? =
Sì, puoi inserire più istanze di `[review_stars_redirect]` nella stessa pagina. Funzioneranno tutte in modo indipendente.

= Cosa succede se non configuro i link? =
Se i link non sono configurati, il click sulle stelline non effettuerà alcun redirect.

= Il plugin è compatibile con Elementor? =
Sì, è completamente compatibile. Usa il widget "Shortcode" di Elementor.

= Posso personalizzare lo stile delle stelline? =
Sì, puoi sovrascrivere gli stili CSS nel tuo tema o tramite CSS personalizzato.

== Changelog ==

= 1.0.0 =
* Prima versione del plugin.
* Shortcode con 5 stelline cliccabili.
* Redirect configurabile per valutazioni basse (1-3) e alte (4-5).
* Pagina impostazioni admin con Settings API.
* CSS e JS separati.
* Compatibilità Elementor.

== Upgrade Notice ==

= 1.0.0 =
Prima versione stabile.
