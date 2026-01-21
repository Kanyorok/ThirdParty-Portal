<div style="min-height: 100vh;min-width: 100%">
    <div style="text-align: center;vertical-align: center; margin-top: 30vh;">
        <h1 style="font-size: xxx-large">403</h1>
        <h2>Sorry, you don't have the permissions required, Authorization</h2>
        <a href="{{ (url()->previous())??route('home') }}">click here to go back</a>
    </div>
</div>
@php
if(request()->hasSession()){
request()->session()->flash('fail','action was unauthorized');
}
@endphp
<script>
    window.addEventListener("load", (event) => {
        setTimeout(() => {
            window.location.replace('{{ (url()->previous())??route('
                home ') }}');
        }, 1000);
    });
</script>