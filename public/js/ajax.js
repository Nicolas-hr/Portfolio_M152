const FILESIZE_MAX = 3145728;

$(document).ready(() => {
  let pageName = window.location.pathname.substring(location.pathname.lastIndexOf("/") + 1);
  
  $("#btnSendPosts").click(sendPost);

  if (pageName == "index.php") {
    GetPosts();
  }
});

/**
 * @author Hoarau Nicolas
 * @date 30.01.2020
 * 
 * @brief Fonction qui permet d'envoyer un post
 * 
 * @param event
 * 
 * @version : 1.0.0
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
    success: () => {
      window.location.href = "./index.php";
    }
  });
}

/**
 * @author Hoarau Nicolas
 * @date 20.02.2020
 * 
 * @brief Fonction qui récupère les posts dans la base de données via ajax
 */
function GetPosts() {
  $.ajax({
    type: "post",
    url: "../App/php/getPosts.php",
    dataType: "json",
    success: data => {
      ShowPosts(data)
    },
  });
}

/**
 * @author Hoarau Nicolas
 * @date 20.02.2020
 * 
 * @brief Fonction qui affiche les posts
 * 
 * @param {array} posts tableau des posts reçu via la fonction GetPost
 */
function ShowPosts(posts) {

}