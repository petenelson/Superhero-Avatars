( function( $ ) {

	'use strict';

	var Meaty_Avatars_Admin = {

		table: null,

		init: function() {
			// Only run the 
			this.table = $( document.getElementById( 'meaty-avatars-form-table' ) );
			if ( this.table.length === 0 ) {
				return;
			}

			this.bindEvents();
		},

		/**
		 * Binds event handlers to element
		 * @return {void}
		 */
		bindEvents: function() {
			var self = this;

			// Handler for new avatar link
			this.table.on( 'click', '.new-meaty-avatar', function( e ) {
				e.preventDefault();
				self.getNewAvatar( self );
			} );
		},

		/**
		 * Gets a new avatar tag and generates a new img tag on the profile
		 * @return {void}
		 */
		getNewAvatar: function( self ) {
			// Turn on the spinner
			self.table.find( '.spinner' ).addClass( 'is-active' ).removeClass( 'hidden' );

			$.get( 'https://baconmockup.com/images-api/image-tags/', function( response ) {

				// Turn off the spinner
				self.table.find( '.spinner' ).removeClass( 'is-active' ).addClass( 'hidden' );

				if ( response && response.data ) {

					var tag = response.data[ Math.floor( Math.random()*response.data.length ) ];

					if ( tag && '' !== tag ) {
						// Store the new tag in the form
						self.table.find( '.meat-avatar-tag' ).val( tag );

						self.table.find( '.meaty-avatar-tag-label em').text( tag );
						
						// Generate new img tag
						var url = 'https://baconmockup.com/128/128/' + tag;
						var img = $( '<img />' ).attr( 'src', url );
						
						// Show the image preview
						self.table.find( '.meaty-avatar-container' ).html( '' ).append( img );
					}

				}

			});
		}

	};

	Meaty_Avatars_Admin.init();	

}) ( jQuery );