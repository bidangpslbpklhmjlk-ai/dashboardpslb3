<div class="bg-white rounded-2xl border {{ ($warn ?? false) ? 'border-red-200 bg-red-50/40' : 'border-slate-200/80' }} p-5">
    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $label }}</p>
    <p class="mt-1 text-2xl font-bold {{ ($warn ?? false) ? 'text-red-700' : 'text-slate-900' }}">{{ $value }}</p>
    <p class="text-xs text-slate-500 mt-0.5">{{ $sub ?? '' }}</p>
</div>