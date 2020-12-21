
function UploadFile () {
    var formdata = new FormData();
    formdata.append('description', $("#fileDescription").val());
    formdata.append('price', $("#filePrice").val());
    formdata.append('image', $("#fileImage")[0].files[0]);
    formdata.append('file', $("#fileFile")[0].files[0]);

    $.ajax({
        url: "upload.php",
        method: "POST",
        data : formdata,
        cache: false,
        contentType: false,
        processData: false,
        success: function(response) {
            console.log(response);
        }
    });
    HideFileForm();
}

function HideFileForm(form) {
    $("#overlay").hide();
    $("#files").css('opacity', '1');
    $("#" + form + "Form").hide();
}

function ShowFileForm(form) {
    $("#overlay").show();
    $("#files").css('opacity', '0.25');
    $("#" + form + "Form").show();
}

function ComputeReceive() {
    var price = $('#filePrice').val();
    var ywr = price - 0.99;
    if (price > 33)
        ywr = 0.97 * price;
    $('#fileYWR').html('You will receive: $' + parseFloat(ywr).toFixed(2));
}

function DeleteFile(FileID) {
    if (confirm ("Are you sure you want to delete this file?")) {
        $.ajax({
            type: "GET",
            url: "delete.php?FileID=" + FileID,
            data: $(this).serialize(),
            dataType: 'text',
            success: function(response) {
                alert ("This file has been deleted");
            }
        });
    }
}

function EditFile(FileID) {
    $("#divLoader").show();
    $.ajax({
        type: "GET",
        url: "GetUpdateData.php?FileID=" + FileID,
        data: $(this).serialize(),
        dataType: 'text',
        success: function(response) {
            var json = JSON.parse(response);
            for (var i = 0; i < json.length; i++) {
                var j = json[i];
                $("#updateDescription").val(j.description);
                $("#updatePrice").val(j.price);
                $("#updateImageCurrent").attr("src","data:image;base64," + j.image);
                $("#updateFileName").html(j.FileName);
                $("#divLoader").hide();
                ShowFileForm("Edit");
            }

            //alert ("This file has been updated");
        }
    });

}
