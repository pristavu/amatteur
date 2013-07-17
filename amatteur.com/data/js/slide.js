$(document).ready(function(){
	
	
	$.ajax({
		url:slidesControllerUrl,
		dataType:"JSON",
		success:function(data){
			var picCount = $(data).size();
			$.each(data, function(i,e){
				$(".slides_container").append("<div data-count='"+i+"'><img  src='"+e.image+"'/></div>");
				if(i == 0){
					$(".slides_controlls ul").append("<li><a href='' id='"+i+"'><span class='activeControll'></span></a></li>")
				}else{
					$(".slides_controlls ul").append("<li><a href='' id='"+i+"'><span></span></a></li>")
				}
			});
			
			$(".slides_controlls ul li a").click(function(event){
				event.preventDefault();
			});	
			
			startSlide();
		}
	});
	
	
	function startSlide(){
		
		var controll =  $(".activeControll");
		$(".slides_container div img").each(function(i,e){
			$(e).css('width','100%');
			
				$(e).css('top',("-"+($(e).height()/2)-125)+"px")
				
				
				
			
			if(i > 0){
				$(e).parent().addClass('hidden');
			}else{
				$(e).parent().addClass('activeImg');
			}
		});
		
		
		

		
		var count = $(".slides_container div").size();
		
		
		var interval = setInterval(function(){
			var i = $('.activeImg').data('count');
			
			$(".slides_controlls ul li a").click(function(event){
				i = $('.activeImg').data('count');
				$(".activeControll").removeClass('activeControll');
				$(this).find('span').addClass('activeControll');
				var that = this
				$(".activeImg").hide().removeClass("activeImg").addClass("hidden");
				$.each($(".slides_container div"),function(i,e){
					//console.log($(e).data('count'));
					if($(e).data('count') == $(that).attr('id')){
						$(e).show().addClass('activeImg').removeClass('hidden');
						setTimeout($("#slides").data('int')*1000);
					}
				});
			});
			
			var next  = $(".activeImg").next();
			
			var nextControll = $(".slides_controlls ul li a span.activeControll").parent().parent().next();
			
			
			if(i < count-1){
				$(".activeImg").hide().removeClass("activeImg").addClass("hidden");
				$(".slides_controlls ul li a span.activeControll").removeClass('activeControll');
				
				$(next).fadeIn('500','linear').removeClass('hidden').addClass('activeImg');
				$(nextControll).find('span').addClass('activeControll');
				i++;
			}else{
			
				$(".slides_controlls ul li a span.activeControll").removeClass('activeControll');
				$(".slides_controlls ul li a span:first").addClass('activeControll');
				$(".slides_container div:first").fadeIn('500','linear').addClass('activeImg').removeClass('hidden');
				
				i=0;
			}
			
				
					
			
		
			
		},$("#slides").data('int')*1000);
		
	}

	
	
	
});




function getVideoImage(){
	$(document).ready(function(){
		$.ajax({
			url:slidesControllerUrl+"/videoImage",
			dataType:"JSON",
			success:function(data){
				if(data.image){
					$(".headerVideoImage").append("<img src='"+data.image+"'/>");
					
					playVideo();
				}
			}
		});
	});
}


function playVideo(){
	$('#videoTrigger').live('click',function(event){
		event.preventDefault();
		$.ajax({
			url:'data/videoImage/videourl.txt',
			dataType:'html',
			success:function(video){
				
				$('.ol').fadeIn();
				$("#iframeHolder").append(video);
				$("#iframeHolder").css('margin-left',(window.innerWidth/2-330)+"px");
				$("#iframeHolder").css('margin-top',$(window).height()*0.24+"px");
				$("#iframeHolder").show().addClass('animated bounceInUp');
			
				$('.blackCloseIcon').click(function(event){
					event.preventDefault();
					var that = $(this);
					$(this).parent().removeClass('bounceInUp ').addClass('animated bounceOutDown');
					
					if($.browser.msie !== true){
						setTimeout(function(){	
							$(that).parent().find($('iframe')).remove();
							$('.ol').hide();
							$("#iframeHolder").removeClass('bounceOutDown').removeClass('animated');
						},500)
					}
					else{
						$(that).parent().find($('iframe')).remove();
							$('.ol').hide();
							$("#iframeHolder").removeClass('bounceOutDown').removeClass('animated');
					}
				});
			
			}
		});
		
	});
}
