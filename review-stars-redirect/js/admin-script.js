/**
 * Review Stars Redirect — Admin Script v2.0.0
 *
 * Gestisce l'aggiunta e rimozione dinamica delle regole di redirect
 * nella pagina di modifica di un'istanza shortcode.
 */
(function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var container = document.getElementById( 'rsr-rules-container' );
		var addBtn    = document.getElementById( 'rsr-add-rule' );

		if ( ! container || ! addBtn ) {
			return;
		}

		/**
		 * Restituisce il prossimo indice disponibile per una nuova regola.
		 */
		function getNextIndex() {
			var rows = container.querySelectorAll( '.rsr-rule-row' );
			var max  = -1;
			for ( var i = 0; i < rows.length; i++ ) {
				var idx = parseInt( rows[ i ].getAttribute( 'data-index' ), 10 );
				if ( idx > max ) {
					max = idx;
				}
			}
			return max + 1;
		}

		/**
		 * Aggiorna i numeri delle regole visualizzati nell'header.
		 */
		function updateRuleNumbers() {
			var rows = container.querySelectorAll( '.rsr-rule-row' );
			for ( var i = 0; i < rows.length; i++ ) {
				var header = rows[ i ].querySelector( '.rsr-rule-header strong' );
				if ( header ) {
					header.textContent = rsrAdmin.ruleLabel + ' ' + ( i + 1 );
				}
			}
		}

		/**
		 * Crea l'HTML di una nuova regola.
		 */
		function createRuleRow( index ) {
			var row = document.createElement( 'div' );
			row.className = 'rsr-rule-row';
			row.setAttribute( 'data-index', index );

			var html = '';
			html += '<div class="rsr-rule-header">';
			html += '<strong>' + rsrAdmin.ruleLabel + ' ' + ( container.querySelectorAll( '.rsr-rule-row' ).length + 1 ) + '</strong>';
			html += '<button type="button" class="button rsr-remove-rule" title="' + rsrAdmin.removeTitle + '">&times;</button>';
			html += '</div>';
			html += '<div class="rsr-rule-content">';
			html += '<div class="rsr-rule-stars">';
			html += '<label>' + rsrAdmin.starLabel + '</label>';
			html += '<div class="rsr-star-checkboxes">';
			for ( var s = 1; s <= 5; s++ ) {
				html += '<label class="rsr-star-checkbox">';
				html += '<input type="checkbox" name="rsr_rule_stars[' + index + '][]" value="' + s + '" />';
				html += ' ' + s + ' &#9733;';
				html += '</label>';
			}
			html += '</div>'; // .rsr-star-checkboxes
			html += '</div>'; // .rsr-rule-stars
			html += '<div class="rsr-rule-url">';
			html += '<label>' + rsrAdmin.urlLabel + '</label>';
			html += '<input type="url" name="rsr_rule_urls[' + index + ']" value="" class="regular-text" placeholder="' + rsrAdmin.urlPlaceholder + '" />';
			html += '</div>'; // .rsr-rule-url
			html += '</div>'; // .rsr-rule-content

			row.innerHTML = html;
			return row;
		}

		// Aggiungi nuova regola.
		addBtn.addEventListener( 'click', function () {
			var index = getNextIndex();
			var row   = createRuleRow( index );
			container.appendChild( row );
			updateRuleNumbers();
		});

		// Rimuovi regola (delegazione eventi).
		container.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '.rsr-remove-rule' );
			if ( ! btn ) {
				return;
			}
			var row = btn.closest( '.rsr-rule-row' );
			if ( row ) {
				row.remove();
				updateRuleNumbers();
			}
		});
	});
})();
