document.addEventListener('DOMContentLoaded', () => {
    const loader = document.getElementById('page-loader');
    if (loader) {
        loader.style.display = 'none';
    }

    const buttons = document.querySelectorAll('.action-btn');

    buttons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            if (btn.disabled) return;

            const id = btn.dataset.id;
            const status = btn.dataset.status;
            const isPriceRequired = btn.dataset.reqPrice === '1';

            if (!id || !status) return;

            // Get note
            const noteInput = document.getElementById(`expert_note_${id}`);
            const expertNote = noteInput ? noteInput.value : null;

            let amount = null;
            let paymentType = null;

            // Check price requirement
            if (isPriceRequired && (status === 'completed' || status === 'complete_and_next')) {
                const amountInput = document.getElementById(`amount_${id}`);
                const paymentInput = document.getElementById(`payment_type_${id}`);

                amount = amountInput ? amountInput.value : '';
                paymentType = paymentInput ? paymentInput.value : '';

                if (!amount) {
                    if (window.toastr) toastr.error('Please enter amount.');
                    else alert('Please enter amount.');
                    return;
                }
                if (!paymentType) {
                    if (window.toastr) toastr.error('Please select payment type.');
                    else alert('Please select payment type.');
                    return;
                }
            }

            // Route check
            if (typeof route === 'undefined') {
                console.error('Ziggy route function is not defined.');
                if (window.toastr) toastr.error('Configuration Error: Routes not loaded.');
                else alert('Configuration Error: Routes not loaded.');
                return;
            }

            // Visual feedback
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            btn.style.opacity = '0.7';
            btn.disabled = true;

            try {
                const response = await fetch(route('expert.status.update'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id,
                        status,
                        expert_note: expertNote,
                        amount: amount,
                        payment_type: paymentType
                    })
                });

                const result = await response.json();

                if (result.success) {
                    if (window.toastr) toastr.success('Action successful!');
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    if (window.toastr) toastr.error('Action failed: ' + (result.message || 'Unknown error'));
                    else alert('Action failed: ' + (result.message || 'Unknown error'));
                    // Reset button state
                    btn.innerHTML = originalText;
                    btn.style.opacity = '1';
                    btn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                if (window.toastr) toastr.error('Network or Server Error');
                else alert('Network or Server Error');
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
                btn.disabled = false;
            }
        });
    });
});

