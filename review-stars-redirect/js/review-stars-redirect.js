/**
 * Review Stars Redirect — Frontend JavaScript v2.0.0
 *
 * Gestisce l'interazione con le stelline:
 * - Hover progressivo
 * - Click e redirect in base alla mappa stella => URL (per istanza)
 * - Supporto tastiera per accessibilità
 * - Supporto multipli shortcode nella stessa pagina
 *
 * Nessuna dipendenza esterna (no jQuery).
 */
(function () {
	'use strict';

	/**
	 * Inizializza tutte le istanze dello shortcode nella pagina.
	 */
	function init() {
		var wrappers = document.querySelectorAll( '.rsr-stars-wrapper' );

		wrappers.forEach( function ( wrapper ) {
			var stars  = wrapper.querySelectorAll( '.rsr-star' );
			var mapRaw = wrapper.getAttribute( 'data-rsr-map' );
			var starMap = {};

			if ( ! stars.length ) {
				return;
			}

			// Decodifica la mappa stella => URL dal data attribute.
			if ( mapRaw ) {
				try {
					starMap = JSON.parse( mapRaw );
				} catch ( e ) {
					starMap = {};
				}
			}

			// Hover: evidenzia progressivamente le stelle.
			stars.forEach( function ( star ) {
				star.addEventListener( 'mouseenter', function () {
					var value = parseInt( star.getAttribute( 'data-value' ), 10 );
					highlightStars( stars, value, 'rsr-hover' );
				});

				star.addEventListener( 'mouseleave', function () {
					clearHighlight( stars, 'rsr-hover' );
				});

				// Click: redirect in base alla mappa.
				star.addEventListener( 'click', function () {
					var value = parseInt( star.getAttribute( 'data-value' ), 10 );
					handleRating( stars, value, starMap );
				});

				// Supporto tastiera: Enter e Space.
				star.addEventListener( 'keydown', function ( e ) {
					if ( e.key === 'Enter' || e.key === ' ' ) {
						e.preventDefault();
						var value = parseInt( star.getAttribute( 'data-value' ), 10 );
						handleRating( stars, value, starMap );
					}
				});
			});
		});
	}

	/**
	 * Evidenzia le stelle fino al valore specificato.
	 *
	 * @param {NodeList} stars    Elenco delle stelle.
	 * @param {number}   upTo    Valore fino al quale evidenziare.
	 * @param {string}   cssClass Classe CSS da aggiungere.
	 */
	function highlightStars( stars, upTo, cssClass ) {
		stars.forEach( function ( star ) {
			var val = parseInt( star.getAttribute( 'data-value' ), 10 );
			if ( val <= upTo ) {
				star.classList.add( cssClass );
			} else {
				star.classList.remove( cssClass );
			}
		});
	}

	/**
	 * Rimuove l'evidenziazione da tutte le stelle.
	 *
	 * @param {NodeList} stars    Elenco delle stelle.
	 * @param {string}   cssClass Classe CSS da rimuovere.
	 */
	function clearHighlight( stars, cssClass ) {
		stars.forEach( function ( star ) {
			star.classList.remove( cssClass );
		});
	}

	/**
	 * Gestisce la selezione della valutazione e il redirect.
	 *
	 * Legge la mappa stella => URL dal data attribute dell'istanza
	 * e reindirizza all'URL corrispondente.
	 *
	 * @param {NodeList} stars   Elenco delle stelle.
	 * @param {number}   value   Valore selezionato (1-5).
	 * @param {Object}   starMap Mappa stella => URL.
	 */
	function handleRating( stars, value, starMap ) {
		// Feedback visivo: mostra la selezione.
		highlightStars( stars, value, 'rsr-selected' );

		// Cerca l'URL nella mappa per il valore selezionato.
		var url = starMap[ String( value ) ] || '';

		// Esegui il redirect se l'URL è configurato.
		// Aggiunge il parametro rsr_rating=N all'URL per passare il valore al form.
		if ( url ) {
			var separator = url.indexOf( '?' ) === -1 ? '?' : '&';
			var redirectUrl = url + separator + 'rsr_rating=' + value;

			// Breve ritardo per mostrare il feedback visivo prima del redirect.
			setTimeout( function () {
				window.location.href = redirectUrl;
			}, 250 );
		}
	}

	// Avvia quando il DOM è pronto.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();
