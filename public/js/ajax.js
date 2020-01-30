$(document).ready(function () {
    $("#btnSendPosts").click(sendPost);

});

/**
 * Fonction qui permet d'envoyer un post
 */
function sendPost(event) {
    formdata = new FormData();

    if (event) {
        event.preventDefault();
    }

    let content = $("#postText").val();
    formdata.append("content", content);

    let images = $("#inputImg").prop("files");
    formdata.append("inputImg", images);

    /*
        let videos = $("#inputVideo").prop("files");

        let audio = $("#inputAudio").prop("files");
*/
    $.ajax({
        type: "post",
        url: "../App/php/sendPosts.php",
        data: formdata,
        dataType: "json",
        success: function (response) {
            console.log("test");
            
        }
    });
}