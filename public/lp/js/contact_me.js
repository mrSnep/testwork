// Contact Form Scripts

$(function() {

    $("#contactForm input,#contactForm textarea").jqBootstrapValidation({
        preventSubmit: true,
        submitError: function($form, event, errors) {
            // additional error messages or events
        },
        submitSuccess: function($form, event) {
            event.preventDefault(); // prevent default submit behaviour
			var url = $form.attr('action');
			var data =  $form.serialize();
			$.ajax({
                        url: url,
                        data: data,
						dataType: "json",
						type : "POST",
                        success : function(data) {
                            if(data.errors){
								 swal({
                                    title: 'Bad...',
                                    text: data.errors,
                                    type: 'error',
                                    timer: '1500'
                                })
                                
                            }
                            else {
                                swal({
                                    title: 'Success!',
                                    text: data.message,
                                    type: 'success',
                                    timer: '1500'
                                })
                            }
                        },
                        error : function(data){
                          swal({
                                    title: 'Error...',
                                    text: data.errors,
                                    type: 'error',
                                    timer: '1500'
                                })
                  		}
			});
        }   
});
});

