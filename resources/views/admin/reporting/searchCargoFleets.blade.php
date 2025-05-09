@extends('layouts.dashboard')
@section('title', '| استفاده کننده بر اساس استان')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش استفاده کنندگان به تفکیک ناوگان (استان)
        </h5>
        <div class="card-body">
            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>استان</th>
                            <th>تعداد</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($loads as $key => $load)
                            <tr>
                                <td>{{ ($loads->currentPage() - 1) * $loads->perPage() + ($key + 1) }}</td>
                                <td>
                                    <a href="{{ route('search.fleets.city', [
                                            'fleet' => $fleet_id,
                                            'origin_state' => $load->origin_state_id
                                        ]) }}">
                                        {{ $load->originState->name }}
                                    </a>
                                </td>
                                <td>{{ $load->count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2 mb-2">
                {{ $loads }}
            </div>

        </div>
    </div>
@endsection
