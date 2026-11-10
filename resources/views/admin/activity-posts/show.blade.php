@extends('layouts.app')

@section('title','Detail Aktivitas')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-cyan-50 py-10">

<div class="max-w-7xl mx-auto px-6">


<!-- ================= HEADER ================= -->

<div class="flex flex-col lg:flex-row justify-between gap-6 mb-10">


<div>

<h1 class="text-4xl font-extrabold text-slate-900">
Detail Aktivitas
</h1>


<p class="text-gray-500 mt-2">
Preview dan pengelolaan konten aktivitas Simagang
</p>

</div>





<div class="flex flex-wrap gap-3">



<!-- EDIT -->

<a href="{{ route('admin.aktivitas.edit',$post) }}"
class="inline-flex items-center gap-3 px-6 py-3.5 rounded-2xl
bg-gradient-to-r from-blue-600 to-blue-500
text-white font-bold
shadow-lg shadow-blue-500/30
hover:-translate-y-1
hover:shadow-blue-500/50
transition">


<span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">

<i class="fas fa-pen"></i>

</span>


Edit Konten

</a>





<!-- PREVIEW -->


<a href="{{ route('public.activity.show',$post->slug) }}"
target="_blank"

class="inline-flex items-center gap-3 px-6 py-3.5 rounded-2xl
bg-gradient-to-r from-emerald-500 to-green-500
text-white font-bold
shadow-lg shadow-green-500/30
hover:-translate-y-1
hover:shadow-green-500/50
transition">


<span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">

<i class="fas fa-eye"></i>

</span>


Preview Website

</a>







<!-- DELETE -->

<form action="{{ route('admin.aktivitas.destroy',$post) }}"
method="POST"
onsubmit="return confirm('Yakin ingin menghapus aktivitas ini?')">


@csrf
@method('DELETE')


<button

class="inline-flex items-center gap-3 px-6 py-3.5 rounded-2xl
bg-gradient-to-r from-red-600 to-rose-500
text-white font-bold
shadow-lg shadow-red-500/30
hover:-translate-y-1
hover:shadow-red-500/50
transition">


<span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">

<i class="fas fa-trash"></i>

</span>


Hapus


</button>


</form>







<!-- BACK -->

<a href="{{ route('admin.aktivitas.index') }}"

class="inline-flex items-center gap-3 px-6 py-3.5 rounded-2xl

bg-white
border border-gray-200

text-gray-700 font-bold

shadow-sm

hover:bg-gray-50

hover:-translate-y-1

transition">


<span class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center">

<i class="fas fa-arrow-left"></i>

</span>


Kembali


</a>



</div>


</div>







<!-- ================= HERO + INFO ================= -->


<div class="grid lg:grid-cols-[1.2fr_.8fr] gap-8">






<!-- MEDIA -->

<div class="relative overflow-hidden rounded-[35px]
shadow-2xl bg-slate-900 min-h-[560px]">



@if($post->type == 'youtube')


@php

preg_match(
'/(?:youtu\.be\/|v=)([^&]+)/',
$post->youtube_url,
$match
);


$videoId=$match[1] ?? '';

@endphp



<iframe

src="https://www.youtube.com/embed/{{ $videoId }}"

class="w-full h-full min-h-[560px]"

allowfullscreen>

</iframe>



@else



@if($post->thumbnail)

<img

src="{{ asset('storage/'.$post->thumbnail) }}"

class="w-full h-full min-h-[560px] object-cover" loading="lazy" decoding="async">


@endif




<div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>




<div class="absolute bottom-0 p-10 text-white">



<span

class="px-5 py-2 rounded-full text-xs font-black uppercase

{{ $post->type=='artikel'
?'bg-cyan-400 text-black'
:'bg-red-500 text-white' }}">

{{ ucfirst($post->type) }}

</span>




<h2 class="text-5xl font-extrabold leading-tight mt-5">

{{ $post->title }}

</h2>

<div class="flex flex-wrap gap-5 mt-6 text-sm">


<span>
<i class="fas fa-calendar mr-2"></i>

{{ optional($post->published_at)->format('d M Y') }}

</span>


@if($post->type !== 'youtube')

<span>
<i class="fas fa-user mr-2"></i>

{{ $post->author_name ?? '-' }}

</span>

@endif

</div>


</div>



@endif


</div>









<!-- INFORMATION -->

<div class="bg-white/80 backdrop-blur-xl

rounded-[35px]

shadow-xl

border border-white

p-8">



<p class="uppercase tracking-widest text-blue-600 font-black text-sm">

Informasi Konten

</p>



<h2 class="text-3xl font-extrabold text-slate-900 mt-3">

{{ $post->title }}

</h2>





<div class="space-y-6 mt-8">





<div class="flex gap-4">

<div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center">

<i class="fas fa-file-alt text-xl"></i>

</div>


<div>

<p class="font-bold">
Jenis Konten
</p>

<p class="text-gray-500">

{{ ucfirst($post->type) }}

</p>

</div>

</div>






<div class="flex gap-4">

<div class="w-14 h-14 rounded-2xl bg-green-100 text-green-600 flex items-center justify-center">

<i class="fas fa-check-circle text-xl"></i>

</div>


<div>

<p class="font-bold">
Status
</p>


@if($post->is_published)

<p class="text-green-600 font-bold">
Published
</p>

@else

<p class="text-yellow-600 font-bold">
Draft
</p>


@endif


</div>


</div>


@if($post->type !== 'youtube')


<div class="flex gap-4">

<div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-600 flex 
items-center justify-center">

<i class="fas fa-user text-xl"></i>

</div>


<div>

<p class="font-bold">
Penulis
</p>

<p class="text-gray-500">

{{ $post->author_name ?? '-' }}

</p>

</div>


</div>


@endif



<div class="flex gap-4">

<div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center">

<i class="fas fa-clock text-xl"></i>

</div>


<div>

<p class="font-bold">
Tanggal Publish
</p>


<p class="text-gray-500">

{{ optional($post->published_at)->format('d M Y H:i') }}

</p>


</div>


</div>






<div class="flex gap-4">

<div class="w-14 h-14 rounded-2xl bg-cyan-100 text-cyan-600 flex items-center justify-center">

<i class="fas fa-history text-xl"></i>

</div>


<div>

<p class="font-bold">
Update Terakhir
</p>


<p class="text-gray-500">

{{ $post->updated_at->format('d M Y H:i') }}

</p>


</div>


</div>



</div>






@if($post->excerpt)


<div class="mt-8 rounded-3xl

bg-gradient-to-r from-blue-600 to-cyan-500

p-6 text-white">


<p class="font-bold mb-2">

Ringkasan

</p>


<p class="leading-relaxed">

{{ $post->excerpt }}

</p>


</div>



@endif



</div>



</div>










<!-- ================= CONTENT ================= -->


<div class="mt-10 bg-white rounded-[35px]

shadow-xl

p-10">



<div class="flex items-center gap-4 mb-8">


<div class="w-14 h-14 rounded-2xl bg-cyan-100 text-cyan-600 flex items-center justify-center">

<i class="fas fa-align-left text-xl"></i>

</div>



<div>

<p class="uppercase text-blue-600 text-sm font-black">

Isi Konten

</p>


<h2 class="text-3xl font-extrabold">

{{ $post->type=='artikel'
?'Artikel Lengkap'
:'Deskripsi Video' }}

</h2>


</div>


</div>





@if($post->type=='artikel')


<div class="prose max-w-none text-gray-700">


{!! $post->content !!}


</div>



@else


<div class="text-gray-700 leading-relaxed whitespace-pre-line">


{{ $post->content ?? '-' }}


</div>


@endif

</div>


</div>

</div>

@endsection