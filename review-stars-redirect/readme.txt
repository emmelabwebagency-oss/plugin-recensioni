=== Review Stars Redirect ===
Contributors: emmelabwebagency
Tags: review, stars, rating, redirect, google reviews, elementor
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Crea multipli shortcode con 5 stelline cliccabili, ognuno con regole di redirect personalizzabili per ogni stella. Compatibile con Elementor.

== Description ==

**Review Stars Redirect** è un plugin WordPress leggero e compatibile con Elementor che permette di creare multipli sistemi di valutazione con 5 stelline cliccabili tramite shortcode.

**Come funziona (v2.0):**

* Crea quanti shortcode vuoi dalla pagina impostazioni.
* Per ogni shortcode, configura le regole di redirect: scegli quali stelle (1-5) associare a quale URL.
* Ogni shortcode è indipendente e può avere regole diverse.
* Inserisci lo shortcode in qualsiasi pagina, post o widget Elementor.

**Esempio:**

* Shortcode "Homepage": stelle 1-3 => form recensione interna, stelle 4-5 => Google recensioni.
* Shortcode "Pagina Contatti": stella 1 => form reclamo, stelle 2-3 => form feedback, stelle 4-5 => Google.

**Caratteristiche:**

* Multipli shortcode con configurazioni indipendenti
* Regole di redirect flessibili per ogni stella (1-5)
* Compatibile con Elementor e qualsiasi page builder
* Stelline responsive (desktop e mobile)
* Hover progressivo fluido
* Nessuna dipendenza esterna
* Accessibilità base con aria-label e navigazione tastiera
* Pagina impostazioni admin con CRUD completo
* CSS e JS separati, caricati solo quando necessario
* Codice pulito e conforme agli standard WordPress

== Installation ==

1. Scarica il file ZIP del plugin.
2. Vai su **Plugin > Aggiungi nuovo > Carica plugin** nel pannello admin di WordPress.
3. Seleziona il file ZIP e clicca **Installa ora**.
4. Attiva il plugin.
5. Vai su **Impostazioni > Review Stars Redirect**.
6. Crea un nuovo shortcode cliccando **Crea Shortcode**.
7. Configura le regole di redirect per le stelle che vuoi.
8. Copia lo shortcode generato e incollalo nella pagina desiderata.

== Come usare ==

= Creare un nuovo shortcode =
1. Vai su **Impostazioni > Review Stars Redirect**.
2. Inserisci un nome (es. "Homepage") e clicca **Crea Shortcode**.
3. Nella pagina di modifica, configura le regole:
   - Per ogni regola, seleziona le stelle (es. 1, 2, 3) e inserisci l'URL di redirect.
   - Clicca **+ Aggiungi Regola** per aggiungere altre regole.
4. Clicca **Salva Impostazioni**.
5. Copia lo shortcode mostrato in alto (es. `[review_stars_redirect id="rsr_abc12345"]`).

= In una pagina o post WordPress =
1. Apri l'editor della pagina/post.
2. Aggiungi un blocco "Shortcode" (o "HTML personalizzato").
3. Incolla lo shortcode copiato.
4. Pubblica o aggiorna la pagina.

= In Elementor =
1. Apri la pagina con Elementor.
2. Trascina il widget **Shortcode** nella posizione desiderata.
3. Nel campo shortcode, incolla lo shortcode copiato.
4. Salva e visualizza la pagina.

== Frequently Asked Questions ==

= Posso usare più shortcode nella stessa pagina? =
Sì, puoi inserire più shortcode diversi nella stessa pagina. Ognuno funzionerà in modo indipendente con le proprie regole di redirect.

= Posso avere regole diverse per ogni stella? =
Sì! Per ogni shortcode puoi creare quante regole vuoi. Ogni regola associa una o più stelle a un URL di redirect specifico.

= Cosa succede se non configuro i link? =
Se un URL non è configurato per una stella, il click non effettuerà alcun redirect.

= Il plugin è compatibile con Elementor? =
Sì, è completamente compatibile. Usa il widget "Shortcode" di Elementor.

= Posso personalizzare lo stile delle stelline? =
Sì, puoi sovrascrivere gli stili CSS nel tuo tema o tramite CSS personalizzato.

== Changelog ==

= 2.0.0 =
* Architettura multi-istanza: crea quanti shortcode vuoi.
* Regole di redirect flessibili per ogni stella (1-5).
* Interfaccia admin rinnovata con CRUD completo.
* Pulsante "Aggiungi Regola" per aggiungere regole dinamicamente.
* Rimosso auto-fill campo form (non compatibile con Elementor Forms).
* Rimosso shortcode [review_stars_rating].
* JavaScript admin per gestione dinamica delle regole.
* Supporto multipli shortcode nella stessa pagina.

= 1.0.0 =
* Prima versione del plugin.
* Shortcode con 5 stelline cliccabili.
* Redirect configurabile per valutazioni basse (1-3) e alte (4-5).
* Pagina impostazioni admin con Settings API.
* CSS e JS separati.
* Compatibilità Elementor.

== Upgrade Notice ==

= 2.0.0 =
Aggiornamento maggiore: ora puoi creare multipli shortcode con regole personalizzate per ogni stella. Rivedi la configurazione dopo l'aggiornamento.
