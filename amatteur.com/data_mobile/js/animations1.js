(function(){function e(a,b,c){if(a.addEventListener)a.addEventListener(b,c,!1);else if(a.attachEvent)a.attachEvent("on"+b,function(){c.call(a)});else throw Error("not supported or DOM not loaded");}function k(a,b){for(var c in b)b.hasOwnProperty(c)&&(a.style[c]=b[c])}function l(a,b){RegExp("\\b"+b+"\\b").test(a.className)||(a.className+=" "+b)}function h(a,b){a.className=a.className.replace(RegExp("\\b"+b+"\\b"),"")}function j(a){a.parentNode.removeChild(a)}var q=document.documentElement.getBoundingClientRect?
function(a){var b=a.getBoundingClientRect(),c=a.ownerDocument;a=c.body;var c=c.documentElement,f=c.clientTop||a.clientTop||0,g=c.clientLeft||a.clientLeft||0,d=1;a.getBoundingClientRect&&(d=a.getBoundingClientRect(),d=(d.right-d.left)/a.clientWidth);1<d&&(g=f=0);return{top:b.top/d+(window.pageYOffset||c&&c.scrollTop/d||a.scrollTop/d)-f,left:b.left/d+(window.pageXOffset||c&&c.scrollLeft/d||a.scrollLeft/d)-g}}:function(a){var b=0,c=0;do b+=a.offsetTop||0,c+=a.offsetLeft||0,a=a.offsetParent;while(a);
return{left:c,top:b}},m,n=document.createElement("div");m=function(a){n.innerHTML=a;return n.removeChild(n.firstChild)};var p,r=0;p=function(){return"ValumsAjaxUpload"+r++};window.AjaxUpload=function(a,b){this._settings={action:"upload.php",name:"userfile",multiple:!1,data:{},autoSubmit:!0,responseType:!1,hoverClass:"hover",focusClass:"focus",disabledClass:"disabled",onChange:function(){},onSubmit:function(){},onComplete:function(){}};for(var c in b)b.hasOwnProperty(c)&&(this._settings[c]=b[c]);a.jquery?
a=a[0]:"string"==typeof a&&(/^#.*/.test(a)&&(a=a.slice(1)),a=document.getElementById(a));if(!a||1!==a.nodeType)throw Error("Please make sure that you're passing a valid element");"A"==a.nodeName.toUpperCase()&&e(a,"click",function(a){a&&a.preventDefault?a.preventDefault():window.event&&(window.event.returnValue=!1)});this._button=a;this._input=null;this._disabled=!1;this.enable();this._rerouteClicks()};AjaxUpload.prototype={setData:function(a){this._settings.data=a},disable:function(){l(this._button,
this._settings.disabledClass);this._disabled=!0;var a=this._button.nodeName.toUpperCase();("INPUT"==a||"BUTTON"==a)&&this._button.setAttribute("disabled","disabled");this._input&&this._input.parentNode&&(this._input.parentNode.style.visibility="hidden")},enable:function(){h(this._button,this._settings.disabledClass);this._button.removeAttribute("disabled");this._disabled=!1},_createInput:function(){var a=this,b=document.createElement("input");b.setAttribute("type","file");b.setAttribute("name",this._settings.name);
this._settings.multiple&&b.setAttribute("multiple","multiple");k(b,{position:"absolute",right:0,margin:0,padding:0,fontSize:"480px",fontFamily:"sans-serif",cursor:"pointer"});var c=document.createElement("div");k(c,{display:"block",position:"absolute",overflow:"hidden",margin:0,padding:0,opacity:0,direction:"ltr",zIndex:2147483583});if("0"!==c.style.opacity){if("undefined"==typeof c.filters)throw Error("Opacity not supported by the browser");c.style.filter="alpha(opacity=0)"}e(b,"change",function(){if(b&&
""!==b.value){var c=b.value.replace(/.*(\/|\\)/,"");!1===a._settings.onChange.call(a,c,-1!==c.indexOf(".")?c.replace(/.*[.]/,""):"")?a._clearInput():a._settings.autoSubmit&&a.submit()}});e(b,"mouseover",function(){l(a._button,a._settings.hoverClass)});e(b,"mouseout",function(){h(a._button,a._settings.hoverClass);h(a._button,a._settings.focusClass);b.parentNode&&(b.parentNode.style.visibility="hidden")});e(b,"focus",function(){l(a._button,a._settings.focusClass)});e(b,"blur",function(){h(a._button,
a._settings.focusClass)});c.appendChild(b);document.body.appendChild(c);this._input=b},_clearInput:function(){this._input&&(j(this._input.parentNode),this._input=null,this._createInput(),h(this._button,this._settings.hoverClass),h(this._button,this._settings.focusClass))},_rerouteClicks:function(){var a=this;e(a._button,"mouseover",function(){var b;if(!a._disabled){a._input||a._createInput();var c=a._input.parentNode,f=a._button,g;b=q(f);g=b.left;b=b.top;k(c,{position:"absolute",left:g+"px",top:b+
"px",width:f.offsetWidth+"px",height:f.offsetHeight+"px"});c.style.visibility="visible"}})},_createIframe:function(){var a=p(),b=m('<iframe src="javascript:false;" name="'+a+'" />');b.setAttribute("id",a);b.style.display="none";document.body.appendChild(b);return b},_createForm:function(a){var b=this._settings,c=m('<form method="post" enctype="multipart/form-data"></form>');c.setAttribute("action",b.action);c.setAttribute("target",a.name);c.style.display="none";document.body.appendChild(c);for(var f in b.data)b.data.hasOwnProperty(f)&&
(a=document.createElement("input"),a.setAttribute("type","hidden"),a.setAttribute("name",f),a.setAttribute("value",b.data[f]),c.appendChild(a));return c},_getResponse:function(a,b){var c=!1,f=this,g=this._settings;e(a,"load",function(){if("javascript:'%3Chtml%3E%3C/html%3E';"==a.src||"javascript:'<html></html>';"==a.src)c&&setTimeout(function(){j(a)},0);else{var d=a.contentDocument?a.contentDocument:window.frames[a.id].document;if(!(d.readyState&&"complete"!=d.readyState)&&!(d.body&&"false"==d.body.innerHTML)){var e;
d.XMLDocument?e=d.XMLDocument:d.body?(e=d.body.innerHTML,g.responseType&&"json"==g.responseType.toLowerCase()&&(d.body.firstChild&&"PRE"==d.body.firstChild.nodeName.toUpperCase()&&(d.normalize(),e=d.body.firstChild.firstChild.nodeValue),e=e?eval("("+e+")"):{})):e=d;g.onComplete.call(f,b,e);c=!0;a.src="javascript:'<html></html>';"}}})},submit:function(){var a=this._settings;if(this._input&&""!==this._input.value){var b=this._input.value.replace(/.*(\/|\\)/,"");if(!1===a.onSubmit.call(this,b,-1!==b.indexOf(".")?
b.replace(/.*[.]/,""):""))this._clearInput();else{var a=this._createIframe(),c=this._createForm(a);j(this._input.parentNode);h(this._button,this._settings.hoverClass);h(this._button,this._settings.focusClass);c.appendChild(this._input);c.submit();j(c);j(this._input);this._input=null;this._getResponse(a,b);this._createInput()}}}}})();

function closeUserDropDown(){
	if($("#user_dropdown").hasClass('dropped')){
		$("#user_dropdown").animate({
			'marginTop':"-300px"
		},function(){
			$(this).removeClass('dropped')
		})
	}
}


$(document).ready(function(){

	$(".uploadButtonLink").click(function(event){
		event.preventDefault();
		$("#container .pin").css('display','none');
		ajaxUpload($("#uploadBut"));
	
//		$(".sheet.upload").css('margin-top','-200px').show().animate({
//			marginTop:'300px'
//		},125);
//		
		
		$(".imageUpload").css('min-height',window.innerHeight-31+'px');
		$(".sheet.upload").css('margin-top','-100%').show().css({
			'margin-top':'30px'
		
			
		});
		document.addEventListener('scroll',function(event){
			console.log("scroll");
			event.preventDefault();
		});
		
		
		
		closeUserDropDown();
		
	});
	
	
	$('#cancelUpload').unbind('tap').live('tap',function(event){
		event.preventDefault();
		
		$(".sheet.upload").css('margin-top','0px');
		$(".sheet.upload").css('display','none');
		$("#container .pin").css('display','block');
		$("#uploadedImage").remove();
		
		$("#upload").css({
			'display':'block',
			'padding-top':'40%'
		});
		
		$("#upload a").css({
			'display':'block'
		});
		
	});
	
	
	
	
});


function ajaxUpload(element){
	var upload = new Upload;
	
	new AjaxUpload(element,{
		action: '../addpin/upload_images',
		name:'file',
		autoSubmit: true,
		onSubmit:function(file,ext){
			upload.showLoader();
		},
		onComplete:function(file,response){
			response = $.parseJSON(response);
		
			if(response['error']){
				
				upload.showBrowseButton();
				alert(response['error']);
			}
			if(response['success'] == '1'){
				
				$.ajax({
					url:"../addpin/upload_mobile",
					dataType:"JSON",
					success:function(data){
						console.log(data);
						upload.hideLoader();
						upload.appendImage(data)
						
					},
					error:function(error){
						console.log(error);
					}
				});
				
			}
			
			
		}
	});
	
	
}


var Upload = function(){
	var self = this;
	

	this.showBrowseButton = function(){
		$(".mobLoader").remove();
		$("#uploadBut").parent().show();
	}
	
	this.hideBrowseButton = function(){
		$('#uploadBut').parent().hide();
	}
	
	this.showLoader = function(){
		self.hideBrowseButton();
		$('#uploadBut').parent().parent().append("<div class='mobLoader'></div>");
	}
	
	this.hideLoader = function(){
		$(".mobLoader").remove();
	}
	
	this.appendImage= function(data){
		$("#upload").hide();
		$(".imageUpload").prepend("<div id='uploadedImage' style='width:100%;text-align:center;'>"+data.image+"</div>");
		$("#uploadedImage img").css('width','100%');
		$(".browseImages").css('padding-top','20px');
		$(".browseImages").css('padding-bottom','20px');
		$("#uploadedImage").append("<form method='post' id='postData' action='addpin/fromfile'>" +
					"<select id='board-id' name='board_id'>"+
						"<option value='noEntry'>"+data.phrases.select_board+"</option>"+
						"<option value='cb'>"+data.phrases.create_board+"</option>"+
					"</select>"+
					"<textarea id='description' name='message'></textarea>"+
				"</form>");
		
		$.each(data.boards, function(i,b){
			$("#board-id").append("<option value='"+b.board_id+"'>"+b.title+"</option>");
		});
		
		$("#uploadedImage").append(
				"<div id='doUpload' style='padding-bottom:30px'>" +
					"<a href='' class='submit_comment red mbtn big'>" +
					"<strong>"+data.phrases.upload_button+"</strong>"+
					"<span id='addPinSubmit'></span>" +
					"</a>"+
				"</div>");
		

		$("#board-id").change(function(){
			
			var option = $(this).find($('option:selected'))[0];
			 if($(option).attr('value') === 'cb'){
				$("#board-id").after("<input type='text' id='new-board' name='newboard' class='event-price-textarea' style='width:98% !important'/>");
				
			 }else{
				$("#new-board").remove();
			 }
		});
		
		console.log($("#addPinSubmit"));
		$("#doUpload").click(function(event){
			event.preventDefault();
			//DEFINE FORM
			var form = $(this).parent();
			//DEFINE TEXTEREA
			var textarea = form.find($('textarea'));
			//VALIDATE IF TEXAREA IS EMPTY
			if(!self.validate('notEmpty',textarea)){
				alert(data.phrases.textarea_validation);
				return false;
			}
			if($("#new-board")[0]){
				var title = $(form).find("input[name='newboard']");							
				if(!self.validate('notEmpty',title)){
					alert(data.phrases.board_validation);
					return false;
				}else{
					
					self.addBoard({'title':$(title).val()},function(data){
						console.log(data.board_id);
						if(data.board_id){
							console.log($("#board-id"));
							$("#board-id").append($("<option></option>").attr('value',data.board_id).attr('selected','SELECTED').text(data.title));
							submitForm(form);
						}else if(data.error){
							alert(data.error);
							return false;
						}
					});
				}
			}else{
				
				if($("#board-id option:selected").attr('value') === 'noEntry'){
					alert(data.phrases.notEmptyMsg);
					return false;
				}
				
				submitForm(form);	
			}
			
	
			function submitForm(form){
				$("#postData").submit();
				$(".imageUpload").empty().append("<div class='mobLoader' style='margin-top:300px'></div>");
				console.log($("#postData")[0]);
			
			}
	});
		
	
	
		

	}
	
	
	
	this.addBoard = function(title,callback){
		$.ajax({
			url:'boards/create_mobile',
			dataType:"JSON",
			type:"POST",
			data:title,
			success:function(data){
				callback(data);
			},
			error:function(error){
				console.log(error);
				alert('Ooops! For some reason we could not create a new board. :(')
			}
		});
	}
	
	this.validate = function(type,element){
		switch(type){
			case 'notEmpty':
				if(element.val() === ''){
					return false;
				}else{
					return true;
				}
			
		}
	}
	
}

var searchInt = setInterval(function(){
	if($("#query")){
		
		$("#query").focus(function(){
			$(this).val('');
		})
		
		clearInterval(searchInt);
		
		
	}
})




var outerWrapperInterval = setInterval(function(){
	
	//close all menus when clicking somewhere else in the wrapper
	
	var wrapper = document.getElementById('wrapper');

	
	
	var outerWrapper = document.getElementById('outer_wrapper');

	
	if(outerWrapper){
		if($("#logo_wrapper a h1")){
									$("#logo_wrapper a h1").css('background',"url('"+logoUrl+"')");
									$("#logo_wrapper a h1").css('-webkit-background-size','125px 37px');
									$("#logo_wrapper a h1").css('-moz-background-size','125px 37px');
									$("#logo_wrapper a h1").css('background-size','125px 37px');
									

								
					}
					
					
		

		
		clearInterval(outerWrapperInterval);
		$("#outer_wrapper").live('tap',function(){
		
				if($("#search_dropdown").hasClass('dropped')){
					$("#search_dropdown").animate({
						'marginTop':'-300px'
					},function(){
						$(this).removeClass('dropped');
							$("#categories").animate({
								marginLeft: "0px"
							});
					 	$("#search_dropdown").val('');
					})
					$(".dropdown,.dropdown.dropped").css('max-height','273px');
	  				$("#search_dropdown,.dropdown,.dropdown.dropped").css('max-height','273px');
	  				$("#search_dropdown,.dropdown,.dropdown.dropped").css('height','273px');
					
				
				}
				else{
					$("#search_dropdown").animate({
						'marginTop': '0px'
					},function(){
					$(this).addClass('dropped');
					})
					
					
					if($("#user_dropdown").hasClass('dropped')){
						
						$("#user_dropdown").animate({
							'marginTop':"-300px"
						},function(){
							$(this).removeClass('dropped')
						});
					
					}
					
				}
		});
		
		
	
		
		
		// close menus on scroll
	/*	document.addEventListener('scroll',function(){
			if($("#search_dropdown").css('margin-top') == '0px'){
				$("#search_dropdown").animate({
					"marginTop":"-300px"
				})
			}
			
			if($("#user_dropdown").css('margin-top') == '0px'){
				$("#user_dropdown").animate({
					"marginTop":"-300px"
				})
			}
		})
		
		
		*/
		
	$("#touch_arrow").live("tap",function(){

		$(".dropdown").css('max-height','600px');
		$("#search_dropdown").css('max-height','600px');
		$("#search_dropdown").css('height','600px');
		
			event.preventDefault();
			$("#categories").animate({
				marginLeft: "-235px"
			});
			
			
			
			
			
			
		})
	
	}
	
	
	
	$("#profile_btn").live('tap',function(){
		
		if($("#user_dropdown").hasClass('dropped')){
			$("#user_dropdown").animate({
				'marginTop':"-300px"
			},function(){
				$(this).removeClass('dropped');
			})
		}
		else
		{
			$("#user_dropdown").animate({
				'marginTop':"0px"
			},function(){
				$(this).addClass('dropped');
			})
			
			if($("#search_dropdown").hasClass('dropped')){
				$("#search_dropdown").animate({
					'marginTop':"-300px"
				},function(){
					$(this).removeClass('dropped')
					$("#categories").animate({
								marginLeft: "0px"
					});
				})
			}
		}
	})
	
	


})



$(".comment_btn").live('tap',function(){
		if(document.getElementById("repin")){
			$("#white-dim").hide();
			$("#repin").hide().remove();
			$(".repin_btn").removeClass('disabled');
		}
		
			
		function clearComment(){
		   			$("#white-dim").hide();
		   			$("#comment").hide().remove();
		   			$(that).removeClass('disabled');
		   }	
			
		
		var pinId = $(event.target).parent().parent().parent().attr('data-id');
		var that = $(event.target).parent();
		
		
		//position of the next pinBox related to thew clicked commentButton
		var elPosition = $(that).parent().offset().top;
		//the height of the document
		var docHeight = $(document).height();
		//the top-margin of the dim layer
		var top = parseInt((docHeight-elPosition)-$(that).parent().height());
		
			if($(that).hasClass('disabled')){
				clearComment();
				
			}
			
			else{
					
					$(that).addClass('disabled');
					//scroll the boddy so the comments are on the top of the window
					$("html, body").animate({
						scrollTop: $(that).parent().prev().prev().prev().offset().top
					})
					
					
				
					$("#white-dim").css('margin-top',(elPosition+$(that).parent().height())+"px").css("min-height",'2000px').show();
					if(!$("#comment")){
					
					}
					$(that).parent().append("<div class='sheet' id='comment' style='overflow: visible;z-index:11000;max-widht:640px;'>"+
												"<div class='comment'>"+
													"<textarea class='comment_text'></textarea>"+
													//"<a id='closeCommentButton' class='closeCommentButton' style='float:left;margin-left:-8px'><img  class='closeGrey' src='../data/images/fancy_close1.png'/><img class='closeRed' src='../data/images/fancy_close1.png' style='display:none' /></a>"+
													"<a class='submit_comment red mbtn'>"+
													"<strong>"+commentButtonText+"</strong>"+
													"<span></span>"+
													"</a>"+
												"</div>"+
												"<div class='mobile_arrow'>"+
												"</div>"+
												
											+"</div>");
											
										
					var commInterval = setInterval(function(){
						if($("#comment")){
							clearInterval(commInterval);
						
							
							
							
						}
					})
				
					}
					
					
						
			/*	//tapping on the #white-dim
				$("#white-dim").tappable(function(){
					$("#comment").fadeOut().remove();
					$("#white-dim").css('margin-top','0px').fadeOut();
					$(that).removeClass('disabled');
				})
				
		    */
		   
		   
		  
		   
		   $("#white-dim").scroll(function(){
		   	clearComment();
		   })
		   
		   document.getElementById('white-dim').addEventListener('touchstart',clearComment,false);
		   document.getElementById('white-dim').addEventListener('scroll',clearComment,false);
		
		   console.log(window.location);
		
		   $(".submit_comment").click(function(event){
		  
		   	$(this).addClass('disabled').text(comentando);
		   	event.preventDefault();
		   	
		 	$.ajax({
		   		url: commentPinUrl+"pin/"+pinId,
		   		dataType:"JSON",
		   		type:"POST",
		   		data:{"write_comment":$("#comment .comment .comment_text").val()},
		   		success:function(data){
		   			  
		   			if(data.ok == true){
		   				
		   				clearComment();
		   				if(!(that).parent().parent().parent().find($(".pin_comments").find($(".icon")))){
		   				
		   					$(that).parent().parent().find(".pin_comments .comment_text").append("<p>"+
		   												"<a class='link' href= '"+data.profile_href+"'>"+data.comment.user.fullname+"</a>"+
		   												"<span>"+data.comment.comment+"</span>"+
		   											"</p>")
		   				}
		   				else{
		   					if(window.location.href.search(pinId) == -1){
		   					$(that).parent().parent().find($(".pin_comments")).append("<table>"+
		   								"<tbody>"+
		   									"<tr>"+
		   										"<td class='icon'></td>"+
		   										"<td class='comment_text'>"+
		   											"<p>"+
		   												"<a class='link' href= '"+data.profile_href+"'>"+data.comment.user.fullname+"</a>"+
		   												"<span>"+data.comment.comment+"</span>"+
		   											"</p>"+
		   										"</td>"+
		   									"<tr>"+
		   								"<tbody/>"+
		   							"</table>");
		   					}
		   					else{
		   						
		   					}
		   				}
		   			}
		   		},
		   		error:function(error){
		   			
		   		}		
		   	})
		   })
		   
				


})



$(".like_btn").live('tap',function(event){
			event.preventDefault();
			var that = $(event.target).parent();
					
					var likesText = parseInt($(that).parent().prev().find('.pin_likes span').text());
					var lt = ''; //lt is like text but we check if its present in the DOM or not
					if(!likesText){
						lt = 0;
					}
					else{
						lt = likesText;
					}
					
					if($(that).parent().prev().find($('.icon'))){
						
						if($(that).attr('data-liked') == 0){
							$(this).find('strong').text(genericUnlike)
							$(this).attr('data-liked',1)
							$(this).addClass('pressed');
							$(that).parent().prev().find('.pin_likes .likeIcon').addClass('icon');
							$(that).parent().prev().find('.pin_likes span').html('<img width="15px" height="14px" src="data/images/ico_mg.png" alt="' + genericUnlike + '" /> ' + (lt+1));
						}
						else{
							$(this).find('strong').text(genericLike)
							$(this).attr('data-liked',0)
							$(this).removeClass('pressed');
							$(that).parent().prev().find('.pin_likes span').html('<img width="15px" height="14px" src="data/images/ico_mg.png" alt="' + genericLike + '" /> ' + (lt-1));
								
						}
						
					}
					else{
				
					}	
					
			
								
			event.preventDefault();
			$.ajax({
				url:$(that).attr('href'),
				type:'JSON',
				success:function(data){
				
				},
				error:function(error){
					$(that).find('strong').append("<b style='color:red'> !</b>")
				}		
		
			})
		
			
})

$(".edit_btn").live('tap',function(event){
	$(this).addClass('disabled');
});

$(".repin_btn").live('tap',function(event){
				
				event.preventDefault();
						
			
		if(document.getElementById("comment")){
			$("#white-dim").hide();
			$("#comment").hide().remove();
			$(".comment_btn ").removeClass('disabled');
		}
		



				function clearRepin(){
		  		 	$("#white-dim").hide();
		   			$("#repin").hide().remove();
		   			$(that).removeClass('disabled');
		   }	
			
		
		var pinId = $(this).parent().parent().attr('data-id');
		
		var that = $(event.target).parent();
		//position of the next pinBox related to thew clicked commentButton
		var elPosition = $(that).parent().offset().top;
		//the height of the document
		var docHeight = $(document).height();
		//the top-margin of the dim layer
		var top = parseInt((docHeight-elPosition)-$(that).parent().height());
		
		
			
			
			if($(that).hasClass('disabled')){
				clearRepin();
				
			}
			
			else{
					
					$(that).addClass('disabled');
					
					//scroll the boddy so the comments are on the top of the window
					$("html, body").animate({
						scrollTop: $(that).parent().prev().prev().prev().offset().top
					})
					
					
				
					$("#white-dim").css('margin-top',(elPosition+$(that).parent().height())+"px").css("min-height",'2000px').show();
					
					$(that).addClass('disabled');
					
					var repinCount = parseInt($(that).parent().parent().find(".pin_repins span").text());
					if(repinCount){
						rc = repinCount
					}
					else{
						rc = 0;
					}
					
					$.ajax({
					url:$(event.target).parent().attr('href'),
					dataType:"html",
					success:function(data){
							$(that).parent().append(data);
							//animate the div
			var commInterval = setInterval(function(){
						if($("#repin")){
							clearInterval(commInterval);
						/*	$("#repin").animate({
							"top":($(that).height()/1.2)+"px",
							duration:'100',
							 
							})
							*/
						}
					})
							
										//clear events
					   $("#white-dim").scroll(function(){
					   	clearRepin();
					   })
					   
					   document.getElementById('white-dim').addEventListener('touchstart',clearRepin,false);
					   document.getElementById('white-dim').addEventListener('scroll',clearRepin,false);
					   
			   		  $("#board_id").change(function(){
			   		  	if($("#board_id option:selected").attr('id') == 'create_new_board'){
			   		  		$("#create_board_wrapper").show();
			   		  		$("#createBoardInput").addClass('opened');
			   		  	}
			   		  	else{
			   		  			$("#create_board_wrapper").hide();
			   		  			$("#createBoardInput").removeClass('opened');
			   		  	}
			   		  })
			   		
			   		  //SENDING POST TO REPIN CONTROLLER
			   		  $("#submitRepin").live('tap',function(){
			   			if (!$(this).hasClass('disabled'))
						{
							$(this).addClass('disabled').text(guardando);
						 
							function submit(boardId){
								$.ajax({
									url:$(that).attr('href'),
									type:"POST",
									dataType:"text",
									data:{'message':$("#message").text(),'board_id':boardId},
									success:function(d){
									  
										$(that).parent().parent().find(".pin_repins .repinIcon").addClass('icon');
										$(that).parent().parent().find(".pin_repins span").text(rc+1);
											clearRepin();
										
										
									},
									error:function(error){
									
									}
								})
							}
						
						  
								if($("#createBoardInput").hasClass('opened')){
									
										var boardName = $("#createBoardInput").val();
										
										if(boardName == ''){
										alert($("#createBoardInput").data('msg'))
										
										}
									else{
											$.ajax({
												url:$("#createBoardInput").data('cb'),
												type:"POST",
												dataType:"JSON",
												data:{"newboard":boardName},
												success:function(d){
													
													submit(d.data.board_id);
												},
												error:function(error){
												
												}
											})				  				
										}
								}
								
								else{
									submit($("#board_id option:selected").val());					  			
								}
						}
								
						  })
					}
				})		
					
			}
					


})


$(document).ready(function(){
		
		
	
		//reduce height of body in user profile!!!!!!!!!!!!!!!
		//document.getElementsByTagName('body').style.height='';
		//console.log(document.getElementsByTagName('body'));
	
		
		$.each($(".notLoadedImg"),function(){
			var img = new Image();
			img.src = $(this).data('src');
			
			var that = this;
			img.onload = function(){
				
				$(that).attr('src',img.src);
				$(that).removeClass('notLoadedImg');
			}
		})
		
		$("#categories").live('swiperight',function(){
			$(".dropdown,.dropdown.dropped").css('max-height','273px');
			$("#search_dropdown,.dropdown,.dropdown.dropped").css('max-height','273px');
			$("#search_dropdown,.dropdown,.dropdown.dropped").css('height','273px');
			$("#categories").animate({
				"marginLeft":"235px"
			});
			
		})
	
			
				
		//LIKE UNLIKE PIN
		

			
		
		
			
		//FOLOW UNFLOW BUTTON
				
		document.addEventListener('touchstart',function(e){
		
			var target = $(e.target).parent();
			
			if($(target).attr('id') == 'follow') { submitQuery(target,'follow') };
			$(target).attr('id') == 'unfollow' ? submitQuery(target,'unfollow') : null;
		
		})
		
		
		function clearLoader(target){
			$("#loader").remove();
			target.parent().removeClass('loading');
		}
		
		function follow(target){
			clearLoader(target);
			target.hide();
			target.parent().find($("a#follow")).show();
			
		}
		
		function unfollow(target){
			clearLoader(target);
			target.hide();
			target.parent().find($("a#unfollow")).show();
		}
		
		function submitQuery(target,type){
			
		if(	!target.parent().hasClass('loading')){
				
			target.parent().append("<img id='loader' src='/data/images/loading_2.gif'/ style='float:right;height:10px;width:10px'>").addClass('loading');
			$.ajax({
				url:target.attr('data-link'),
				dataType:'json',
				success:function(data){
				
					data.ok == 'Follow' ? follow(target) : unfollow(target)
				}
			})
		   
		   }
			
  		}
		
		})
		


	
  		
  		$('#white-dim').live('tap',function(event){
  		  event.preventDefault();
  		
  		})
  		
  		$('#white-dim').live('swipe',function(event){
  		  event.preventDefault();
  		
  		})
  		
  		$("#container").live('tap',function(event){
  			if($(".dropped")){
  				$(".dropped").animate({
  					"marginTop" : "-300px"
  				});
  				
  			}
  			
  			
  		})
  		
  	/*	$(document).scroll(function(){
  			if($(".dropped")){
  				$(".dropped").animate({
  					"marginTop" : "-300px"
  				})
  			}
  		})
  	*/	
  	
  		
	

			

