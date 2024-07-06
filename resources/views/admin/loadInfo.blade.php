@extends('layouts.dashboard')

@section('content')

    <div class="container">
        <div class="my-2">
            <a class="btn btn-primary" href="{{ url('admin/editLoadInfoForm') }}/{{ $load->id }}"> ویرایش اطلاعات بار</a>
            @if ($load->userType == 'owner')
                <a class="btn btn-success" href="{{ route('owner.show', $load->user_id) }}"> اطلاعات صاحب بار</a>
            @endif
            <a class="btn btn-danger" href="{{ url('admin/removeLoadInfo') }}/{{ $load->id }}"> حذف اطلاعات بار</a>
        </div>

        <div class="text-right">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-2">
                        <h5 class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    اطلاعات بار
                                </div>
                                <div class="col-6 text-end">
                                    {{ $load->dateTime }} | {{ $load->loadingDate }}
                                </div>
                            </div>
                        </h5>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td class="font-weight-bold">عنوان بار</td>
                                            <td>{{ $load->title }}</td>
                                            <td class="font-weight-bold">کرایه</td>
                                            <td>
                                                @if ($load->priceBased == 'توافقی')
                                                    توافقی
                                                @else
                                                    {{ number_format($load->suggestedPrice) }} تومان
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">مبدا</td>
                                            <td>
                                                <a
                                                    href="https://maps.google.com/maps?f=q&q={{ $load->originLatitude }},{{ $load->originLongitude }}">
                                                    {{ $path['stateFrom'] }} - {{ $path['from'] }}
                                                </a>
                                            </td>

                                            <td class="font-weight-bold">مقصد</td>
                                            <td>
                                                <a
                                                    href="https://maps.google.com/maps?f=q&q={{ $load->destinationLatitude }},{{ $load->destinationLongitude }}">
                                                    {{ $path['stateTo'] }} - {{ $path['to'] }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">ثبت توسط</td>
                                            <td>{{ $load->userType == 'owner' ? 'صاحب بار' : 'اپراتور' }}</td>
                                            <td class="font-weight-bold">تعداد</td>
                                            <td>
                                                <span class="badge bg-primary">بازدید :
                                                    {{ $load->driverVisitCount }}</span>
                                                <span>
                                                    <a class="badge bg-danger"
                                                        href="{{ route('load.searchLoadInquiry', $load->id) }}">
                                                        درخواست: {{ $load->numOfInquiryDrivers }}
                                                    </a>

                                                </span>
                                                <span>
                                                    <a class="badge bg-success"
                                                        href="{{ route('load.searchLoadDriverCall', $load->id) }}">
                                                        تماس: {{ $load->numOfDriverCalls }}
                                                    </a>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">تلفن جهت هماهنگی</td>
                                            <td>{{ $load->mobileNumberForCoordination }}</td>

                                            <td class="font-weight-bold">نوع</td>
                                            <td>{{ $load->bulk == '0' ? 'غیر فله' : 'فله' }}</td>

                                        </tr>
                                        <tr>

                                            <td class="font-weight-bold">محموله خطرناک</td>
                                            <td>{{ $load->dangerousProducts == '0' ? 'خیر' : 'بله' }}</td>
                                            <td class="font-weight-bold">حق بیمه</td>
                                            <td>
                                                {{ $load->insuranceAmount }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">مسافت</td>
                                            <td>{{ $load->distanceCity }} کیلومتر</td>

                                            <td class="font-weight-bold">نوع بار (درون شهری یا برون شهری)</td>
                                            <td>
                                                {{ $load->loadMode }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">فوری</td>
                                            <td>
                                                @switch($load->urgent)
                                                    @case(0)
                                                        بله
                                                    @break

                                                    @case(1)
                                                        خیر
                                                    @break
                                                @endswitch
                                            </td>

                                            <td class="font-weight-bold">تصویر بار</td>
                                            <td>
                                                {{ $load->loadPic == null ? 'ندارد' : 'دارد' }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="font-weight-bold">توضیحات</td>
                                            <td>{{ $load->description }}</td>

                                            <td class="font-weight-bold">ارسال برای</td>
                                            <td>
                                                {{ $load->storeFor == 'driver' ? 'رانندگان' : 'باربری' }}
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-2">
                <h5 class="card-header">لیست ناوگان</h5>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>نوع ناوگان</th>
                                <th>تعداد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fleetLoads as $item)
                                <tr>
                                    <td>{{ \App\Http\Controllers\FleetController::getFleetName($item->fleet_id) }}</td>
                                    <td>{{ $item->numOfFleets }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        var modal = document.getElementById('myModal');
        var img = document.getElementById('loadPic');
        var modalImg = document.getElementById("img01");
        var captionText = document.getElementById("caption");
        img.onclick = function() {
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
        }
        var span = document.getElementsByClassName("close")[0];
        span.onclick = function() {
            modal.style.display = "none";
        }
        modal.onclick = function() {
            modal.style.display = "none";
        }
    </script>
@stop
