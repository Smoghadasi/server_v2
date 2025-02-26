@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            اعلان ها
        </h5>
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>متن پیام</th>
                        <th>تاریخ ثبت</th>
                    </tr>
                </thead>
                <tbody class="small text-right">
                    <?php $i = 1; ?>
                    @forelse ($notifications as $key => $notification)
                        <tr class="text-center">
                            <td>{{ ($notifications->currentPage() - 1) * $notifications->perPage() + ($key + 1) }}</td>
                            <td>
                                <a href="{{ $notification->link }}">
                                    {{ $notification->message ?? '-' }}

                                </a>
                            </td>

                            @php
                                $pieces = explode(' ', $notification->created_at);
                            @endphp
                            <td>
                                {{ gregorianDateToPersian($notification->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}
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
            <div class="mt-3">
                {{ $notifications }}
            </div>

        </div>
    </div>

@stop
