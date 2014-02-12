(function($)
{
	$.extend(Motionmill,
	{
		initialize : function()
		{
			$(document).ready(function(){ Motionmill._onDocumentReady(); });
		},

		_onDocumentReady : function()
		{
			$('.hide-if-js').hide();
			$('.hide-if-no-js').show();
		}
	});

	Motionmill.initialize();

})(jQuery);