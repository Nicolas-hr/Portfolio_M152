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
  <?php include './includes/navbar.html'; ?>

  <div class="container">
    <form action="" method="post">
      <div class="post-container">
        <img src="./img/default-profile.jpg" alt="profile picture" height="30" width="30" class="img-thumbnail" id="pp-send-post">
        <textarea class="form-control" name="post-text" id="post-text" cols="30" rows="2" placeholder="Write something..."></textarea><br>
      </div>
      <label for="inputFile" class="custom-file-upload">
        <img src="./img/fileinput-img.logo.svg" alt="logo appareil photo">
      </label>
      <input type="file" name="inputFile" id="inputFile" accept="image/*" multiple>
      <button type="submit" class="btn btn-secondary btn-light-gray">Boost Post</button>
      <button type="submit" class="btn btn-primary">Publish</button>
    </form>
  </div>

  <!-- Footer -->
  <?php include './includes/footer.html'; ?>
</body>

</html>