$(document).ready( () => {
    $("#btnSendPosts").click(sendPost);

});

/**
 * Fonction qui permet d'envoyer un post
 */
function sendPost(event) {
    if (event) {
        event.preventDefault();
    }

    let formdata = new FormData();

    let content = $("#postText").val();
    formdata.append("postText", content);

    let ins = document.getElementById('inputImg').files.length;
    for (let x = 0; x < ins; x++) {
        formdata.append("inputImg[]", document.getElementById('inputImg').files[x]);
    }

    $.ajax({
        type: "post",
        url: "../App/php/sendPosts.php",

        // pour l'upload de fichier
        contentType: false,
        processData: false,

        data: formdata,
        dataType: "json",
        success: (response) => {
            console.log("test");
        }
    });
}