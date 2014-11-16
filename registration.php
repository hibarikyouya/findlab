<?php
require("utils.php");
Page::open();
Page::setSession();
Page::displayNavbar();
?>

<div class="container">

<div class="row">

<div class="col-md-4 col-md-offset-4">

<form role="form" class="form" method="post" action="valid_registration.php">

<div class="form-group">
<label>Login</label>
<br>
<input type="text" class="form-control" name="regist_login"
placeholder="Entrez un login"/>
</div>

<div class="form-group">
<label>Firstname</label>
<br>
<input type="text" class="form-control" name="regist_firstname"
placeholder="Votre prénom"/>
</div>

<div class="form-group">
<label>Lastname</label>
<br>
<input type="text" class="form-control" name="regist_lastname"
placeholder="Votre nom"/>
</div>

<div class="form-group">
<label>Password</label>
<br>
<input type="password" class="form-control" name="regist_password"
placeholder="Mot de passe"/>
</div>

<button type="submit" class="btn btn-default">Register</button<

</form>
</div>
</div>
</div>

<?php
Page::close();
?>

