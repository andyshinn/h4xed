<html>
<head>
	<title>AJAX with jQuery Example</title>
	<script type="text/javascript" src="/assets/js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="/assets/js/shoutbox.js"></script>

</head>
<body>
	<div id="wrapper">
	<p id="messagewindow"><span id="loading">Loading...</span></p>
	<form id="chatform">
	<div id="author">
	Name: <input type="text" id="name" />
	</div><br />

	<div id="txt">
	Message: <input type="text" name="content" id="content" value="" />
	</div>
	
	<div id="contentLoading" class="contentLoading">  
	<img src="/assets/images/shoutbox/blueloading.gif" alt="Loading data, please wait...">  
	</div><br />
	
	<input type="submit" value="ok" />POOP<br />
	</form>
	</div>
</body>
</html>