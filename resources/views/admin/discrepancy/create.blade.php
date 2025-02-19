@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">
                    صورت مغایرت
                </h5>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>واریزی های صورت گرفته</th>
                                <th>مقدار</th>
                                <th>واریزی های سامانه</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>کارت به کارت</td>
                                <td id="cardToCard">{{ number_format($cardToCardTotal) }} تومان</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>بانک سینا</td>
                                <td><input id="bankSina" class="form-control" placeholder="0" type="text"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>زرین پال</td>
                                <td><input id="bankZarinpal" class="form-control" placeholder="0" type="text"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>زیبال</td>
                                <td><input id="bankZibal" class="form-control" placeholder="0" type="text"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>مجموع</td>
                                <td id="total"></td>
                                <td id="totalPayment">{{ number_format($onlineTotal) }} تومان</td>
                            </tr>
                            <tr>
                                <td>اختلاف مبلغ: </td>
                                <td id="amountDifference"></td>
                                <td colspan="1" >
                                    <form action="{{ route('discrepancy.store') }}" method="post">
                                        @csrf
                                        <input id="total_card" type="hidden" name="total_card">
                                        <input id="total_site" value="{{ $onlineTotal }}" type="hidden" name="total_site">
                                        <input id="total_all" type="hidden" name="total_all">
                                        <button type="submit" class="btn btn-primary btn-block">ثبت</button>
                                    </form>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function(){
            function numberWithCommas(x) {
                return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            function calculateSum() {
                var cardToCard = parseFloat($('#cardToCard').text().replace(/,/g, '').replace(' تومان', '') || 0);
                var bankSina = parseFloat($('#bankSina').val().replace(/,/g, '') || 0);
                var bankZarinpal = parseFloat($('#bankZarinpal').val().replace(/,/g, '') || 0);
                var bankZibal = parseFloat($('#bankZibal').val().replace(/,/g, '') || 0);

                var total = cardToCard + bankSina + bankZarinpal + bankZibal;
                $('#total').text(total.toLocaleString() + ' تومان');

                var totalPayment = parseFloat($('#totalPayment').text().replace(/,/g, '').replace(' تومان', '') || 0);
                var amountDifference = totalPayment - total;
                $('#amountDifference').text(amountDifference.toLocaleString() + ' تومان');
                $('#total_card').val(total);
                $('#total_all').val(amountDifference);
            }

            $('input').on('input', function() {
                $(this).val(numberWithCommas($(this).val().replace(/,/g, '')));
                calculateSum();
            });

            calculateSum();  // محاسبه اولیه
        });
    </script>
@endsection
