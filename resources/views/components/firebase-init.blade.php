@if(config('services.firebase.api_key') && config('services.firebase.project_id'))
<script>
    window.firebaseConfig = {
        apiKey: "{{ config('services.firebase.api_key') }}",
        authDomain: "{{ config('services.firebase.auth_domain') }}",
        projectId: "{{ config('services.firebase.project_id') }}",
        storageBucket: "{{ config('services.firebase.storage_bucket') }}",
        messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
        appId: "{{ config('services.firebase.app_id') }}",
        vapidKey: "{{ config('services.firebase.vapid_key') }}"
    };
</script>
<script type="module" src="{{ asset('assets/common/js/firebase-notification.js') }}?v={{ file_exists(public_path('assets/common/js/firebase-notification.js')) ? filemtime(public_path('assets/common/js/firebase-notification.js')) : time() }}"></script>
@endif
