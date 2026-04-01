/**
 * Review Stars Redirect — Auto-fill Rating Field
 *
 * Legge il parametro "rsr_rating" dall'URL e compila automaticamente
 * i campi del form che hanno l'ID specificato in rsrAutofill.fieldId.
 *
 * Usa MutationObserver + retry con intervallo per gestire form caricati
 * dinamicamente (es. Elementor Forms che renderizza dopo DOMContentLoaded).
 *
 * Funziona con Elementor Forms, Contact Form 7, WPForms, Gravity Forms, ecc.
 * Nessuna dipendenza esterna.
 */
(function () {
	'use strict';

	// Numero massimo di tentativi (ogni 300ms = ~3 secondi totali).
	var MAX_ATTEMPTS = 10;
	var RETRY_INTERVAL = 300;

	/**
	 * Legge un parametro GET dall'URL corrente.
	 *
	 * @param {string} name Nome del parametro.
	 * @return {string|null} Valore del parametro o null.
	 */
	function getUrlParam( name ) {
		var params = new URLSearchParams( window.location.search );
		return params.get( name );
	}

	/**
	 * Restituisce i selettori CSS per trovare il campo del form.
	 *
	 * @param {string} fieldId ID del campo configurato.
	 * @return {Array} Array di selettori CSS.
	 */
	function getSelectors( fieldId ) {
		return [
			'#form-field-' + fieldId,
			'#' + fieldId,
			'[name="' + fieldId + '"]',
			'[name="form_fields[' + fieldId + ']"]',
			'[data-rsr-rating]'
		];
	}

	/**
	 * Cerca e compila il campo del form con il valore della valutazione.
	 *
	 * @param {number} ratingNum Valore della valutazione (1-5).
	 * @param {string} fieldId   ID del campo da compilare.
	 * @return {boolean} true se il campo è stato trovato e compilato.
	 */
	function tryFillField( ratingNum, fieldId ) {
		var selectors = getSelectors( fieldId );
		var filled = false;

		selectors.forEach( function ( selector ) {
			try {
				var fields = document.querySelectorAll( selector );
				fields.forEach( function ( field ) {
					field.value = ratingNum;

					// Trigger eventi per compatibilità con framework JS.
					field.dispatchEvent( new Event( 'input', { bubbles: true } ) );
					field.dispatchEvent( new Event( 'change', { bubbles: true } ) );

					filled = true;
				});
			} catch ( e ) {
				// Selettore non valido, ignora.
			}
		});

		return filled;
	}

	/**
	 * Usa MutationObserver per intercettare l'aggiunta del campo nel DOM.
	 *
	 * @param {number} ratingNum Valore della valutazione.
	 * @param {string} fieldId   ID del campo da compilare.
	 */
	function startObserver( ratingNum, fieldId ) {
		if ( typeof MutationObserver === 'undefined' ) {
			return;
		}

		var observer = new MutationObserver( function () {
			if ( tryFillField( ratingNum, fieldId ) ) {
				observer.disconnect();
			}
		});

		observer.observe( document.body, {
			childList: true,
			subtree: true
		});

		// Timeout di sicurezza: disconnetti dopo 10 secondi.
		setTimeout( function () {
			observer.disconnect();
		}, 10000 );
	}

	/**
	 * Avvia il processo di auto-fill con retry e MutationObserver.
	 */
	function init() {
		var rating = getUrlParam( 'rsr_rating' );

		// Valida che il valore sia un numero tra 1 e 5.
		if ( ! rating ) {
			return;
		}

		var ratingNum = parseInt( rating, 10 );
		if ( isNaN( ratingNum ) || ratingNum < 1 || ratingNum > 5 ) {
			return;
		}

		// Determina l'ID del campo da compilare.
		var fieldId = 'rating';
		if ( typeof rsrAutofill !== 'undefined' && rsrAutofill.fieldId ) {
			fieldId = rsrAutofill.fieldId;
		}

		// Primo tentativo immediato.
		if ( tryFillField( ratingNum, fieldId ) ) {
			return;
		}

		// MutationObserver per intercettare quando il campo viene aggiunto al DOM.
		startObserver( ratingNum, fieldId );

		// Retry con intervallo come ulteriore sicurezza per form lenti.
		var attempts = 0;
		var retryTimer = setInterval( function () {
			attempts++;

			if ( tryFillField( ratingNum, fieldId ) || attempts >= MAX_ATTEMPTS ) {
				clearInterval( retryTimer );
			}
		}, RETRY_INTERVAL );
	}

	// Avvia quando il DOM è pronto.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();
