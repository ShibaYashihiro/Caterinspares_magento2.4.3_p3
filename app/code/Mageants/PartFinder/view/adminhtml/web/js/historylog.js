require(['jquery','Magento_Ui/js/modal/modal'],function($,modal){
	var options = {
		type: 'popup',
		responsive: true,
		innerScroll: true,
		title: 'History Error Log'		
	};
	var loading = log_loding;
	var last_view_log_rand = "";
	var popup = modal(options, $('#historyLogModel'));
	
	$(".admin__grid-massaction-form select").removeClass("required-entry");
	
	setInterval(function(){
		if(window.view_log_rand != last_view_log_rand)
		{
			last_view_log_rand = window.view_log_rand;
			
			$(".view-log").on("click",function(){		
				var url = $(this).attr("href")
				popup.openModal();
				$(".modal-content #historyloggrid").html(loading)
				$(".modal-content #historyloggrid").load(url);				
				return false;
			})
		}
	},1000)
	
})