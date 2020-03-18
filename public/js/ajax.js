// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CONSTANTES ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
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

  $("#btnSendPosts").click(SendPost);
});

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CREATE POSTS FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
function SendPost(event) {
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
    },
    error: (error) => {
      console.log(error);
    }
  });
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ SHOW POSTS FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
    html += `<div class="container" id="${post.idPost}">
                <p id="postText">${post.comment}</p>
                <input type="text" value="${post.comment}" id="modify">`;

    if (post.medias != null) {
      let medias = post.medias.split(",");
      let types = post.types.split(",");
      html += `<table id="tableMedia">`;

      for (let i = 0; i < medias.length; i++) {
        if (types[i] == "image/png" || types[i] == "image/jpeg") {
          html += `<tr><td><img id="imgPosts" src="${MEDIA_PATH + medias[i]}" alt="uploaded image">
          </td><td class="removeMedia"><img src="./assets/img/delete-icon.svg" id="deleteIcon" onclick="RemoveMedia(event)"></td></tr>`;
        } else if (types[i] == "audio/mpeg") {
          html += `<td><audio controls> <source src="${MEDIA_PATH + medias[i]}" type="${types[i]}">
          </audio></td><td class="removeMedia"><img src="./assets/img/delete-icon.svg" id="deleteIcon" onclick="RemoveMedia(event)"></td></tr>`;

        } else if (types[i] == "video/mp4") {
          html += `<tr><td><video loop autoplay muted controls> <source src="${MEDIA_PATH + medias[i]}" type="${types[i]}">
          </video></td><td class="removeMedia"><img src="./assets/img/delete-icon.svg" id="deleteIcon" onclick="RemoveMedia(event)"></td></tr>`;
        }
      }

      html += `</table>`;
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

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ DELETE POSTS FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
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

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MODIFY/UPDATE POSTS FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
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

  let postContainer = button.closest(".container");
  let postText = postContainer.children().closest("#postText")[0].textContent;
  let btnValidate = $(`<button class="btn btn-primary" onclick="ValidateModification(event, $('#tbxTextModify').val(), $(this).closest('.container').attr('id'))" id="btnValidate">Valider</button>`);
  let btnCancel = $(`<button class="btn btn-secondary" onclick="GetPosts()">Annuler</button>`);
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

  let pathToTbody = postContainer[0].length == 4 ? postContainer[0].children[3].children[0] : null;

  if (postText != "") {
    postContainer.prepend(html);
    postContainer.children().closest("#postText").hide();
  }

  if (pathToTbody != null) {
    for (let i = 0; i < pathToTbody.children.length; i++) {
      console.log(pathToTbody.children[i]);
    }
  }

  // Récupère la source de chaque image dans le .container du bouton cliqué
  let imgSrc = button.closest('.container').children('img').map(function () {
    console.log($(this).attr('src'));

  });

  button.closest(".btn-group").append(btnValidate);
  button.closest(".btn-group").prepend(btnCancel);
  $('.removeMedia').show();
}

/**
 * @author Hoarau Nicolas
 * @date 10.03.2020
 * 
 * @brief Fonction qui met à jour les informations modifiées
 *
 * @param {*} event 
 * @param {string} text 
 * @param {int} idPost 
 * 
 * @version 1.0.0
 */
function ValidateModification(event, text, idPost) {
  if (event) {
    event.preventDefault();
  }

  let formdata = new FormData();
  
  let pathToForm = event.target.parentElement.parentElement.children['formPost'];
  
  let inputImg = pathToForm[1].files;
  let inputAudio = pathToForm[2].files;
  let inputVideo = pathToForm[3].files;
  let mediasSuppressed = GetMediaSuppressed(idPost);

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

  for (let i = 0; i < mediasSuppressed.length; i++) {
    formdata.append("mediasSuppressed[]", mediasSuppressed[i]);
  }

  $.ajax({
    type: "post",
    url: AJAX_PATH + 'modifyPost.php',
    contentType: false,
    processData: false,
    data: formdata,
    dataType: "json",
    success: (response) => {
      GetPosts();
    }, error: (err) => {
      console.log(err);
    }
  });
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
  if (event.target.files) {
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

/**
 * @author Hoarau Nicolas
 * @date 17.03.2020
 * 
 * @brief Fonction qui retire de l'affichage le media sélectionné
 * 
 * @param {*} event 
 * 
 * @version 1.0.0
 */
function RemoveMedia(event) {
  let pathToMedia = event.target.parentElement.parentElement.firstChild.childElementCount == 1 ? event.target.parentElement.parentElement.firstChild.firstChild : event.target.parentElement.parentElement.firstChild.children[1];

  pathToMedia.style.display == 'none' ? pathToMedia.style.display = 'initial' : pathToMedia.style.display = 'none';
}

/**
 * @author Hoarau Nicolas
 * @date 18.03.2020
 * 
 * @brief Recupère les medias qui vont être supprimés du post
 * @param {int} idPost 
 * 
 * @returns {array} fichier à supprimer
 */
function GetMediaSuppressed(idPost) {
  let tableMedia = $('#' + idPost)[0].children['tableMedia'].children[0];
  let mediaSuppressed = new Array();

  // parcour le tableau de medias dans le post
  for (let i = 0; i < tableMedia.childElementCount; i++) {
    const tr = tableMedia.children[i].children[0];
    let media = tr.childElementCount == 1 ? tr.children[0] : tr.children[1]; // récipère le media du tr

    if (media.style.display == 'none') {
      if (media.childElementCount == 0) {
        mediaSuppressed.push(media.attributes['src'].value);
      } else {
        mediaSuppressed.push(media.children[0].attributes['src'].value);
      }
    }
  }

  return mediaSuppressed;
}