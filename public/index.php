<?php require_once __DIR__. '/includes/const.inc.php';?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
  <link rel="stylesheet" href="./css/style.css">
  <title>Portfolio M152</title>
</head>

<body>
  <!-- Navbar -->
  <?php include './includes/navbar.inc.html'; ?>

  <div class="alert alert-dark alert-dismissible fade show" role="alert">
    Bienvenue
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

  <div class="card">
  <img src="./assets/img/city-dawn-sky-373893.jpg" class="card-img-top" alt="Img of city" draggable="false">
    <div class="card-body">
    <h5 class="card-title">Portfolio M152</h5>
      <p class="card-text">
        <img src="<?=PROFILE_PHOTO?>" alt="profile picture" height="25" width="25" draggable="false">
        <small class="text-muted">45 Followers, 13 Posts</small></p>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">
        Bootstrap Examples
        <small><a href="#" class="stretched-link">View all</a></small>
      </h5>
      <p class="card-text"></p>
    </div>
  </div>

  <div id="post"></div>

  <!-- Footer -->
  <?php include './includes/footer.inc.html'; ?>
</body>

</html>