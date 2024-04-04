@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-4">
                    مرخصی روزانه
                </div>
                <div class="col-4" style="text-align: left">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCenter">
                        جدید
                    </button>
                    <!-- Modal -->
                    <div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <form action="{{ route('vacations.store') }}" method="post" class="modal-content">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalCenterTitle">مرخصی جدید</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="user_id" class="form-label">کاربر</label>
                                            <select class="form-select" name="user_id" id="">
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}
                                                        {{ $user->lastName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col mb-0">
                                            <label for="fromDate" class="form-label">از تاریخ</label>
                                            <input type="text" name="fromDate" id="fromDate" class="form-control"
                                                placeholder="1400/01/01" />
                                        </div>
                                        <div class="col mb-0">
                                            <label for="toDate" class="form-label">تا تاریخ</label>
                                            <input type="text" name="toDate" id="toDate" class="form-control"
                                                placeholder="1400/01/01" />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="description" class="form-label">توضیحات</label>
                                            <textarea class="form-control" name="description" id="" cols="30" rows="10"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        بستن
                                    </button>
                                    <button type="submit" class="btn btn-primary">ذخیره</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </h5>
        <div class="card-body">
            <div class="table-responsive mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>از تاریخ</th>
                            <th>تا تاریخ</th>
                            {{-- <th>تعداد روز</th> --}}
                            <th>علت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 0; ?>
                        @forelse($vacations as $vacation)
                            <tr>
                                <td>{{ ($vacations->currentPage() - 1) * $vacations->perPage() + ++$i }}</td>
                                <td>{{ $vacation->user->name }} {{ $vacation->user->lastName }}</td>

                                <td>{{ $vacation->fromDate }}</td>
                                <td>{{ $vacation->toDate }}</td>
                                <td>
                                    {{ $vacation->description ?? '-' }}
                                </td>

                                <td>
                                    @if (auth()->user()->role == ROLE_ADMIN)

                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editVacation_{{ $vacation->id }}">ویرایش
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#removeVacation_{{ $vacation->id }}">حذف
                                        </button>
                                    @endif
                                    <div id="editVacation_{{ $vacation->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <form action="{{ route('vacations.update', $vacation) }}" method="POST"
                                                class="modal-content">
                                                @csrf
                                                @method('Put')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalCenterTitle">ویرایش مرخصی</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col mb-3">
                                                            <label for="user_id" class="form-label">کاربر</label>
                                                            <select class="form-select" name="user_id" id="">
                                                                @foreach ($users as $user)
                                                                    <option @if($vacation->user_id == $user->id) selected @endif value="{{ $user->id }}">
                                                                        {{ $user->name }}
                                                                        {{ $user->lastName }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col mb-0">
                                                            <label for="fromDate" class="form-label">از تاریخ</label>
                                                            <input type="text" value="{{ $vacation->fromDate }}" name="fromDate" id="fromDate"
                                                                class="form-control" placeholder="1400/01/01" />
                                                        </div>
                                                        <div class="col mb-0">
                                                            <label for="toDate" class="form-label">تا تاریخ</label>
                                                            <input type="text" value="{{ $vacation->toDate }}" name="toDate" id="toDate"
                                                                class="form-control" placeholder="1400/01/01" />
                                                        </div>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col mb-3">
                                                            <label for="description" class="form-label">توضیحات</label>
                                                            <textarea class="form-control" name="description" cols="30" rows="10">
                                                                {{ $vacation->description }}
                                                            </textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">
                                                        بستن
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">ذخیره</button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                    <div id="removeVacation_{{ $vacation->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">حذف مرخصی</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>آیا مایل به حذف
                                                        هستید؟
                                                    </p>
                                                </div>
                                                <div class="modal-footer text-left">
                                                    <form action="{{ route('vacations.destroy', $vacation->id) }}" method="post">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button class="btn btn-primary" type="submit">حذف</button>
                                                    </form>
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                        انصراف
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="10">فیلد مورد خالی است</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-2">
                    {{ $vacations }}
                </div>
            </div>
        </div>
    </div>
@endsection
