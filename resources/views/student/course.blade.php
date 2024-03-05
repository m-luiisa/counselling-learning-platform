@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-9">
            @if(count($course->counsellingSetups) > 1)
            <div class="card">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'tasks']"></fa-icon>
                    Aufgaben
                </div>
                <div class="card-body p-0">
                    <task-table :small-view="true" :setups="{{$course->counsellingSetups->where('mandatory', true) }}" course-end-date="{{ $course->end_date }}"></task-table>
                </div>
            </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'cog']"></fa-icon>
                    Übungen
                </div>
                <div class="card-body p-0">
                    <exercise-table :small-view="true" :setup-id="{{$course->counsellingSetups->where('mandatory', false)->first()->id}}" course-end-date="{{ $course->end_date }}"></exercise-table>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'chart-pie']"></fa-icon>
                    Statistik
                </div>
                <div class="card-body p-0">
                    <statistics :course-id="{{ $course->id }}"></statistics>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'info-circle']"></fa-icon>
                    Kursinformationen
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <fa-icon :icon="['fas', 'calendar']" class="text-grey-dark h2 mb-0"></fa-icon>
                        </div>
                        <div class="col">{{ \Carbon\Carbon::parse($course->start_date)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($course->end_date)->format('d.m.Y') }}</div>
                    </div>
                    <div class="row align-items-center mt-2">
                        <div class="col-auto text-grey-dark">
                            <b>Pseudonym: </b>
                        </div>
                        <div class="col">{{$course->pseudonym['pseudo_first_name'] . ' ' . $course->pseudonym['pseudo_last_name']}}</div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'user-friends']"></fa-icon>
                    Peer-Review Anfragen
                </div>
                <div class="card-body text-center p-5">
                    <h5 class="text-grey-dark">Coming soon</h5>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <fa-icon :icon="['fas', 'question']"></fa-icon>
                    Hilfecenter
                </div>
                <div class="card-body text-center">
                    <p>Unterstützung notwendig oder technische Probleme?</p>
                    <div class="fw-bold text-grey-dark">Zum Hilfecenter</div>
                    {{-- <a class="link" href="{{ route('helpdesk.index') }}">Zum Hilfecenter</a> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
