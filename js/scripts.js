(function($)
{
	$.ajaxSetup(
	{
		cache 	 : false,
		dataType : 'json'
	});

	$.extend(Motionmill,
	{
		initialize : function()
		{
			
		}
	});

	Motionmill.initialize();

	$(document).ready(function()
	{
		$('.hide-if-js').hide();
		$('.hide-if-no-js').show();
			
		Motionmill.initialize();
	});

})(jQuery);