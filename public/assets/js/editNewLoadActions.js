$(document).ready(function () {

    $("#step2").fadeOut();
    $("#step3").fadeOut();
    $("#step4").fadeOut();
    $("#step5").fadeOut();
    $("#remove-pic-button").fadeOut();

    // $("#weight").keydown(function (e) {
    //     if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
    //         (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
    //         (e.keyCode === 67 && (e.ctrlKey === true || e.metaKey === true)) ||
    //         (e.keyCode === 88 && (e.ctrlKey === true || e.metaKey === true)) ||
    //         (e.keyCode >= 35 && e.keyCode <= 39)) {
    //         return;
    //     }
    //     if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
    //         e.preventDefault();
    //     }
    // });
    $("#width").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#height").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    $("#length").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode === 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });


    $("#insuranceAmount").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#suggestedPrice").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#numOfTrucks").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    // انتخاب تصویر برای بار
    $("#selected-pic").click(function () {
        $("#pic").click();
    });
    $("#selected-pic-button").click(function () {
        $("#pic").click();
    });

    $("#remove-pic-button").click(function () {
        $("#pic").val('');
        $('#selected-pic').attr('src', "../assets/img/add_pic.svg");
        $("#remove-pic-button").fadeOut();
    });

    // انتخاب زمان بارگیری
    $("#dischargeTimeDay").click(function () {

        $("#dischargeTimeDay").removeClass();
        $("#dischargeTimeNight").removeClass();

        $("#dischargeTimeDay").addClass("dischargeTimeItem dischargeTimeDay dischargeTimeSelected");
        $("#dischargeTimeNight").addClass("dischargeTimeItem dischargeTimeNight dischargeTimeNoSelected");

        $("#dischargeTime").value("day");
    });
    $("#dischargeTimeNight").click(function () {

        $("#dischargeTimeDay").removeClass();
        $("#dischargeTimeNight").removeClass();

        $("#dischargeTimeDay").addClass("dischargeTimeItem dischargeTimeDay dischargeTimeNoSelected");
        $("#dischargeTimeNight").addClass("dischargeTimeItem dischargeTimeNight dischargeTimeSelected");

        $("#dischargeTime").value("night");
    });

    $('input.number').keyup(function (event) {

        // skip for arrow keys
        if (event.which >= 37 && event.which <= 40) return;

        // format number
        $(this).val(function (index, value) {
            return value
                .replace(/\D/g, "")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });


    $("#packingType").click(function () {
        $("#packingTypeModal").fadeIn();
    });

    $("#packingTypeModal").click(function () {
        $("#packingTypeModal").fadeOut();
    });


    $("#fleetType").click(function () {
        $("#fleetTypeModal").fadeIn();
    });

    $("#fleetTypeModal").click(function () {
        $("#fleetTypeModal").fadeOut();
    });

});


function fadeInSteps(showStep) {

    switch (showStep) {
        case 1:
            $("#step1").fadeIn(1000);
            break;
        case 2:
            $("#step2").fadeIn(1000);
            break;
        case 3:
            $("#step3").fadeIn(1000);
            break;
        case 4:
            $("#step4").fadeIn(1000);
            break;
        case 5:
            $("#step5").fadeIn(1000);
            break;

    }
}

function selectPackingType(id, title, pic) {
    $("#packingTypesPic").attr("src", pic);
    $("#packingTypeTitle").text(title)
    $("#packing_type_id").val(id);
}

var fleetTypesPic = "";
var fleetTypeTitle = "";

function selectFleetType(id, title, pic) {
    $("#fleetTypesPic").attr("src", pic);
    $("#fleetTypeTitle").text(title)
    $("#fleet_id").val(id);

    fleetTypesPic = pic;
    fleetTypeTitle = title;
}

function showFleetGroup(parent_id) {
    $(".parent_id_0").fadeOut();
    $(parent_id).fadeIn();
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#selected-pic').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
        $("#remove-pic-button").fadeIn();
    }
}

$("#pic").change(function () {
    readURL(this);
});


let fleetListArray = $("#fleetListArray").val();
let fleetList = JSON.parse(fleetListArray);

$("#addToSelectedFleetsList").click(function () {
    var numOfFleets = parseInt($("#numOfFleets").val().replace(",", ""));
    // var suggestedPrice = parseInt($("#suggestedPrice").val().replace(",", ""));
    var fleet_id = parseInt($("#fleet_id").val());

    console.log(fleetList)

    if (!isNaN(numOfFleets) && !isNaN(fleet_id) && numOfFleets > 0 && fleet_id > 0) {
        fleetList.push({
            numOfFleets: numOfFleets,
            fleet_id: fleet_id,
            pic: fleetTypesPic,
            title: fleetTypeTitle
        });

        console.log(fleetList)

        displaySelectedFleetList();
    }


});

function removeSelectedFleet(fleet_id) {
    for (var i = 0; i < fleetList.length; i++)
        if (fleetList[i].fleet_id == fleet_id)
            fleetList.splice(i, 1);

    displaySelectedFleetList();
}


function displaySelectedFleetList() {
    $("#selectedFleetsList").html("");
    $("#fleetListArray").val("");

    var data = "";

    for (var i = 0; i < fleetList.length; i++) {
        // $("#selectedFleetsList").append("<tr><td>" + (i + 1) + "</td><td>" + fleetList[i].title + "</td><td>" + fleetList[i].suggestedPrice + "</td><td>" + fleetList[i].numOfFleets + "</td><td><button class='btn btn-sm btn-danger' type='button' onclick='removeSelectedFleet(" + fleetList[i].fleet_id + ")'>حذف</button></td></tr>");
        $("#selectedFleetsList").append("<tr><td>" + (i + 1) + "</td><td>" + fleetList[i].title + "</td><td>" + fleetList[i].numOfFleets + "</td><td><button class='btn btn-sm btn-danger' type='button' onclick='removeSelectedFleet(" + fleetList[i].fleet_id + ")'>حذف</button></td></tr>");
        var comma = ',';
        if (data.length == 0)
            comma = '';

        data += comma + '{"numOfFleets":' + fleetList[i].numOfFleets + ',"fleet_id":' + fleetList[i].fleet_id + '}';

    }
    $("#fleetListArray").val("[" + data + "]");

    console.log($("#fleetListArray").val())
}


// فرم ثبت بار
$("document").ready(function () {
    $("#cargoTitle").click(function () {
        $("#cargoForm").slideToggle();
        $("#vehicleTypeForm").slideUp();
        $("#moreInfoForm").slideUp();
    });

    $("#vehicleTypeTitle").click(function () {
        $("#vehicleTypeForm").slideToggle();
        $("#cargoForm").slideUp();
        $("#moreInfoForm").slideUp();
    });

    $("#moreInfoTitle").click(function () {
        $("#moreInfoForm").slideToggle();
        $("#cargoForm").slideUp();
        $("#vehicleTypeForm").slideUp();
    });


    $("#agreedPrice").click(function () {
        $("#suggestedPrice").val("");
        $("#suggestedPriceForm").slideUp();
    });

    $("#proposedPrice").click(function () {
        $("#suggestedPriceForm").slideDown();
    });


    // $("#weight").keydown(function (e) {
    //     if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
    //         (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
    //         (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
    //         (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
    //         (e.keyCode >= 35 && e.keyCode <= 39)) {
    //         return;
    //     }
    //     if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
    //         e.preventDefault();
    //     }
    // });
    // $("#weight").keyup(function (event) {
    //     if (event.which >= 37 && event.which <= 40) return;
    //     $(this).val(function (index, value) {
    //         return value
    //             .replace(/\D/g, "")
    //             .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    //     });
    // });


    $("#width").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#width").keyup(function (event) {
        if (event.which >= 37 && event.which <= 40) return;
        $(this).val(function (index, value) {
            return value
                .replace(/\D/g, "")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });


    $("#length").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#length").keyup(function (event) {
        if (event.which >= 37 && event.which <= 40) return;
        $(this).val(function (index, value) {
            return value
                .replace(/\D/g, "")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });

    $("#height").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#height").keyup(function (event) {
        if (event.which >= 37 && event.which <= 40) return;
        $(this).val(function (index, value) {
            return value
                .replace(/\D/g, "")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });

    $("#numOfFleets").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#numOfFleets").keyup(function (event) {
        if (event.which >= 37 && event.which <= 40) return;
        $(this).val(function (index, value) {
            return value
                .replace(/\D/g, "")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });

    $("#suggestedPrice").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#suggestedPrice").keyup(function (event) {
        if (event.which >= 37 && event.which <= 40) return;
        $(this).val(function (index, value) {
            return value
                .replace(/\D/g, "")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });

    $("#insuranceAmount").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#insuranceAmount").keyup(function (event) {
        if (event.which >= 37 && event.which <= 40) return;
        $(this).val(function (index, value) {
            return value
                .replace(/\D/g, "")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });


    $("#suggestedPrice").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#suggestedPrice").keyup(function (event) {
        if (event.which >= 37 && event.which <= 40) return;
        $(this).val(function (index, value) {
            return value
                .replace(/\D/g, "")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        });
    });


});
