$(function () {
              $('#registration').on('submit', function(e) {
              e.preventDefault();
              var $this = $(this); 
              $.ajax({
              url: $this.attr('action'), 
              type: $this.attr('method'), 
              data: $this.serialize(), 
              dataType: 'json', 
              success: function(data) { 
                console.log(data.existe);
                if(data.existe){
                  //v.text("un compte avec la meme adresse mail éxiste déja ")
                  //v.show();
                  $('#sub').after("<div class='col-md-12 mb-3 alert alert-danger'>compte existe</div>");
                  
                }
                else{
                  $('#sub').after("<div  class='col-md-12 mb-3 alert alert-success'>compte créé avec succes ! Un mail vous a été envoyé pour activer votre compte</div>");  
                }
              }
            });
          });
         }); 