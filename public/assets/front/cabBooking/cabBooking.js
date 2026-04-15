$(document).ready(function () {
    const businessName = $('meta[name="business-name"]').attr('content') || 'Store';
    let businessContact = $('meta[name="business-contact"]').attr('content') || '';

    // WhatsApp Contact Form Handler
    $('#whatsappContactForm').on('submit', function (e) {
        e.preventDefault();

        const name = $('#contact_name').val();
        const subject = $('#contact_subject').val();
        const message = $('#contact_message').val();

        // Clean the number (remove non-digits)
        let cleanedContact = businessContact.replace(/\D/g, '');

        // Prepend 91 if it's 10 digits (India context)
        if (cleanedContact.length === 10) {
            cleanedContact = '91' + cleanedContact;
        }

        const whatsappMessage = `*New Contact Inquiry for ${businessName}*%0A%0A` +
            `*Name:* ${name}%0A` +
            `*Subject:* ${subject}%0A%0A` +
            `*Message:*%0A${message}`;

        const whatsappUrl = `https://wa.me/${cleanedContact}?text=${whatsappMessage}`;

        // Open in new tab
        window.open(whatsappUrl, '_blank');
    });

    // Cab Booking Dynamic Search Simulation
    $('#cabSearchBtn').on('click', function(e) {
        e.preventDefault();
        
        let pickup = $('#pickupLocation').val();
        let drop = $('#dropLocation').val();
        
        if(!pickup) {
            toastr.error('Please enter pickup location');
            $('#pickupLocation').focus();
            return;
        }
        
        if(!drop) {
            toastr.error('Please enter drop location');
            $('#dropLocation').focus();
            return;
        }
        
        // Show success toast simulating a search
        toastr.success('Searching for rides from ' + pickup + ' to ' + drop + '...');
        
        // Let form submission simulate loading
        let btn = $(this);
        let originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Searching...');
        btn.prop('disabled', true);
        
        setTimeout(function() {
            btn.html(originalText);
            btn.prop('disabled', false);
        }, 1500);
    });
});
