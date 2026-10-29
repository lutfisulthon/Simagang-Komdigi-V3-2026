{{--
    Diagram batang HTML/CSS. Dipakai di dalam scope wsReports (public/js/workspace/reports.js).
    Variabel: $expr (getter berbentuk { cols, ticks }), $label (judul untuk pembaca layar).
--}}
<div class="ws-bc" role="img" :aria-label="'{{ $label }}: ' + {{ $expr }}.cols.map((c) => c.label + ' ' + c.value).join(', ')">
    <div class="ws-bc__plot">
        <template x-for="t in {{ $expr }}.ticks" :key="t.v">
            <div class="ws-bc__tick" :style="{ bottom: t.pct + '%' }"><span x-text="t.v"></span></div>
        </template>
        <div class="ws-bc__cols">
            <template x-for="c in {{ $expr }}.cols" :key="c.label">
                <div class="ws-bc__col">
                    <div class="ws-bc__bar" :style="{ height: c.pct + '%' }" :title="c.label + ': ' + c.value + ' tugas'">
                        <span class="ws-bc__val" x-text="c.value"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>
    <div class="ws-bc__labels">
        <template x-for="c in {{ $expr }}.cols" :key="c.label">
            <span class="ws-bc__label" x-text="c.label"></span>
        </template>
    </div>
</div>
