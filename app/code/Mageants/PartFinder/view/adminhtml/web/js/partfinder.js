require(['jquery','Magento_Ui/js/modal/modal'],function($,modal){
	
	
	var options = {
		type: 'popup',
		responsive: true,
		innerScroll: true,
		buttons: [{
			text: $.mage.__('OK'),
			class: 'success_importfile_delete',
			click: function () {
				this.closeModal();
			}
		}]
	};
	
	var popup_html = $('<div class="finder-popup"/>').html('<div id="content"></div>')
	var last_import_rand = "";
	
	$(".admin__grid-massaction-form select").removeClass("required-entry");
	
	setInterval(function(){
		if(window.import_rand != last_import_rand)
		{
			last_import_rand = window.import_rand;
			
			$(".partfinder_file_delete").on("click",function(){
				var $this = $(this);
				var url = $(this).attr("href");
				var confirm_text = $(this).data('confirm-text');
				if(confirm(confirm_text))
				{
					$.ajax({
						url:url,
						dataType: "json",
						showLoader: true,
						type:"get",
						success:function(data){
							if(data.success)
							{
								options.title = "Success";
								jQuery("#importFilesGrid .action-reset").click();
							}
							else{
								options.title = "Error"
							}
							
							var popup = modal(options, popup_html);
							popup_html.find("#content").html(data.message)
							popup_html.modal('openModal');
							$(".success_importfile_delete").on("click",function(){
								$(".admin__grid-massaction-form select").removeClass("required-entry");
							});
						}
					})
				}
				return false;
			})
			
			$(".partfinder_file_import").on("click",function(){
				var $this = $(this);
				var url = $(this).attr("href") +"/delete_existing_data/"+$("#imports_import_delete_existing").val();
				if($("#imports_import_delete_existing").val() == 1)
				{
					if(!confirm("Are you sure to delete all old data and import new data")) return false;
				}
				$.ajax({
					url:url,
					dataType: "json",
					showLoader: true,
					type:"get",
					success:function(data){
						if(data.success)
						{
							options.title = "Success";
							
						}
						else{
							options.title = "Error"
						}
						jQuery("#importFilesGrid .action-reset").click();
						jQuery("#historyGrid .action-reset").click();
						jQuery("#productsGrid .action-reset").click();
						var popup = modal(options, popup_html);
						popup_html.find("#content").html(data.message)
						popup_html.modal('openModal');
						$(".success_importfile_delete").on("click",function(){
							$(".admin__grid-massaction-form select").removeClass("required-entry");
						});
					}
				})
				return false;
			})
			
		}
	},1000)
})