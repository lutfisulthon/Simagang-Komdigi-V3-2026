{{--
    Ikon gerigi + panel pengaturan tampilan (opsi Mode gelap).
    Perilakunya ada di public/js/dark-mode.js (pastikan layouts.partials.theme-head dimuat di <head>).
--}}
@php($themeMenuId = 'sm-theme-' . \Illuminate\Support\Str::random(6))
<div {{ $attributes->class(['sm-theme']) }} data-theme-menu>
    <button type="button" class="sm-theme__btn" data-theme-trigger aria-haspopup="true" aria-expanded="false"
            aria-controls="{{ $themeMenuId }}" aria-label="Pengaturan tampilan" title="Pengaturan tampilan">
        <i class="fas fa-gear" aria-hidden="true"></i>
    </button>
    <div class="sm-theme__panel" id="{{ $themeMenuId }}" role="group" aria-label="Pengaturan tampilan" hidden>
        <p class="sm-theme__title">Tampilan</p>
        <div class="sm-theme__row">
            <span class="sm-theme__icon" aria-hidden="true"><i class="fas fa-moon"></i></span>
            <span class="sm-theme__text" id="{{ $themeMenuId }}-label">
                <strong>Mode gelap</strong>
                <small>Nyaman dipandang di ruangan redup</small>
            </span>
            <button type="button" role="switch" class="sm-switch" data-theme-switch aria-checked="false" aria-labelledby="{{ $themeMenuId }}-label"><span></span></button>
        </div>
    </div>
</div>
