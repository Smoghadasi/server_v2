@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    تماس رایگان رانندگان
                </div>
            </div>
        </h5>
        <div class="card-body">
            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>راننده</th>
                            <th>تاریخ</th>
                            <th>تعداد</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($freeCallDrivers as $key => $freeCallDriver)
                            <tr>
                                <td>
                                    {{ ($freeCallDrivers->currentPage() - 1) * $freeCallDrivers->perPage() + ($key + 1) }}
                                </td>

                                <td>
                                    <a href="{{ route('driver.detail', $freeCallDriver->driver) }}">
                                        {{ $freeCallDriver->driver->name . ' ' . $freeCallDriver->driver->lastName }}
                                    </a>
                                </td>
                                <td>
                                    {{ $freeCallDriver->persian_date ?? '-' }}
                                    </td>
                                <td>{{ $freeCallDriver->count }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2">
                {{ $freeCallDrivers }}
            </div>
        </div>
    </div>
@endsection
