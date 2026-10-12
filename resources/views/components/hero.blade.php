<div x-data="{
        active: 0,
        berita: {{ $berita->toJson() }},
        next() { this.active = (this.active + 1) % this.berita.length },
        prev() { this.active = (this.active - 1 + this.berita.length) % this.berita.length }
     }" 
     x-show="berita.length > 0"
     class="w-full max-w-6xl mx-auto px-4 py-16 flex flex-col items-center justify-center overflow-hidden">

    <div class="relative w-full h-[500px] md:h-[550px] flex items-center justify-center mt-8">
        
        <template x-for="(item, index) in berita" :key="index">
            <div class="absolute w-[320px] sm:w-[400px] bg-white rounded-[2.5rem] border border-green-100 shadow-2xl p-5 transition-all duration-500 ease-out origin-center flex flex-col justify-between"
                 :class="{
                    'z-30 opacity-100 scale-100 pointer-events-auto': active === index,
                    'z-20 opacity-70 scale-90 -translate-x-[70%] sm:-translate-x-[90%] -rotate-3 pointer-events-none': (active - 1 + berita.length) % berita.length === index,
                    'z-20 opacity-70 scale-90 translate-x-[70%] sm:translate-x-[90%] rotate-3 pointer-events-none': (active + 1) % berita.length === index,
                    'z-10 opacity-0 scale-50 pointer-events-none': active !== index && (active - 1 + berita.length) % berita.length !== index && (active + 1) % berita.length !== index
                 }">
                
                <div class="w-full aspect-[16/10] bg-slate-900 rounded-[2rem] overflow-hidden relative shadow-inner">
                    <img :src="item.image || 'https://images.unsplash.com/photo-1500485035595-cbe6f645feb1?q=80&w=800'" 
                         class="w-full h-full object-cover" alt="Foto Berita">
                    <span class="absolute top-4 left-4 px-3 py-1 bg-green-600 text-white font-extrabold text-[10px] tracking-wider uppercase rounded-full" x-text="item.category"></span>
                </div>

                <div class="pt-6 pb-4 text-center flex-1 flex flex-col justify-between">
                    <h3 class="text-lg font-black text-slate-800 leading-snug line-clamp-2 px-2" x-text="item.title"></h3>
                    
                    <div class="mt-5">
                        <a href="#" :class="active === index ? 'inline-flex' : 'hidden'" class="items-center gap-1 text-sm font-bold text-green-600 hover:text-green-700 mx-auto transition-all">
                            Lihat Detail 
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="flex items-center gap-8 mt-12 z-40">
        <button @click="prev()" class="p-4 rounded-full bg-white border border-slate-200 hover:border-green-500 hover:bg-green-50 text-slate-700 transition shadow-lg active:scale-95">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        
        <div class="flex gap-2">
            <template x-for="(item, index) in berita" :key="index">
                <span class="h-2.5 rounded-full transition-all duration-300"
                      :class="active === index ? 'w-8 bg-green-600' : 'w-2.5 bg-slate-300'"></span>
            </template>
        </div>

        <button @click="next()" class="p-4 rounded-full bg-white border border-slate-200 hover:border-green-500 hover:bg-green-50 text-slate-700 transition shadow-lg active:scale-95">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
</div>