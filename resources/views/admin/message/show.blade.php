@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#storeResult">
                ثبت نتیجه
            </button>
            <div id="storeResult" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <form method="post" action="{{ url('admin/changeMessageStatus') }}/{{ $contactUses->id }}"
                        class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">ثبت نتیجه</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label> نتیجه تماس با کاربر را ثبت کنید: </label>
                                <textarea class="form-control" placeholder="نتیجه ..." name="result"></textarea>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" name="notification" type="checkbox" id="gridCheck">
                                <label class="form-check-label" for="gridCheck">
                                    ارسال اعلان
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer text-left">
                            <button type="submit" class="btn btn-primary">
                                ثبت نتیجه
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                انصراف
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </h5>
        <div class="card-body">
            <div class="col-lg-8 text-dark border-primary border rounded m-2 p-2" style="background: #f1f1f1">
                <div class="small">
                    <span>
                        کاربر :
                        {{ $contactUses->name }} {{ $contactUses->lastName }}
                    </span>
                    <p class="mr-5">
                        تاریخ :
                        {{ $contactUses->messageDateAndTime }}
                    </p>
                </div>
                <hr>
                <div class="mt-3">
                    <p>موضوع : {{ $contactUses->title }}</p>
                    <p>پیام: {{ $contactUses->message }}</p>
                </div>
            </div>

            @foreach ($contactUses->childrenRecursive as $recursive)
                <div class="mt-3" @if ($recursive->role == 'operator') style="justify-items: left" @endif>
                    {{-- <div class="h4 mb-2">نتایج :</div> --}}
                    <div class="card text-dark bg-light col-lg-8 ">
                        <div class="card-body m-2 p-2">
                            <div class="small">
                                <span>
                                    کاربر :
                                    {{ $recursive->role == 'operator' ? 'اپراتور' : $recursive->name . ' ' . $recursive->lastName }}
                                </span>
                                <p class="mr-5">
                                    تاریخ :
                                    {{ $recursive->messageDateAndTime }}
                                </p>
                            </div>
                            <hr>
                            <div class="mt-3">
                                {{ $recursive->result }}
                            </div>
                        </div>
                    </div>


                </div>
            @endforeach

        </div>
    </div>


@stop
