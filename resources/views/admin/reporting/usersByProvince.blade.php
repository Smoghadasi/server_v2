@extends('layouts.dashboard')
@section('title', '| استفاده کننده بر اساس استان')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش استفاده کنندگان به تفکیک استان
        </h5>
        <div class="card-body">
            <form action="{{ route('reporting.searchUsersByProvince') }}" method="post">
                @csrf
                <div class="col-lg-12 border rounded mt-2 mb-2 p-2">
                    <h6>جستجوی رانندگان : </h6>
                    <div class="container">
                        <div class="row row-cols-4">
                            <div class="col">
                                <div class="form-group">
                                    <label>نوع شهر :</label>
                                    <select class="form-select" name="province_id">
                                        <option value="0">همه</option>
                                        @foreach ($provinceCities as $provinceCity)
                                            <option value="{{ $provinceCity->id }}">{{ $provinceCity->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group my-4">
                            <button class="btn btn-info" type="submit">جستجو</button>
                        </div>
                    </div>


                </div>
            </form>
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
                                <td>
                                    <a href="{{ route('reporting.usersByCustomProvinces', $user->province_id) }}">
                                        {{ $user->provinceOwner->name }}
                                    </a>
                                </td>
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
