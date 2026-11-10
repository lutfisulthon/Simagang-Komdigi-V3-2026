{{-- Avatar yang dirender Alpine dari objek UserMini/InternMini. `user` = ekspresi JS, mis. "a" di dalam x-for. --}}
@props(['user' => 'user', 'size' => 'sm'])

<span {{ $attributes->class(['ws-avatar', 'ws-avatar--' . $size]) }} :title="{{ $user }}.name">
    <template x-if="{{ $user }}.avatar_url"><img :src="{{ $user }}.avatar_url" :alt="{{ $user }}.name" loading="lazy"></template>
    <template x-if="!{{ $user }}.avatar_url"><span x-text="WS.initials({{ $user }}.name)"></span></template>
</span>
<x-workspace.page-css />
