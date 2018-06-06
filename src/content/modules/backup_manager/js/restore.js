// Checks password before submitting the form
$("#restore-form").submit(function(event) {
	event.preventDefault();
	var password = $("#password").val();
	var url = $("#password").data("url");
	$.ajax({
		data : {
			password : password
		},
		url : url,
		success : function(result) {
			$("#restore-form").off("submit").submit();
		},
		error : function(xhr, status, error) {
			$("#password").val("");
			alert(Translation.WrongPassword + "!");
		}
	});
});
