/**
 * Review Stars Redirect — Frontend JavaScript
 *
 * Gestisce l'interazione con le stelline:
 * - Hover progressivo
 * - Click e redirect in base alla valutazione
 * - Supporto tastiera per accessibilità
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
			var stars = wrapper.querySelectorAll( '.rsr-star' );

			if ( ! stars.length ) {
				return;
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

				// Click: redirect in base alla valutazione.
				star.addEventListener( 'click', function () {
					var value = parseInt( star.getAttribute( 'data-value' ), 10 );
					handleRating( stars, value );
				});

				// Supporto tastiera: Enter e Space.
				star.addEventListener( 'keydown', function ( e ) {
					if ( e.key === 'Enter' || e.key === ' ' ) {
						e.preventDefault();
						var value = parseInt( star.getAttribute( 'data-value' ), 10 );
						handleRating( stars, value );
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
	 * 1-3 stelle => redirect al link per recensioni interne.
	 * 4-5 stelle => redirect al link per recensioni Google.
	 *
	 * @param {NodeList} stars Elenco delle stelle.
	 * @param {number}   value Valore selezionato (1-5).
	 */
	function handleRating( stars, value ) {
		// Feedback visivo: mostra la selezione.
		highlightStars( stars, value, 'rsr-selected' );

		// Determina l'URL di redirect.
		var url = '';

		if ( typeof rsrData === 'undefined' ) {
			return;
		}

		if ( value >= 1 && value <= 3 ) {
			url = rsrData.lowUrl || '';
		} else if ( value >= 4 && value <= 5 ) {
			url = rsrData.highUrl || '';
		}

		// Esegui il redirect se l'URL è configurato.
		// Aggiunge il parametro rsr_rating=N all'URL per passare il valore al form di destinazione.
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
