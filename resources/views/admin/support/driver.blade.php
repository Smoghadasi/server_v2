@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            تماس های پشتیبانی
        </h5>
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>راننده</th>
                        <th>اپراتور</th>
                        <th>نتیجه</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody class="small text-right">
                    <?php $i = 1; ?>
                    @forelse ($supports as $key => $support)
                        <tr>
                            <td>{{ ($supports->currentPage() - 1) * $supports->perPage() + ($key + 1) }}</td>
                            <td>
                                @if ($support->driver)
                                    <a href="{{ route('driver.detail', $support->driver_id) }}">
                                        {{ $support->driver ? $support->driver->name . ' ' . $support->driver->lastName . ' ( ' . $support->driver->mobileNumber . ' )' : '-' }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                {{ $support->user ? $support->user->name . ' ' . $support->user->lastName : '-' }}
                            </td>

                            <td>
                                {{ $support->result ?? '-' }}
                            </td>
                            <td>
                                -
                            </td>
                        </tr>
                    @empty
                        <tr class="text-center">
                            <td colspan="10">
                                دیتا مورد نظر یافت نشد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if (Auth::user()->role_id == 'admin')
                <div class="mt-3">
                    {{ $supports }}
                </div>
            @endif

        </div>
    </div>

@stop
