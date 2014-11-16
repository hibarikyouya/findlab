<?php
require("utils.php");
Page::open();
Page::setSession();
Page::displayNavbar();
Result::insertResults($_POST['articles']);
?>

<div class="container">

<h1>Find a lab that suits you ! Who is working on what and where ?</h1>

<div class="row">

<nav class="col-xs-3 bs-docs-sidebar">
<ul class="nav nav-list bs-docs-sidenav affix">
<li class="active">
<a href="#map_section">Where are the institutes ?</a>
</li>
<li class="active">
<a href="#countries_section">Is USA first again ?</a>
</li>
<li class="active">
<a href="#timeline_section">Is your query fashionable ?</a>
</li>
<li class="active">
<a href="#results_section">Wanna see the details ?</a>
</li>
</ul>
</nav>

<div class="col-xs-9">

<section id="map_section">
<div class="page-header">
<h2>Where are the institutes ?</h2>
</div>
<div id="map" class="col-md-5 col-md-offset-2"
style="width:700px; height:500px;"></div>
</section>

<section id="countries_section">
<div class="page-header">
<h2>Are USA first again ?</h2>
</div>
<div id="piechart">Make a query and you'll see ! ;)</div>
</section>

<section id="timeline_section">
<div class="page-header">
<h2>Is your query fashionable ?</h2>
</div>
<div id="timeline">Make a query and you'll see ! ;)</div>
</section>

<section id="results_section">
<div class="page-header">
<h2>Wanna see the details ?</h2>
</div>
<div id="results">Make a query and you'll see ! :)</div>
</section>

</div>

</div>

<?php
Page::close();
?>

