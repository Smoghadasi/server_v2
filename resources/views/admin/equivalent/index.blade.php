@extends('layouts.dashboard')

@section('css')
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
@endsection
@section('content')
    <div class="card">
        <h5 class="card-header">
            کلمات معادل در ثبت بار
        </h5>
        <div class="card-body">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                data-bs-target="#addWordToDictionary">افزودن کلمه جدید
            </button>

            <div id="addWordToDictionary" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <form action="{{ url('admin/addWordToEquivalent') }}" method="post" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">افزودن کلمه جدید</h4>
                        </div>
                        <div class="modal-body text-right">
                            <div class="form-group">
                                <label>کلمه جدید : </label>
                                <input type="text" class="form-control" name="equivalentWord">
                            </div>
                            <div class="form-group">
                                <label class="alert alert-info p-1" onclick="hideItem('fleet')">
                                    <input type="radio" name="type" value="fleet" checked>
                                    ناوگان
                                </label>
                                <label class="alert alert-info p-1" onclick="hideItem('city')">
                                    <input type="radio" name="type" value="city">
                                    شهر
                                </label>
                            </div>

                            <div class="form-group" id="cityList" style="display: none">
                                <label>شهر : </label>
                                <select id="city-select" name="city_id" dir="rtl">
                                    <option value="0">شهر</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}">
                                            <?php echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)); ?>
                                        </option>
                                    @endforeach
                                </select>

                            </div>

                            <div class="form-group" id="fleetList">
                                <label>ناوگان : </label>
                                <select id="fleet-select" name="fleet_id" class="form-control" style="width: 100%"
                                    dir="rtl">
                                    <option value="0">ناوگان</option>
                                    @foreach ($fleets as $fleet)
                                        <option value="{{ $fleet->id }}">
                                            <?php echo str_replace('ك', 'ک', str_replace('ي', 'ی', $fleet->title)); ?>
                                        </option>
                                    @endforeach
                                </select>
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
                            <th>کلمه اصلی</th>
                            <th>کلمه معادل</th>
                            <th>دسته کلمه</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($dictionary as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->originalWord }}</td>
                                <td>{{ $item->equivalentWord }}</td>
                                <td>
                                    @if ($item->type == 'city')
                                        شهر
                                    @elseif($item->type == 'fleet')
                                        ناوگان
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteWordToDictionary_{{ $item->id }}">حذف
                                    </button>

                                    <div id="deleteWordToDictionary_{{ $item->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">حذف</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>آیا مایل به حذف
                                                        <span class="text-primary"> {{ $item->equivalentWord }}</span>
                                                        هستید؟
                                                    </p>
                                                </div>
                                                <div class="modal-footer text-left">
                                                    <form action="{{ route('removeDictionaryWord', ['equivalent' => $item]) }}"
                                                        method="POST">
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
                {{ $dictionary }}
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const element = document.getElementById('city-select');
            const choices = new Choices(element, {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
                placeholderValue: 'انتخاب شهر',
            });

            const fleetSelect = document.getElementById('fleet-select');
            new Choices(fleetSelect, {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
                placeholderValue: 'انتخاب ناوگان',
            });
        });
    </script>


    <script>
        function hideItem(item) {
            switch (item) {
                case 'fleet':
                    $('#cityList').fadeOut(function() {
                        $('#fleetList').fadeIn();
                    });

                    break;
                case 'city':
                    $('#fleetList').fadeOut(function() {
                        $('#cityList').fadeIn();
                    });

                    break
            }
        }
    </script>
@endsection
