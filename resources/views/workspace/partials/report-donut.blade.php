{{--
    Donat (conic-gradient) + legenda berisi angka. Dipakai di dalam scope wsReports.
    Variabel: $expr (getter berbentuk { gradient, total, legend }), $center (ekspresi JS angka di tengah), $caption (teks kecil di tengah).
--}}
<div class="ws-rp__donutwrap">
    <div class="ws-donut" :style="{ background: {{ $expr }}.gradient }" role="img"
         :aria-label="{{ $expr }}.legend.map((s) => s.label + ' ' + s.value).join(', ')">
        <div class="ws-donut__center">
            <strong x-text="{{ $center }}"></strong>
            <span>{{ $caption }}</span>
        </div>
    </div>
    <ul class="ws-rp__legend">
        <template x-for="s in {{ $expr }}.legend" :key="s.key">
            <li>
                <i class="ws-rp__swatch" :style="{ background: s.color }" aria-hidden="true"></i>
                <span x-text="s.label"></span>
                <strong x-text="s.value"></strong>
                <span class="ws-text-faint" x-text="'(' + s.pct + '%)'"></span>
            </li>
        </template>
    </ul>
</div>
