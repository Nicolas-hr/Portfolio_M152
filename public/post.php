<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
  <link rel="stylesheet" href="./css/style.css">

  <title>Post</title>
</head>

<body>
  <!-- Navbar -->
  <?php include './includes/navbar.inc.html'; ?>

  <div class="post-form-container">
    <textarea class="form-control" name="postText" id="postText" cols="30" rows="2" placeholder="Write something..."></textarea><br>

    <img src="./assets/img/default-profile.jpg" alt="profile picture" height="30" width="30" class="img-thumbnail" id="ppSendPost" draggable="false">

    <!-- iamge -->
    <label for="inputImg" class="custom-file-upload">
      <img src="./assets/img/fileinput-img.svg" alt="icon appareil photo">
    </label>
    <input type="file" name="inputImg" id="inputImg" accept="image/*" multiple>

    <!-- video -->
    <label for="inputAudio" class="custom-file-upload">
      <img src="./assets/img/fileinput-audio.svg" alt="icon musique">
    </label>
    <input type="file" name="inputAudio" id="inputAudio" accept="audio/*" multiple>

    <!-- video -->
    <label for="inputVideo" class="custom-file-upload">
      <img src="./assets/img/fileinput-video.svg" alt="icon camera">
    </label>
    <input type="file" name="inputVideo" id="inputVideo" accept="video/*" multiple>

    <input type="submit" class="btn btn-secondary btn-light-gray" value="Boost Post">
    <input type="submit" class="btn btn-primary" value="Publish" id="btnSendPosts">
  </div>

  <!-- Footer -->
  <?php include './includes/footer.inc.html'; ?>
</body>

</html>