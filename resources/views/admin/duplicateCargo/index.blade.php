@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">بار های تکراری</h5>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('duplicateCargoFromCargoList') }}">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-3 col-sm-12">
                            <input type="text" placeholder="بار مورد نظر..." class="form-control"
                                name="cargo" id="cargo" />
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <select name="type" class="form-control form-select" id="">
                                <option value="">همه</option>
                                <option value="block">بلاک</option>
                                <option value="duplicate">تکراری</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <button type="submit" class="btn btn-primary">جستجو</button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>#</th>
                    <th>بار</th>
                    <th>دلیل</th>
                    <th>تعداد</th>
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
                        <td>
                            {{ $cargo->total }}
                        </td>

                        @php
                            $pieces = explode(' ', $cargo->created_at);
                        @endphp
                        <td>{{ gregorianDateToPersian($cargo->created_at, '-', true) . ' ' . $pieces[1] }}</td>
                    </tr>
                @endforeach
            </table>
            <div class="mt-2">
                {{ $cargoList->appends($_GET)->links() }}
            </div>
        </div>
        </div>

    </div>


@stop
