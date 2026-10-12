{{-- resources/views/components/admin-layout.blade.php --}}
<div class="flex h-screen">
    <x-admin-sidebar :items="$items" :activeItem="$activeItem" :type="$type" />

    <div class="flex-1 p-8 overflow-y-auto">
        {{ $slot }}
    </div>
</div>