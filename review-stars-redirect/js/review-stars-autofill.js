/**
 * Review Stars Redirect — Auto-fill Rating Field
 *
 * Legge il parametro "rsr_rating" dall'URL e compila automaticamente
 * i campi del form che hanno l'ID specificato in rsrAutofill.fieldId.
 *
 * Funziona con Elementor Forms, Contact Form 7, WPForms, Gravity Forms, ecc.
 * Nessuna dipendenza esterna.
 */
(function () {
	'use strict';

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
	 * Compila il campo del form con il valore della valutazione.
	 */
	function fillRatingField() {
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

		// Cerca il campo per ID (Elementor Forms usa form-field-{id}).
		var selectors = [
			'#form-field-' + fieldId,
			'#' + fieldId,
			'[name="' + fieldId + '"]',
			'[name="form_fields[' + fieldId + ']"]'
		];

		var filled = false;

		selectors.forEach( function ( selector ) {
			var fields = document.querySelectorAll( selector );
			fields.forEach( function ( field ) {
				field.value = ratingNum;
				// Trigger evento change per compatibilità con framework JS.
				var event = new Event( 'change', { bubbles: true } );
				field.dispatchEvent( event );
				filled = true;
			});
		});

		// Fallback: cerca anche per attributo data-rsr-rating.
		if ( ! filled ) {
			var dataFields = document.querySelectorAll( '[data-rsr-rating]' );
			dataFields.forEach( function ( field ) {
				field.value = ratingNum;
				var event = new Event( 'change', { bubbles: true } );
				field.dispatchEvent( event );
			});
		}
	}

	// Avvia quando il DOM è pronto.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', fillRatingField );
	} else {
		fillRatingField();
	}
})();
