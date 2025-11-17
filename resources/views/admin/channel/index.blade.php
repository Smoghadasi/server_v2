@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">کانال ({{ $channels->total() }})</h5>

        </div>
        <div class="card-body">
            <form method="get" action="{{ route('channel.index') }}">
                <div class="form-group">
                    <div class="col-md-12 row">
                        <div class="col-md-3">
                            <input type="text" placeholder="نام یا شماره کانال..." class="form-control col-md-4" name="word"
                                id="word" />
                        </div>
                    </div>
                    <button class="btn btn-primary m-2">جستجو</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>بار</th>
                        <th>کانال</th>
                        <th>شماره</th>
                        <th>اخرین زمان ثبت</th>
                    </tr>
                    <?php $i = 0; ?>

                    @foreach ($channels as $channel)
                        <tr>
                            <td>{{ ($channels->currentPage() - 1) * $channels->perPage() + ++$i }}</td>

                            <td>
                                {{ $channel->name }}
                            </td>
                            <td>{{ $channel->bot_number }}</td>

                            @php
                                $pieces = explode(' ', $channel->updated_at);
                            @endphp
                            <td>{{ gregorianDateToPersian($channel->updated_at, '-', true) . ' | ' . $pieces[1] }}</td>
                        </tr>
                    @endforeach
                </table>
                <div class="mt-2">
                    {{ $channels->appends($_GET)->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
