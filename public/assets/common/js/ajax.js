$(function () {

	var scrollIntoView = true;

	$(document).on('change', '.status-switch-input', function () {
		const checkbox = $(this);
		const form = checkbox.closest('.status-switch-form');

		if (!form.length) {
			return;
		}

		form.data('previous-checked', !checkbox.is(':checked'));
		form.find('input[name="status"]').val(checkbox.is(':checked') ? checkbox.data('active-value') : checkbox.data('inactive-value'));
		form.trigger('submit');
	});

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
					if ($(form).hasClass('status-switch-form')) {
						resetStatusSwitch(form);
					}
					error_handler(result.message, form);
					if (typeof ajaxFailResponce === 'function') {
						ajaxFailResponce(result, form);
					}

					//console.log(result.message);
				}

			},
			error: function (xhr, status, error) {
				console.log(xhr);
				if ($(form).hasClass('status-switch-form')) {
					resetStatusSwitch(form);
				}
				if (xhr.status === 419) {
					toastr.error('Your session has expired, please login again');
					setTimeout(function () {
						location.reload();
					}, 3000);
				} else {
					toastr.error('Something Wrong');
					$(form).find('.btn_action #buttonText').removeClass('d-none');
					$(form).find('.btn_action #loader').addClass('d-none');
					$(form).find('.btn_action').prop('disabled', false);
				}
			}
		});
	}));

	function resetStatusSwitch(form) {
		const switchInput = $(form).find('.status-switch-input');

		if (!switchInput.length) {
			return;
		}

		const previousChecked = $(form).data('previous-checked');

		if (typeof previousChecked === 'undefined') {
			return;
		}

		switchInput.prop('checked', previousChecked);
		$(form).find('input[name="status"]').val(previousChecked ? switchInput.data('active-value') : switchInput.data('inactive-value'));
	}

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




});
