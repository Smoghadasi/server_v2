@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">بار های تکراری</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>#</th>
                    <th>بار</th>
                    <th>دلیل</th>
                    <th>تاریخ</th>
                </tr>
                <?php $i = 0; ?>

                @foreach ($cargoList as $cargo)
                    <tr>
                        <td>{{ ($cargoList->currentPage() - 1) * $cargoList->perPage() + ++$i }}</td>

                        <td>{{ $cargo->cargo }}</td>
                        <td>
                            @if ($cargo->isBlocked == 1)
                                بلاک
                            @endif
                            @if ($cargo->isDuplicate == 1)
                                تکراری
                            @endif
                        </td>

                        @php
                            $pieces = explode(' ', $cargo->created_at);
                        @endphp
                        <td>{{ gregorianDateToPersian($cargo->created_at, '-', true) . ' ' . $pieces[1] }}</td>
                    </tr>
                @endforeach
            </table>
            <div class="mt-2">
                {{ $cargoList }}
            </div>
        </div>
        </div>

    </div>


@stop
