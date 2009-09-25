<html>
<head>
<title>My Form</title>
</head>
<body>

<?php echo validation_errors(); ?>

<?= $form_open ?>

<h5>Track</h5>
<?= $input_track ?>

<h5>Name</h5>
<?= $input_name ?>

<div><input type="submit" value="Submit" /></div>

</form>

</body>
</html>