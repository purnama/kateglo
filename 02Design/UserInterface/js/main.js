function resetHomeContainer() {

    var height = $(window).height();

   if(height > 150){
       var newHeight = (height-250)/2;
       if(newHeight > 150){
        $('#home-container').attr("style", "margin-top: " + newHeight + "px;");
       }else{
           $('#home-container').removeAttr("style");
       }
   }else{
       $('#home-container').removeAttr("style");
   }
}
