@extends('layouts.dashboard')

@section('content')
    <div class="card align-items-center">
        <div class="col-lg-6" style="height: 100vh;overflow-y: auto;">
            <form method="POST" action="{{ route('warehouse.destroy', $warehouse) }}">
                @csrf
                @method('delete')

                <div class="form-group row text-right alert alert-light border border-dark" style="color: #000000">
                    <input type="hidden" name="key[]" value="">

                    <label class="col-lg-12 mb-2">عنوان :
                        <input type="text" class="form-control" name="title" placeholder="بدون عنوان">
                    </label>
                    <label class="col-lg-6 mb-2">مبدا :
                        <input type="text" class="form-control">
                    </label>


                    <label class="col-lg-6 mb-2">مقصد :
                        <input type="text" class="form-control">
                    </label>

                    <label class="col-lg-12 mb-2">شماره تلفن :
                        <input type="text" class="form-control">
                    </label>

                    <div class="col-lg-12 row mb-2">

                        <label class="col-lg-6">قیمت :
                            <input type="text" class="form-control">
                        </label>
                        <label class="col-lg-6">نوع قیمت :
                            <div class="col-lg-12">
                                <label class="ml-3">
                                    <input checked type="radio" value="توافقی" name="priceType" />توافقی
                                </label>

                                <label class="ml-3">
                                    <input type="radio" value="به ازای هر تن" name="priceType" />به
                                    ازای
                                    هر تن
                                </label>
                                <label class="ml-3">
                                    <input type="radio" value="به صورت صافی" name="priceType" />به
                                    صورت
                                    صافی
                                </label>
                            </div>

                        </label>
                    </div>

                    <label class="col-lg-12 row mb-2">
                        <lable class="col-lg-12">ناوگان :</lable>
                        <input type="text" class="form-control col-lg-4" name="fleets" value="">
                    </label>
                    <label class="col-lg-12 row">توضیحات :
                        <textarea class="form-control" name="description" rows="4"></textarea>
                    </label>
                    <label class="col-lg-12 row">دیتا :
                        <textarea class="form-control" name="description" rows="10">{{ $warehouse->data }}</textarea>
                    </label>
                </div>

                <div class="row form-group row mb-0">
                    <div class="col-md-12 offset-md-4">
                        <button type="submit" class="btn btn-danger">
                            بار بعدی
                        </button>

                    </div>
                </div>
            </form>
        </div>

    </div>


    <script>
        function separate(freight) {
            document.getElementById(freight).value = document.getElementById(freight).value.replace(/\D/g, "")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function priceType(pt, value) {
            value = value.replace(",", "");
            if (value !== "0" && value.length > 0)
                document.getElementById(pt).value = "به صورت صافی";
            else
                document.getElementById(pt).value = "توافقی";
        }
    </script>

@stop
