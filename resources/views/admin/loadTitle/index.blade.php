@extends('layouts.dashboard')
@section('content')
    <div class="card">
        <h5 class="card-header">
            عنوان ها
        </h5>
        <div class="card-body">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                data-bs-target="#addWordToDictionary">افزودن کلمه جدید
            </button>

            <div id="addWordToDictionary" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <form action="{{ route('loadTitles.store') }}" method="post" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">افزودن کلمه جدید</h4>
                        </div>
                        <div class="modal-body text-right">
                            <div class="form-group">
                                <label>کلمه جدید : </label>
                                <input type="text" class="form-control" name="title">
                            </div>
                        </div>
                        <div class="modal-footer text-left">
                            <button class="btn btn-primary">ثبت</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"> انصراف</button>
                        </div>
                    </form>

                </div>
            </div>

            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>لغت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($loadTitles as $loadTitle)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $loadTitle->title }}</td>

                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteWordToDictionary_{{ $loadTitle->id }}">حذف
                                    </button>

                                    <div id="deleteWordToDictionary_{{ $loadTitle->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">حذف</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>آیا مایل به حذف
                                                        <span class="text-primary"> {{ $loadTitle->title }}</span>
                                                        هستید؟
                                                    </p>
                                                </div>
                                                <div class="modal-footer text-left">
                                                    <form action="{{ route('loadTitles.destroy', $loadTitle) }}" method="POST">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="btn btn-primary">حذف</button>
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
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2">
                {{ $loadTitles }}
            </div>
        </div>
    </div>
@endsection
