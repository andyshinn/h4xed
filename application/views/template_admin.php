<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>H4XED Radio Admin</title>
<style type="text/css">
<!--
div#welcome {
    float: right;
}
div#head {
    float: left;
    height: 2em;
    width: 50%;
}
div#message {
    float: left;
}
div#links {
    clear: left;
}
-->
</style>
</head>

<body>
<div id="head">
    <div id="message">h4xed radio minimal admin</div>
    <div id="welcome">welcome, <?php echo $user ?> - edit - <?php echo anchor('auth/logout', 'logout'); ?></div>
</div>
<div id="links">links:
  <ul id="links_list">
    <li>news: list, add</li>
    <li>requests: list</li>
  </ul>
</div>
<div id="breadcrumbs"><?php echo set_breadcrumb(); ?></div>
<div id="main"><?php echo $main ?></div>

</body>
</html>