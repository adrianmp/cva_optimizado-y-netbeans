$(document).ready(function()
{		
	var brand = $("#brand").val();
	$.post("datos.php", { brand: brand }, function(data) 
	{				
		  $('#tabla').empty();
		  $('#tabla').append(data);
		  $("a#v3").fancybox
		  ({
				'width'				: '50%',
				'height'			: '130%',
				'autoScale'			: true,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe',
				'onClosed': function() 
				{ 
				   parent.location.reload(false); 
				} 
		   }); 
	});
	$("#brand").change(function() {
		var brand = $("#brand").val();
		if(brand =="")
			$("#tabla").html("");
		else
		{
			$.post("datos.php", { brand: brand }, function(data) {
			  $('#tabla').empty();			
			  $('#tabla').append(data);
			  $("a#v3").fancybox({
				'width'				: '80%',
				'height'			: '130%',
				'autoScale'			: true,
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'type'				: 'iframe',
				'onClosed': function() 
				{ 
				   parent.location.reload(false); 
				} 
				});
			});
		}		
	});
});
