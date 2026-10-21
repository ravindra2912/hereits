$(function () {

	var scrollIntoView = true;

	toastr.options = {
		"closeButton": true,
		"debug": false,
		"newestOnTop": true,
		"progressBar": true,
		"positionClass": "toast-top-right",
		"preventDuplicates": false,
		"onclick": null,
		"showDuration": "300",
		"hideDuration": "1000",
		"timeOut": "6000",
		"extendedTimeOut": "2000",
		"showEasing": "swing",
		"hideEasing": "linear",
		"showMethod": "fadeIn",
		"hideMethod": "fadeOut"
	};

	$(document).on('submit', '.serversidevalidation, .formaction', (function (e) {
		e.preventDefault();

		var form = this;

		// check form validation start
		const requiredInputs = form.querySelectorAll('input.required, select.required, textarea.required');
		let requiredInputsArray = {};

		requiredInputs.forEach(input => {
			// Check if the element is visible on screen
			const isVisible = !!(input.offsetParent !== null || input.getClientRects().length);

			if (isVisible && !input.value.trim()) {
				let label = input.name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
				requiredInputsArray[input.name] = ['The ' + label + ' field is required.'];
			}
		});

		if (Object.keys(requiredInputsArray).length > 0) {
			obj_handler(requiredInputsArray, form);
			return; // stop form submit or ajax
		}
		// check form validation End

		if ($(form).data('scroll') == false) {
			scrollIntoView = false;
		}

		$.ajax({
			url: this.action,
			type: "POST",
			data: new FormData(this),
			dataType: "json",
			//headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
			contentType: false,
			cache: false,
			processData: false,
			beforeSend: function () {
				remove_error();
				$(form).find('.btn_action #buttonText').addClass('d-none');
				$(form).find('.btn_action #loader').removeClass('d-none');
				$(form).find('.btn_action').prop('disabled', true);
			},
			success: function (result) {
				$(form).find('.btn_action #buttonText').removeClass('d-none');
				$(form).find('.btn_action #loader').addClass('d-none');
				$(form).find('.btn_action').prop('disabled', false);
				remove_error();
				if (result.success) {
					if ($(form).data('tost') == null || $(form).data('tost') == true) {
						toastr.success(result.message);
					}

					//cleare form after submite
					if ($(form).data('reset')) {
						form.reset();
					}

					if ($(form).data('action') == 'reload') {
						setTimeout(function () { location.reload(); }, 1000);
					} else if ($(form).data('action') == 'call') {
						responce(result);
					} else if ($(form).data('action') == 'redirect') {
						if (result.redirect != null) {
							setTimeout(function () { window.location.href = result.redirect }, 1000);
						} else {
							setTimeout(function () { history.back() }, 1000);
						}
					} else if ($(form).data('action') == 'back') {
						setTimeout(function () { history.back() }, 1000);
						// setTimeout(function () { window.location.href = '{{ url()->previous() }}' }, 1000);
					}


				} else {
					error_handler(result.message, form);
					if (typeof ajaxFailResponce === 'function') {
						ajaxFailResponce(result);
					}

					//console.log(result.message);
				}

			},
			error: function (xhr, status, error) {
				console.log(xhr);
				xhr._permissionErrorHandled = true;
				if (xhr.status === 419) {
					toastr.error('Your session has expired, please login again');
					setTimeout(function () {
						location.reload();
					}, 3000);
				} else if (xhr.status === 403) {
					const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unauthorized. You do not have permission to perform this action.';
					toastr.error(msg);
					$(form).find('.btn_action #buttonText').removeClass('d-none');
					$(form).find('.btn_action #loader').addClass('d-none');
					$(form).find('.btn_action').prop('disabled', false);
				} else if (xhr.status === 422 && xhr.responseJSON) {
					if (xhr.responseJSON.errors) {
						obj_handler(xhr.responseJSON.errors, form);
					} else if (xhr.responseJSON.message) {
						toastr.error(xhr.responseJSON.message);
					}
					$(form).find('.btn_action #buttonText').removeClass('d-none');
					$(form).find('.btn_action #loader').addClass('d-none');
					$(form).find('.btn_action').prop('disabled', false);
				} else {
					const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong. Please try again.';
					toastr.error(msg);
					$(form).find('.btn_action #buttonText').removeClass('d-none');
					$(form).find('.btn_action #loader').addClass('d-none');
					$(form).find('.btn_action').prop('disabled', false);
				}
			}
		});
	}));

	$(document).ajaxError(function (event, xhr, settings) {
		if (xhr.status === 403 && !xhr._permissionErrorHandled) {
			xhr._permissionErrorHandled = true;
			const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unauthorized. You do not have permission to perform this action.';
			if (typeof toastr !== 'undefined') {
				toastr.error(msg);
			} else if (typeof Swal !== 'undefined') {
				Swal.fire('Unauthorized', msg, 'error');
			}
		}
	});

	function error_handler(error, form) {
		if (typeof error === 'string') {
			toastr.error(error);
		} else if (typeof error === 'object' && error !== null) {
			obj_handler(error, form);
		} else {
			console.error('Unknown error type:', error);
			toastr.error('Something went wrong. Please try again.');
		}
	}

	function obj_handler(obj, form) {
		const values = Object.values(obj);
		const keys = Object.keys(obj);

		keys.forEach((key, index) => {
			// Handle dot notation (e.g., "benefits.benefit1")
			if (key.includes('.')) {
				const parts = key.split('.');
				key = `${parts[0]}[${parts[1]}]`;
			}

			const formSelector = form.id ? `#${form.id} ` : '';
			const errorMessage = values[index][0];

			const fieldSelectors = [
				`input[name="${key}"]`,
				`select[name="${key}"]`,
				`select[name="${key}[]"]`,
				`textarea[name="${key}"]`
			];

			let field = null;
			for (let selector of fieldSelectors) {
				const el = document.querySelector(formSelector + selector);
				if (el) {
					field = el;
					break;
				}
			}

			if (field) {
				const $field = $(field);
				$field.addClass('is-invalid');

				// Remove any existing error message
				$field.siblings(`#error-${key}`).remove();

				// Add new error message
				$field.after(`<div class="text-danger errors" id="error-${key}">${errorMessage}</div>`);
			}

			// Scroll and focus on first error
			if (index === 0) {
				if (scrollIntoView) {
					form.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
				}
				field?.focus();
			}
		});
	}

	function remove_error() {
		// Remove all validation styling from inputs
		$(".form-control, .form-select, textarea").removeClass("is-invalid");

		// Remove error messages next to the inputs
		$(".errors").remove();
	}

	// onchange remove error
	$(".formaction input, .formaction select, .formaction textarea").on('change keyup', function () {
		$(this).removeClass("is-invalid");

		// Remove any `.errors` element next to the field
		$(this).siblings(".errors").remove();

		// Optionally, remove from parent `.form-group` if needed
		$(this).closest('.form-group').find('.errors').remove();
	});

	/*$(document).ajaxError(function(e) {
	 var e = eval("(" + e.responseText + ")");
	 if (e.message == "CSRF token mismatch.") {
		toastr.error('Your session has expired');
		setTimeout(function() {
		   location.reload();
		}, 3000);
	 }
  });*/

	$('.numeric').on('keypress', function (e) {
		const charCode = e.which ? e.which : e.keyCode;

		// Allow only digits (0–9)
		if (charCode < 48 || charCode > 57) {
			e.preventDefault(); // Block non-numeric input
		}
	});

	// Helper to show auth login modal
	window.showAuthLoginModal = function () {
		const authModalEl = document.getElementById('authModal');
		if (authModalEl) {
			if (typeof switchAuthSection === 'function') {
				switchAuthSection('login');
			} else {
				$('#login-pane').removeClass('d-none');
				$('#register-pane, #forgot-pane').addClass('d-none');
				$('#authModalLabel').text('Welcome Back');
				$('.auth-subtitle').text('Login to your account to continue');
			}

			if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
				bootstrap.Modal.getOrCreateInstance(authModalEl).show();
			} else if (typeof $(authModalEl).modal === 'function') {
				$(authModalEl).modal('show');
			}
		} else if (document.getElementById('loginModal')) {
			const loginModalEl = document.getElementById('loginModal');
			if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
				bootstrap.Modal.getOrCreateInstance(loginModalEl).show();
			} else if (typeof $(loginModalEl).modal === 'function') {
				$(loginModalEl).modal('show');
			}
		} else {
			window.location.href = '/login';
		}
	};

	// Global Favorite Toggle Event Handler
	$(document).on('click', '.btn-toggle-favorite', function (e) {
		e.preventDefault();
		e.stopPropagation();

		// Check if user is logged in
		const isLoggedIn = $('meta[name="user-logged-in"]').attr('content') === '1';
		if (!isLoggedIn) {
			showAuthLoginModal();
			return false;
		}

		const $btn = $(this);
		const type = $btn.data('type');
		const id = $btn.data('id');
		const businessId = $btn.data('business-id') || $('meta[name="business-id"]').attr('content') || '';

		if (!type || !id) return;

		$btn.prop('disabled', true);

		$.ajax({
			url: '/favorite/toggle',
			type: 'POST',
			data: {
				_token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
				favorite_type: type,
				favorite_item_id: id,
				business_id: businessId
			},
			dataType: 'json',
			success: function (res) {
				$btn.prop('disabled', false);
				if (res.success) {
					if (res.is_favorite) {
						$btn.addClass('active').attr('title', 'Remove from favorites');
						$btn.find('i').removeClass('far fa-heart bi-heart text-muted').addClass('fas fa-heart bi-heart-fill text-danger');
					} else {
						$btn.removeClass('active').attr('title', 'Add to favorites');
						$btn.find('i').removeClass('fas fa-heart bi-heart-fill text-danger').addClass('far fa-heart bi-heart text-muted');
					}
					if (typeof toastr !== 'undefined') {
						toastr.success(res.message);
					}
				}
			},
			error: function (xhr) {
				$btn.prop('disabled', false);
				if (xhr.status === 401) {
					showAuthLoginModal();
				} else {
					const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong. Please try again.';
					if (typeof toastr !== 'undefined') {
						toastr.error(msg);
					} else {
						alert(msg);
					}
				}
			}
		});
	});

});

