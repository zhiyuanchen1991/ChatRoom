$(function () {
	select_client_id = 'all';
	$("#client_list").change(function(){
		select_client_id = $("#client_list option:selected").attr("value");
	});

	$('.face').click(function(event){
		$(this).sinaEmotion();
		event.stopPropagation();
	});
});