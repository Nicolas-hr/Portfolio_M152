const FILESIZE_MAX = 3145728;
const MEDIA_PATH = "./assets/upload/";
const AJAX_PATH = "../App/php/";

$(document).ready(() => {
  let pageName = window.location.pathname.substring(
    location.pathname.lastIndexOf("/") + 1
  );

  if (pageName == "index.php" || pageName == "") {
    GetPosts();
  }

  $("#btnSendPosts").click(sendPost);
});

/**
 * @author Hoarau Nicolas
 * @date 30.01.2020
 *
 * @brief Fonction qui permet d'envoyer un post
 *
 * @param event
 *
 * @version 1.0.0
 */
function sendPost(event) {
  if (event) {
    event.preventDefault();
  }

  let formdata = new FormData();

  let content = $("#postText").val();
  formdata.append("postText", content);

  for (let x = 0; x < document.getElementById("inputImg").files.length; x++) {
    let file = document.getElementById("inputImg").files[x];

    if (file["size"] < FILESIZE_MAX) {
      formdata.append("medias[]", file);
    } else {
      console.log("trop gros");
    }
  }

  for (let x = 0; x < document.getElementById("inputAudio").files.length; x++) {
    let file = document.getElementById("inputAudio").files[x];
    formdata.append("medias[]", file);
  }

  for (let x = 0; x < document.getElementById("inputVideo").files.length; x++) {
    let file = document.getElementById("inputVideo").files[x];
    formdata.append("medias[]", file);
  }

  $.ajax({
    type: "post",
    url: AJAX_PATH + "sendPosts.php",

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
 * 
 * @version 1.0.0
 */
function GetPosts() {
  $.ajax({
    type: "post",
    url: AJAX_PATH + "getPosts.php",
    dataType: "json",
    success: data => {
      ShowPosts(data);
    }
  });
}

/**
 * @author Hoarau Nicolas
 * @date 20.02.2020
 *
 * @brief Fonction qui affiche les posts
 *
 * @param {array} posts tableau des posts reçu via la fonction GetPost
 * 
 * @version 1.0.0
 */
function ShowPosts(posts) {
  let html = "";

  $.each(posts, (index, post) => {
    html += `<div class="container" id="${post.idPost}"><p id="postText">${post.comment}</p> <input type="text" value="${post.comment}" id="modify">`;

    if (post.medias != null) {
      let medias = post.medias.split(",");
      let types = post.types.split(",");

      for (let i = 0; i < medias.length; i++) {
        if (types[i] == "image/png" || types[i] == "image/jpeg") {
          html += `<img id="imgPosts" src="${MEDIA_PATH +
            medias[i]}" alt="uploaded image"><br>`;
        } else if (types[i] == "audio/mpeg") {
          html += `<audio controls> <source src="${MEDIA_PATH +
            medias[i]}" type="${types[i]}"></audio><br>`;
        } else if (types[i] == "video/mp4") {
          html += `<video loop autoplay muted controls> <source src="${MEDIA_PATH +
            medias[i]}" type="${types[i]}"></video><br>`;
        }
      }
    }
    html += `<div class="btn-group">
              <button type="button" class="btn btn-light" onclick="ModifyPost($(this)) ">Modifier</button>
              <button type="button" id="btnDelete" class="btn btn-danger" onclick="DeletePost($(this).closest('.container').attr('id'));">Supprimer</button>
            </div>
          </div>
          <hr>`;
  });
  $("#posts").html(html);
}

/**
 * @author Hoarau Nicolas
 * @date 03.03.2020
 *
 * @brief Fonction qui supprime le post choisis
 *
 * @param {int} idPost id du post à supprimer
 * 
 * @version 1.0.0
 */
function DeletePost(idPost) {
  $.ajax({
    type: "post",
    url: AJAX_PATH + "deletePost.php",
    data: { idPost: idPost },
    dataType: "json",
    success: () => {
      GetPosts();
    }
  });
}

/**
 * @author Hoarau Nicolas
 * @date 02.03.2020
 * 
 * @brief Fonction qui affiche les modifications faites
 * 
 * @param {*} button 
 * 
 * @version 1.0.0
 */
function ModifyPost(button) {
  button.hide();

  let closestButtonCopenant = button.closest(".container");
  let postText = closestButtonCopenant.children().closest("#postText")[0].textContent;
  let btnValidate = $(`<button class="btn btn-primary" onclick="ValidateModification(event, $('#tbxTextModify').val(), $(this).closest('.container').attr('id'))" id="btnValidate">Valider</button>`);
  let btnCancel = $(`<button class="btn btn-secondary" onclick="CancelModification($(this))">Annuler</button>`);
  let html = `<form method="post" id="formPost" enctype="multipart/form-data">
                <table>
                  <tr>
                    <td>
                      <input type="text" id="tbxTextModify" value="${postText}"></td>
                    </td>
                    <td>
                      <!-- iamge -->
                      <label for="inputImg" class="custom-file-upload">
                        <img src="./assets/img/fileinput-img.svg" alt="icon appareil photo">
                      </label>
                      <input type="file" name="inputImg" id="inputImg" onchange="DisplayMedias(event, $(this))" accept="image/*" multiple>
                    </td>
                    <td>
                      <!-- video -->
                      <label for="inputAudio" class="custom-file-upload">
                        <img src="./assets/img/fileinput-audio.svg" alt="icon musique">
                      </label>
                      <input type="file" name="inputAudio" id="inputAudio" onchange="DisplayMedias(event, $(this))" accept="audio/*" multiple>
                    </td>
                    <td>
                      <!-- video -->
                      <label for="inputVideo" class="custom-file-upload">
                        <img src="./assets/img/fileinput-video.svg" alt="icon camera">
                      </label>
                      <input type="file" name="inputVideo" id="inputVideo" onchange="DisplayMedias(event, $(this))" accept="video/*" multiple>
                    </td>
                  </tr>
                </table>
              </form>`;

  if (postText != "") {
    closestButtonCopenant.prepend(html);
    closestButtonCopenant.children().closest("#postText").hide();
  }

  // Récupère la source de chaque image dans le .container du bouton cliqué
  // let imgSrc = button.closest('.container').children('img').map(function () {
  //   return $(this).attr('src')
  // });

  button.closest(".btn-group").append(btnValidate);
  button.closest(".btn-group").prepend(btnCancel);
}

/**
 * @author Hoarau Nicolas
 * @date 10.03.2020
 * 
 * @brief Fonction qui met à jour les informations modifiées
 * 
 * @param {string} text 
 * 
 * @version 1.0.0
 */
function ValidateModification(event, text, idPost) {
  if (event) {
    event.preventDefault();
  }

  let pathToTr = ($('.vsc-controller').length == 0) ? event.target.closest('.container').children[0].children[0].children[0].children[0] : event.target.closest('.container').children[1].children[0].children[0].children[0];
  
  let formdata = new FormData();

  let inputImg = pathToTr.children[1].children[1].files;
  let inputAudio = pathToTr.children[2].children[1].files;
  let inputVideo = pathToTr.children[3].children[1].files;

  formdata.append("postText", text);
  formdata.append("idPost", idPost);

  for (let x = 0; x < inputImg.length; x++) {
    let file = inputImg[x];

    if (file["size"] < FILESIZE_MAX) {
      formdata.append("medias[]", file);
    } else {
      console.log("trop gros");
    }
  }

  for (let x = 0; x < inputAudio.length; x++) {
    let file = inputAudio[x];
    formdata.append("medias[]", file);
  }

  for (let x = 0; x < inputVideo.length; x++) {
    let file = inputVideo[x];
    formdata.append("medias[]", file);
  }

  $.ajax({
    type: "post",
    url: AJAX_PATH + 'modify.php',
    contentType: false,
    processData: false,
    data: formdata,
    dataType: "json",
    success: (response) => {
      console.log(response.Success);

      window.location.reload();
    }, error: (err) => {
      console.log(err);
    }
  });
}

/**
 * @author Hoarau Nicolas
 * @date 09.03.2020
 * 
 * @brief Fonction qui annule les modifications faites
 * 
 * @param {*} button 
 * 
 * @version 1.0.0
 */
function CancelModification(button) {
  button.hide();

  button.closest('.btn-group').children().closest('.btn-primary').hide();
  button.closest('.btn-group').children().closest('.btn-light').show();
  button.closest(".container").children()[0].style.display = "none";
  $(".mediaAdded").hide();
  $('#postText').show();
}

/**
 * @author Hoarau Nicolas
 * @date 12.03.2020
 * 
 * @brief Fonction affiche les images dès qu'elles sont choisis dans l'input
 * 
 * @param {*} input 
 * @param {*} event 
 * 
 * @version 1.0.0
 */
function DisplayMedias(event, input) {
  if (event.target.files) 
  {
    let html = ``;

    for (let i = 0; i < event.target.files.length; i++) {
      let media = event.target.files[i];
      if (media.type == "image/png" || media.type == "image/jpeg") {
        html += `<img id="imgPosts" class="mediaAdded" src="${URL.createObjectURL(media)}" alt="uploaded image"><br>`;
      } else if (media.type == "audio/mpeg") {
        html += `<audio controls class="mediaAdded"> <source src="${URL.createObjectURL(media)}" type="${media.type}"></audio><br>`;
      } else if (media.type == "video/mp4") {
        html += `<video loop autoplay muted controls class="mediaAdded"> <source src="${URL.createObjectURL(media)}" type="${media.type}"></video><br>`;
      }
    }

    input.closest('.container').find(".btn-group").before(html);
  }
}