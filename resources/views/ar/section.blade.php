@extends('ar.layout')
@section('title',$title.' | فيردليف')
@section('alternate',route('section',$section))
@section('content')<section class="wrap"><h1>{{$title}}.</h1>@forelse($entries as $e)<article class="chapter"><h2>{{$e->title_ar}}</h2><p class="prose">{{$e->body_ar}}</p>@if($e->source_url)<a href="{{$e->source_url}}">المصدر أو المستند</a>@endif</article>@empty<p>سيتم نشر المعلومات العربية بعد مراجعتها واعتمادها.</p>@endforelse
@if($section==='contact')<a class="primary" href="{{route('enquiry','contact')}}">نموذج التواصل (English)</a>@endif</section>@endsection