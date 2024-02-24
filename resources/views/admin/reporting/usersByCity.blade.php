@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش استفاده کنندگان به تفکیک شهرستان
        </h5>
        <div class="card-body">

            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>شهر - استان</th>
                            <th>تعداد</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($users as  $key => $user)
                            <tr>
                                <td>{{ ($users->currentPage() - 1) * $users->perPage() + ($key + 1) }}</td>
                                <td>{{ $user->cityOwner->state }} - {{ $user->cityOwner->name }}</td>
                                <td>{{ $user->count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2 mb-2">
                {{ $users }}
            </div>

        </div>
    </div>
@endsection
