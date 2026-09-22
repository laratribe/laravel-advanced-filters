{{-- A host app's own Livewire panel markup, pointed at via the `view` mount argument. --}}
<div class="my-own-livewire-panel">
    @foreach ($inputViews as $inputView)
        @include($inputView, ['prefix' => 'af'])
    @endforeach
</div>
