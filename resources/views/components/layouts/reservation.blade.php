@extends('components.layouts.app')

@section('title', 'Parking Reservation')

@push('style')
    {{-- <script type="text/javascript" src="https://js.stripe.com/v2/"></script> --}}
    <script src="https://js.stripe.com/v3/"></script>
    <script src="{{ asset('js/reservation.js') }}"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('reservation', reservation);
            csrf_token = '{{ csrf_token() }}';
            stripePublishableKey = '{{ env('STRIPE_PUBLISH_KEY') }}';
            {{-- quotesUrl = '{{ route('api.quotes') }}';
            reservation_url = '{{ route('api.reservation') }}';
            createPaymentIntent = '{{ route('create_payment_intent') }}';
            signinUrl = '{{ route('signin') }}';
            registerUrl = '{{ route('signup') }}';  --}}
        })
    </script>
@endpush

@section('content')
    <div class="bg-white reservation summary">
        {{ $slot }}
    </div>
@endsection