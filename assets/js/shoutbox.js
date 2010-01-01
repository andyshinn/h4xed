$(document).ready(function() {
	timestamp = 0;
	updateMsg();
	hideLoading();
	$("form#chatform").submit(function() {
		showLoading();
		$.post("/shoutbox2/backend", {
			message : $("#content").val(),
			name : $("#name").val(),
			action : "postmsg",
			time : timestamp
		}, function(xml) {
			addMessages(xml);
			$("#content").val("");
			hideLoading();
			$("#content").focus();
		});
		return false;
	});
});
function rmContent() {

}

function showLoading() {
	$("#contentLoading").show();
	$("#txt").hide();
	$("#author").hide();
}

function hideLoading() {
	$("#contentLoading").hide();
	$("#txt").show();
	$("#author").show();
}

function addMessages(xml) {
	if ($("status", xml).text() == "2")
		return;
	timestamp = $("time", xml).text();
	$("message", xml).each(
			function(id) {
				message = $("message", xml).get(id);
				$("#messages").prepend(
						"<dt>" + $("author", message).text() + "</dt>"
								+ "<dd>" + $("text", message).text() + "</dd>");
			});

}

function updateMsg() {
	$.post("/shoutbox2/backend", {
		time : timestamp
	}, function(xml) {
		$("#loading").remove();
		addMessages(xml);
	});
	setTimeout('updateMsg()', 10000);
}