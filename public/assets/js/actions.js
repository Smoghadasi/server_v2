$(document).ready(function () {
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

$('#destination_city_id').select2({
    dir: "rtl",
    language: "fa"
});
$('#origin_city_id').select2({
    dir: "rtl",
    language: "fa"
});
$('#city_id').select2({
    dir: "rtl",
    language: "fa"
});


document.getElementById("searchCity").style.display = "none";
document.getElementById("searchState").style.display = "none";


function showOrHideElement(showElementId, hideElementId) {
    document.getElementById(showElementId).style.display = "block";
    document.getElementById(hideElementId).style.display = "none";
}

function showOrHideElementInBearingSearch(showElementId) {

    document.getElementById("searchCity").style.display = "none";
    document.getElementById("searchWord").style.display = "none";
    document.getElementById("searchState").style.display = "none";

    document.getElementById(showElementId).style.display = "block";
}
