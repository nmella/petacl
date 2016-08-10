$(document).ready(function(){

	$(document).pngFix();

	var invalidMailError	= "E-mail no es válido";
	var duplicateMailError	= "E-mail ya está en la lista.";
	var systemError			= "Ocurrió un error.";
	var successMessage		= "Su email ha sido agregado.";
	
	$(".successBalloon").hide();
	$(".errorBalloon").hide();
	$(".resultText").hide();
	$(".loader").hide();
	
    $('form#newsletterForm').bind('submit', function(e){
		$(".successBalloon").hide();
		$(".errorBalloon").hide();
		$(".resultText").hide();
		$(".loader").show();
		var email  = $('input#email').val();
        e.preventDefault();
		
		$.ajax({
			type: 'POST',
			url: 'newsletter.php?email='+email,
			data: '',
			success: function(theResponse){
				$(".resultText").fadeIn("slow");
				$(".resultText").animate({opacity: 1.0}, 3000);
				$(".resultText").fadeOut(1500);
				
				if (theResponse == 1) {
					$(".successBalloon").fadeIn("slow");
					$(".successBalloon").animate({opacity: 1.0}, 3000);
					$(".successBalloon").fadeOut(1500);
					$(".resultText").html(successMessage);
				}
				if (theResponse == 2) {
					$(".errorBalloon").fadeIn("slow");
					$(".errorBalloon").animate({opacity: 1.0}, 3000);
					$(".errorBalloon").fadeOut(1500);
					$(".resultText").html(invalidMailError);
				}
				if (theResponse == 3) {
					$(".errorBalloon").fadeIn("slow");
					$(".errorBalloon").animate({opacity: 1.0}, 3000);
					$(".errorBalloon").fadeOut(1500);
					$(".resultText").html(duplicateMailError);
				}
				$(".loader").hide();
			},
			error: function(){
				$(".errorBalloon").fadeIn("slow");
				$(".errorBalloon").animate({opacity: 1.0}, 3000);
				$(".errorBalloon").fadeOut(1500);
				$(".resultText").html(systemError);
				$(".loader").hide();
			}		
		});
	});
			
});
