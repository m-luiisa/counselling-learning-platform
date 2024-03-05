@extends('layouts.app')

@section('content')
<div class="container">
    <postprocessing :id="{{ $counsellingId }}" course-end-date="{{ $end_date }}"></postprocessing>
</div>
@endsection