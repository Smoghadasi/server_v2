@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    موارد قابل پیگیری
                </div>
                <div class="col text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCenter">
                        جدید
                    </button>
                    <!-- Modal -->
                    <div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalCenterTitle">موارد قابل پیگیری</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('trackableItems.store') }}">
                                    @csrf

                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-4 mb-3">
                                                <input type="text" id="mobileNumber" name="mobileNumber"
                                                    class="form-control" placeholder="شماره موبایل..." />
                                            </div>
                                            <div class="col-md-4 col-sm-12">
                                                <input class="form-control" type="text" id="new" name="date"
                                                    required placeholder="تاریخ" autocomplete="off" />
                                            </div>
                                            <div class="col-md-4 col-sm-12">
                                                <input value="{{ now() }}" class="form-control" type="time"
                                                    id="time" name="time" required placeholder="ساعت"
                                                    autocomplete="off" />
                                            </div>
                                            <div class="col-12">
                                                <textarea class="form-control" name="description" id="" cols="15" rows="5"
                                                    placeholder="متن توضیحات"></textarea>
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
            </div>
        </div>
        <div class="card-body">
            <form class="mb-3" method="get" action="{{ route('trackableItems.index') }}">
                <div class="form-group row">
                    <div class="col-md-2 mt-3">
                        <select class="form-select" name="status">
                            <option>انتخاب کنید...</option>
                            <option value="0">بایگانی</option>
                            <option value="1">فعال</option>
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <button type="submit" class="btn btn-primary mr-2">جستجو</button>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ردیف</th>
                            <th>شماره موبایل</th>
                            <th>اپراتور</th>
                            <th>کد رهگیری</th>
                            <th>توضیحات</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 1; ?>
                        @forelse ($tracks as $key => $track)
                            <tr>
                                <td>
                                    @if (isset($track->childrenRecursive))
                                        <a
                                            href="{{ route('trackableItems.index', ['parentId' => $track->id]) }}">{{ ($tracks->currentPage() - 1) * $tracks->perPage() + ($key + 1) }}</a>
                                    @else
                                        {{ ($tracks->currentPage() - 1) * $tracks->perPage() + ($key + 1) }}
                                    @endif
                                </td>
                                <td>{{ $track->mobileNumber }}({{$track->childrenRecursive->count()}})</td>
                                <td>{{ $track->user->name ?? '-' }} {{ $track->user->lastName ?? '' }}</td>
                                <td>{{ $track->tracking_code }}</td>
                                <td>{{ Str::limit($track->description, 30) }}</td>
                                <td>{{ $track->status ? 'فعال' : 'بایگانی شد' }}</td>
                                {{-- @php
                                $pieces = explode(' ', $track->created_at);
                            @endphp --}}
                                <td>
                                    {{ $track->date }}
                                </td>

                                <td>
                                    @if ($track->status == 0)
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#followUp">پیگیری مجدد</button>
                                    @endif

                                    <div class="modal fade" id="followUp" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="followUpTitle">پیگیری مجدد :
                                                        {{ $track->tracking_code }} </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>

                                                <form action="{{ route('trackableItems.store') }}" method="post">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-4 col-sm-12">
                                                                <input class="form-control" type="text" id="new_again"
                                                                    name="date" required placeholder="تاریخ"
                                                                    autocomplete="off" />
                                                            </div>
                                                            <div class="col-md-4 col-sm-12">
                                                                <input value="{{ now() }}" class="form-control"
                                                                    type="time" id="time_2" name="time" required
                                                                    placeholder="ساعت" autocomplete="off" />
                                                            </div>
                                                        </div>

                                                        <input type="hidden" name="parent_id"
                                                            value="{{ $track->id }}">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            data-bs-dismiss="modal">
                                                            بستن
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">ثبت</button>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                    @if ($track->status == 1)
                                        <button data-bs-toggle="modal" data-bs-target="#submitClose"
                                            class="btn btn-sm btn-outline-danger">بستن</button>
                                        <div class="modal fade" id="submitClose" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="submitCloseTitle">بستن تیکت شماره
                                                            :
                                                            {{ $track->tracking_code }} </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('trackableItems.destroy', $track) }}">
                                                        @csrf
                                                        @method('delete')

                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <textarea class="form-control" name="result" id="" cols="15" rows="5"
                                                                        placeholder="متن توضیحات"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                data-bs-dismiss="modal">
                                                                بستن
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">ثبت</button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>
                                        @else
                                        <button data-bs-toggle="modal" data-bs-target="#watchResult"
                                            class="btn btn-sm btn-outline-success">مشاهده نتیجه</button>
                                        <div class="modal fade" id="watchResult" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="watchResultTitle">نتیجه تیکت شماره
                                                            :
                                                            {{ $track->tracking_code }} </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>


                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <textarea class="form-control" disabled name="description" id="" cols="15" rows="5"
                                                                    placeholder="متن نتیجه">{{ $track->result }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif


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
            </div>

            <div class="mt-3">
                {{ $tracks }}
            </div>

        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var now = new Date();
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            var currentTime = hours + ':' + minutes;

            document.getElementById('time').value = currentTime;
            document.getElementById('time_2').value = currentTime;
        });
        $("#new").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            selectedBefore: !0
        });
        $("#new_again").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            selectedBefore: !0
        });
    </script>
@endsection
