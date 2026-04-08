<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 rounded-sm font-semibold text-xs uppercase tracking-widest shadow-sm disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
