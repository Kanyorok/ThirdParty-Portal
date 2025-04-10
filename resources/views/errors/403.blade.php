<div style="min-height: 100vh;min-width: 100%">
    <div style="text-align: center;vertical-align: center; margin-top: 30vh;">
        <h1 style="font-size: xxx-large">403</h1>
        <h2>This action is unauthorized, redirecting back if not <a href="{{ (url()->previous())??route('home') }}">click
                here</a></h2>
    </div>
</div>
@php
    request()->session()->flash('fail','action was unauthorized')
@endphp
<script>
    window.addEventListener("load", (event) => {
        setTimeout(() => {
            window.location.replace('{{ (url()->previous())??route('home') }}');
        }, 1000);
    });
</script>

