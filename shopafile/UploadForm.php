<div id="UploadForm" class="FileForm">
    <div style="width:100%">
        <h3 style="float: left;">Upload New File</h3>
        <img src="close.png" style="float: right; padding-top:16px;" class="close" onclick="HideUploadForm();"></img>
    </div>
    <br>
    <table>
        <tr>
            <td colspan="2">
                Description <br>
                <textarea id="fileDescription"></textarea>
            </td>
        </tr>
        <tr>
            <td>Price</td>
            <td>
                $ <input type="number" id="filePrice" onkeyup="ComputeReceive();" style="width:100px;">
                <br>
                <span id="fileYWR"></span>
            </td>
        </tr>
        <tr>
            <td>Image</td>
            <td>
                <input type="file" id="fileImage">
            </td>
        </tr>
        <tr>
            <td>The File</td>
            <td>
                <input type="file" id="fileFile">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="checkbox" id="fileAllowed"> I am legally allowed to sell this file
            </td>
        </tr>
    </table>
    <br>
    <a class="button greenBG" onclick="UploadFile();" >Upload File</a>
</div>
