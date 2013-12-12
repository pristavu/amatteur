$(document).ready(function(){

	var i = 2;
	var loaded = 0;

        window.onscroll = function(){
		var currPos = window.pageYOffset;
		var docLength = $(document).height();
		var ratio = parseFloat(currPos/docLength);
	 
		if(ratio > 0.20 && loaded < i){
			
			$("#floader").show();
		
			//ratio = '';
                        var urlSearch = $("#urlSearch").val();
                        var url="";
                        if (urlSearch)
                        {
                            if(urlSearch.indexOf('?')>-1)
                            {
                                var query = urlSearch.split('?');
                                url=window.location+((urlSearch).indexOf('?')>-1?"&":"?")+"page="+i+"&RSP=ajax&" + query[1];
                            }
                            else
                            {
                                url=window.location+((window.location.href).indexOf('?')>-1?"&":"?")+"page="+i+"&RSP=ajax";
                            }
                            
                        }
                        else
                        {
                            url=window.location+((window.location.href).indexOf('?')>-1?"&":"?")+"page="+i+"&RSP=ajax";
                        }
			loaded = i;
			$.ajax({
                                url:url,
                                dataType:"html",
                                type:"POST",
                                success:function(d){

                                    i++;


                                    //$("#Page").val(i);

                                   //$("#container").append("<div class='batch"+loaded+"'/></div>")

                                    $.each($(d),function(c,e){

                                            if($(e).hasClass('pin') || $(e).hasClass('person')  || $(e).hasClass('tappable')){
                                                    setTimeout(function(){
                                                            $("#container").append($(e));

                                                            $.each($(".notLoadedImg"),function(){
                                                                var img = new Image();
                                                                img.src = $(this).data('src');

                                                                var that = this;

                                                                $(that).attr('src',img.src);
                                                                $(that).removeClass('notLoadedImg');
                                                            })
                                                            if ($('#container').width()>768)
                                                            {
                                                                    $('#container').masonry('reload');
                                                            }
                                                    })
                                            }


                                            $("#floader").hide();



                                    })



                                },

                                error:function(error){

                                }
					
			});
			
		}
	}
	
	
	

})
