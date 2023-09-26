@extends('layouts.dashboard')

@section('content')

<!-- Breadcrumbs-->
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        گروه بندی بارها
    </li>
    <li class="breadcrumb-item active"> گروه بندی بارها</li>
</ol>
@if(isset($message))
<div class="alert alert-primary">{{ $message }}</div>
@endif

<div class="alert alert-info text-right">
    @if(isNewLoadAutoAccept())
    تایید بار ها بصورت خودکار
    <a class="btn btn-danger" href="{{ url('admin/changeSiteOption/newLoadAutoAccept') }}">
        تغییر به غیر خودکار
    </a>
    @else
    تایید بار ها بصورت غیر خودکار
    <a class="btn btn-primary" href="{{ url('admin/changeSiteOption/newLoadAutoAccept') }}">
        تغییر به خودکار
    </a>
    @endif
</div>


<div class="container">
    <div class="text-right">
        <p>
            <a class="btn btn-primary" href="{{ url('admin/addNewLoadForm/admin') }}"> + افزودن بار توسط اپراتور</a>
            <a class="btn btn-primary" href="{{ url('admin/addNewLoadForm/bearing') }}"> + افزودن برای باربری</a>
            <a class="btn btn-primary" href="{{ url('admin/addNewLoadForm/customer') }}"> + افزودن بار برای صاحب
                بار</a>
            <a class="btn btn-primary" href="{{ route('accept.cargo.index') }}">+ تایید بار</a>
        </p>
    </div>
    <div class="col-md-12 text-center" id="List">
        <?php echo htmlspecialchars_decode(\App\Http\Controllers\LoadController::displayLoadsCategoriesFromLoadStatus());?>
    </div>
</div>
<script>
    var timer = setInterval(function() {
        $.ajax({
            url: "/admin/displayLoadsCategoriesFromLoadStatus/"
            , success: function(result) {
                document.getElementById("List").innerHTML = result;
            }
            , error: function() {}
        });
    }, 30000);

</script>
@stop
