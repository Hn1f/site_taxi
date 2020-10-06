/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/accueil.scss';
import '../css/connexion.scss';


// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
    import $ from 'jquery';
    import 'bootstrap';
    require('webpack-jquery-ui');
    
  $("#reservez").on("click",function() {
         $(".trajet1").hide();
         $(".trajet2").show();
         $("#trajet_depart").value=coordoneesDepart; 
         $("#trajet_arrive").value=coordoneesArrive; 
         });
      
    $(document).ready(function() { 
      
      var coordoneesDepart=new Array(2);
      var coordoneesArrive=new Array(2);                     

      if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(function(position){
            coordoneesDepart[0]= position.coords.latitude;
            coordoneesDepart[1] = position.coords.longitude;
            $("#trajet_depart").val(coordoneesDepart);
            $.ajax({
              url:"https://api-adresse.data.gouv.fr/reverse/?lon="+coordoneesDepart[1]+"&lat="+coordoneesDepart[0],
              success: function (data){
                document.getElementById("trajet_cp1").value= data.features[0].properties.postcode;
                document.getElementById("trajet_adresse1").value=data.features[0].properties.name;
      
              }
              
            })
        });
      } 

      $("#trajet_cp1").autocomplete({
        source: function (request, response) {
          $.ajax({
            url: "https://api-adresse.data.gouv.fr/search/?postcode="+$("input[name='trajet[cp1]']").val(),
            data: { q: request.term },
            dataType: "json",
            success: function (data) {
              var postcodes = [];
              response($.map(data.features, function (item) {
                // Ici on est obligé d'ajouter les CP dans un array pour ne pas avoir plusieurs fois le même
                if ($.inArray(item.properties.postcode, postcodes) == -1) {
                  postcodes.push(item.properties.postcode);
                  return { label: item.properties.postcode + " - " + item.properties.city, 
                      city: item.properties.city,
                      value: item.properties.postcode
                  };
                }
              }));
            }
          });
        }
      });
      
      $("#trajet_adresse1").autocomplete({
        source: function (request, response) {
          $.ajax({
            url: "https://api-adresse.data.gouv.fr/search/?postcode="+$("input[name='trajet[cp1]']").val(),
            data: { q: request.term },
            dataType: "json",
            success: function (data) {
              coordoneesDepart[0] =data.features[0].geometry.coordinates[1];
              coordoneesDepart[1] =data.features[0].geometry.coordinates[0];
              console.log(coordoneesDepart);
              $("#trajet_depart").val(coordoneesDepart);
              response($.map(data.features, function (item) {
                return { label: item.properties.name, value: item.properties.name};
              }));
            }
          });
        }
      });
      
      $("#trajet_cp2").autocomplete({
        source: function (request, response) {
          $.ajax({
            url: "https://api-adresse.data.gouv.fr/search/?postcode="+$("input[name='trajet[cp2]']").val(),
            data: { q: request.term },
            dataType: "json",
            success: function (data) {
              var postcodes = [];
              response($.map(data.features, function (item) {
                // Ici on est obligé d'ajouter les CP dans un array pour ne pas avoir plusieurs fois le même
                if ($.inArray(item.properties.postcode, postcodes) == -1) {
                  postcodes.push(item.properties.postcode);
                  return { label: item.properties.postcode + " - " + item.properties.city, 
                      city: item.properties.city,
                      value: item.properties.postcode
                  };
                }
              }));
            }
          });
        }
      });
      
      $("#trajet_adresse2").autocomplete({
        source: function (request, response) {
          $.ajax({
            url: "https://api-adresse.data.gouv.fr/search/?postcode="+$("input[name='trajet[cp2]']").val(),
            data: { q: request.term },
            dataType: "json",
            success: function (data) {
              coordoneesArrive[0] =data.features[0].geometry.coordinates[1];
              coordoneesArrive[1] =data.features[0].geometry.coordinates[0];
              console.log(coordoneesArrive); 
              $("#trajet_arrive").val(coordoneesArrive); 
              response($.map(data.features, function (item) {
                return { label: item.properties.name, value: item.properties.name};
              }));
            }
          });
        }
      });

      $(function(){
        setInterval(function(){
           $(".slideshow ul").animate({marginLeft:-3500},800,function(){
              $(this).css({marginLeft:0}).find("li:last").after($(this).find("li:first"));
           })
        }, 3500);
     })
     ;
     function lancement(){
       'use strict'; 
        $('#etoile1').click(function(){
          var valeurclique =$(this).attr('value'); 
          colorStar(1); 
        }); 
        $('#etoile1').hover(function() {
          colorStar(1); 
        }); 
        $('#etoile2').click(function(){
          var valeurclique=$(this).attr('value'); 
          colorStar(2); 
          reponseQuestion=valeurclique; 
        }); 
        $('#etoile2').hover(function() {
          colorStar(2); 
        }); 
        $('#etoile3').click(function(){
          var valeurclique=$(this).attr('value'); 
          colorStar(3); 
          reponseQuestion=valeurclique; 
        }); 
        $('#etoile3').hover(function() {
          colorStar(3); 
        });
        $('#etoile4').click(function(){
          var valeurclique=$(this).attr('value'); 
          colorStar(4); 
          reponseQuestion=valeurclique; 
        }); 
        $('#etoile4').hover(function() {
          colorStar(4); 
        }); 
        $('#etoile5').click(function(){
          var valeurclique=$(this).attr('value'); 
          colorStar(5); 
          reponseQuestion=valeurclique; 
        }); 
        $('#etoile5').hover(function() {
          colorStar(5); 
        });
          
    }

     function colorStar(n){
       switch(n){
         case 0: 
         $('.etoile1').css('color','black');
         $('.etoile2').css('color','black');
         $('.etoile3').css('color','black');
         $('.etoile4').css('color','black');
         $('.etoile5').css('color','black');
          break; 
         case 1: 
          $('.etoile1').css('color','#fed430');
          $('.etoile2').css('color','black');
          $('.etoile3').css('color','black');
          $('etoile4').css('color','black');
          $('etoile5').css('color','black');
          break;
         case 2: 
          $('.etoile1').css('color','#fed430');
          $('.etoile2').css('color','#fed430');
          $('.etoile3').css('color','black');
          $('.etoile4').css('color','black');
          $('.etoile5').css('color','black');
          break;
         case 3: 
          $('.etoile1').css('color','#fed430');
          $('.etoile2').css('color','#fed430');
          $('.etoile3').css('color','#fed430');
          $('.etoile4').css('color','black');
          $('.etoile5').css('color','black');
          break;
         case 4: 
          $('.etoile1').css('color','#fed430');
          $('.etoile2').css('color','#fed430');
          $('.etoile3').css('color','#fed430');
          $('.etoile4').css('color','#fed430');
          $('.etoile5').css('color','black');
          break;
         case 5: 
          $('.etoile1').css('color','#fed430');
          $('.etoile2').css('color','#fed430');
          $('.etoile3').css('color','#fed430');
          $('.etoile4').css('color','#fed430');
          $('.etoile5').css('color','#fed430');
          break;
         default: 
          break; 

       }
     }
    
     var reponseQuestion="0"; 
     window.onload = lancement;
     $('departP').val=coordoneesDepart; 
     $('arriveP').val=coordoneesArrive; 
     
    

 });