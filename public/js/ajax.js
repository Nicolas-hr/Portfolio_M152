const FILESIZE_MAX = 3145728;

$(document).ready(() => {
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

  let ins = document.getElementById("inputImg").files.length;

  for (let x = 0; x < ins; x++) {
    let file = document.getElementById("inputImg").files[x];

    if (file["size"] < FILESIZE_MAX) {
      formdata.append("inputImg[]", file);
    } else {
      console.log("trop gros");
    }
  }

  $.ajax({
    type: "post",
    url: "../App/php/sendPosts.php",

    // pour l'upload de fichier
    contentType: false,
    processData: false,

    data: formdata,
    dataType: "json",
    success: response => {
//      window.location.href = "./index.php";
    }
  });
}
