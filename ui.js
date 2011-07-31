$(document).ready(function() {

	$("#help-icon").click(

		function(event) {
	
			if ($("#help-icon").html()=="?") {

				$("#information").show();
				$("#help-icon").html("X");
			}
			else {
		                $("#information").hide();
                                $("#help-icon").html("?");
			}
		}
	);

	$("#clear").click(

		function(event) {

			event.preventDefault();
			$('form [type="text"]').val("");
		}
	);
});
