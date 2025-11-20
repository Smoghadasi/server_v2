@extends('layouts.dashboard')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">کانال ({{ $channels->total() }})</h5>
    </div>
    <div class="card-body">

        {{-- Tabs --}}
        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link {{ request('tab') == 'all' || !request('tab') ? 'active' : '' }}"
                   href="{{ route('channel.index', array_merge(request()->except('page'), ['tab' => 'all'])) }}">
                   همه
                </a>
            </li>
            @foreach($botNumbers as $botNumber)
                <li class="nav-item">
                    <a class="nav-link {{ request('tab') == $botNumber ? 'active' : '' }}"
                       href="{{ route('channel.index', array_merge(request()->except('page'), ['tab' => $botNumber])) }}">
                       {{ $botNumber }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Search --}}
        <form method="get" action="{{ route('channel.index') }}">
            <input type="hidden" name="tab" value="{{ request('tab', 'all') }}">
            <div class="form-group row">
                <div class="col-md-3">
                    <input type="text" placeholder="نام یا شماره کانال..." class="form-control" name="word" id="word" value="{{ request('word') }}" />
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary">جستجو</button>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>بار</th>
                    <th>کانال</th>
                    <th>شماره</th>
                    <th>آخرین زمان ثبت</th>
                </tr>
                <?php $i = 0; ?>
                @foreach ($channels as $channel)
                    <tr>
                        <td>{{ ($channels->currentPage() - 1) * $channels->perPage() + ++$i }}</td>
                        <td>{{ $channel->name }}</td>
                        <td>{{ $channel->bot_number }}</td>
                        @php $pieces = explode(' ', $channel->updated_at); @endphp
                        <td>{{ gregorianDateToPersian($channel->updated_at, '-', true) . ' | ' . $pieces[1] }}</td>
                    </tr>
                @endforeach
            </table>
            <div class="mt-2">
                {{ $channels->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
