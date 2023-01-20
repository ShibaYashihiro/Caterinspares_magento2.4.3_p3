require(['jquery'],function($){
	$(".partfinder-container form.partfinder-form").each(function(){
		var load_first = true;
		var form = $(this);
		form.on("submit",function(){
			var url = $(this).attr("action")+"?";
			var select = "";
			var query = {findpart:""}
			form.find("select").each(function(){				
				if($(this).val() != "" && $(this).val() != 0)
				{
					select = $(this);				
					query.findpart += select.find("option:selected").text().toLowerCase()+"-";
				}
			})
			query.findpart += select.val();
			url += $.param( query, true );;
			
			select.attr("action",url);
			window.location = url;
			return false;
		})
		
		form.find("select").on("change",function(){
			var curr_sel = $(this)
			var li = curr_sel.closest("li")
			var prevli = li.prev();
			var nextli = li.next();
			
			var parent_values = '';
			var curr_id = $(this).data('lavel');
			curr_id = curr_id+1;
			while($(prevli).length > 0){
				curr_id = curr_id-1;
				parent_values += '/p'+curr_id+'/'+$(prevli).find('.finder-option-dropdown select').children("option:selected").text();
				prevli = $(prevli).prev();
			}
			var url = $(this).data("option-url")+"parent_id/"+curr_sel.val()+"/value/"+curr_sel.children("option:selected").text()+"/lavel/"+($(this).data("lavel")+1)+parent_values;
			if(!curr_sel.hasClass("last-option") && curr_sel.val() )
			{
				nextli.addClass("loading");
			
				$.ajax({
					url:url,
					type:"GET",
					success:function(data){				
						
						nextli.find("select").html(data);
						nextli.find("select").change();
						
						nextli.nextAll().each(function(){
							var select = $(this).find("select")
							select.html(curr_sel.find("option").first().clone())							
						})						
						
						nextli.removeClass("loading");
					}
				})
			}
			else
			{
				if(partfinder_config.auto_search_on_last_opt_select)
				{
					var findpart = getUrlParameter('findpart');
					var tmp_flag = true;
					
					form.find("select").each(function(){
						if(!$(this).val()) tmp_flag = false;						
					})
					
					if(tmp_flag && typeof findpart == 'undefined')
					{
						form.submit();						
					}
					else if(typeof findpart != 'undefined'){
						$('.last-option').on('change', function() {
							if($('.last-option')[0].value != ''){
								form.submit();
							}
						});
					}

				}
			}
			resetSubmitButton();
		})
		function getUrlParameter(sParam) {
		    var sPageURL = window.location.search.substring(1),
		        sURLVariables = sPageURL.split('&'),
		        sParameterName,
		        i;

		    for (i = 0; i < sURLVariables.length; i++) {
		        sParameterName = sURLVariables[i].split('=');

		        if (sParameterName[0] === sParam) {
		            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
		        }
		    }
		}
		function resetSubmitButton()
		{

			if(partfinder_config.enable_btn_on_any_one)
			{
				
				var count=0;	
				form.find("select").each(function(){
					if($(this).val() == "" || $(this).val() == 0){
						count++;
					}
					// if($(this).val())
					// {						
					// 	form.find(".go-action").removeAttr("disabled");
					// 	return true;
					// }
				})
				if(count == 3){
					form.find(".go-action").attr("disabled",'disabled');
					return true;
				}
				else{
					form.find(".go-action").removeAttr("disabled");
					return true;
				}
			}
			else
			{
				form.find(".go-action").removeAttr("disabled");
				form.find("select").each(function(){
					if($(this).val() == "" || $(this).val() == 0)
					{
						form.find(".go-action").attr("disabled",'disabled');
						return true;
					}
				})
			}
		}
		
		var first_select = form.find("select").first();
		var url = first_select.data("option-url") + "parent_id/"+0+"/lavel/0";
		first_select.closest("li").addClass("loading");
		first_select.load(url,function(){
			first_select.change();
			first_select.closest("li").removeClass("loading");
		});
	})
})