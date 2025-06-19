( function( $, settings ) {
	function CustomLogin() {
		this.$fpForm = $( '#custom-login-fp-form' );
		this.$rpForm = $( '#custom-login-rp-form' );
		this.$lgForm = $( '#custom-login-lg-form' );
	}

	CustomLogin.prototype.init = function() {
		this.$fpForm.on( 'submit', this.onFormSubmit.bind( this ) );
		this.$rpForm.on( 'submit', this.onFormSubmit.bind( this ) );
		this.$lgForm.on( 'submit', this.onFormSubmit.bind( this ) );
	}

	CustomLogin.prototype.onFormSubmit = function( e ) {
		e.preventDefault();

		var $form = $( e.target );

		$( 'input[type=submit]', $form ).attr( 'disabled', 'disabled' );

		var data = $form.serialize();

		$.ajax( {
			url: settings.ajaxurl,
			type: 'POST',
			data: data
		} ).done( this.onFormSubmitComplete.bind( this, $form ) );

		return false;
	}

	CustomLogin.prototype.onFormSubmitComplete = function( $form, response ) {
		console.log('got response',response,$form);
		if ( ! response.data.hasOwnProperty( 'message' ) && ! response.data.hasOwnProperty( 'redirect' ) ) {
			console.error( "Response object's 'data' attribute misses required 'message' or 'redirect' attribute. Failing silently." );

			return;
		}

		if ( response.data.message ) {
			var messageClass = 'success';

			if ( ! response.success ) {
				console.log('error here');
				messageClass = 'error';
			}
			console.log('el',$( '.custom-login-fp__messages p', $form ));
			$( '.custom-login-fp__messages p', $form ).removeClass( 'error' ).removeClass( 'success' ).addClass( messageClass );

			$( '.custom-login-fp__messages p', $form ).html( response.data.message );

			$form[0].reset();
			$( 'input[type=submit]', $form ).removeAttr( 'disabled' );
			if ( messageClass === 'success' ) {
				$('.form__wrapper form fieldset, .form__wrapper form input').hide();
			}
		}

		if ( response.data.redirect ) {
			window.location.href = response.data.redirect;

			return;
		}
	}

	var instance = new CustomLogin();
	instance.init();
} ( jQuery, _nuxCustomLoginSettings ) );
