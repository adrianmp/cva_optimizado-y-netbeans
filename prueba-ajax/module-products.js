var link = document.location.href;
var linkparts = link.split("/");
var link_module_name = linkparts[linkparts.length-1];

$(function(){
	$('p a.load').click(function(e) {
		e.preventDefault();
		// Current selection
		$('a.load').removeClass("view");
		var module_name = $(this).attr('title');
		$('a.load:contains("' + module_name + '")').addClass("view");
		if (module_name == "Productos") {
        	$("#linkblock").show();
		}
		else
			$("#linkblock").hide();
		
		// end current selection
		$("#load_content").html("<p><img src='images/indicator_snake.gif' width='16' height='16' align='absmiddle' /></p>");
		$("#load_content").load($(this).attr('href'),
			function() {
				product_actions();				
				return false;
			});
	});	
	var delete_href;
	// Confirm Product Deletion
	$("#delete-confirm").dialog({
		autoOpen: false,
		resizable: false,
		//height:140,
		modal: true,
		buttons: {
			"Cancelar": function() {
				$(this).dialog('close');
			},
			'Eliminar': function() {				
				//alert("Data Loaded: " + delete_href);
				var elements_href = delete_href.replace("#", "");
				var elements_href_broken = elements_href.split("-");
				var action = elements_href_broken[0];
				var what = elements_href_broken[1];
				var id = elements_href_broken[2];
				$.post("delete.php", { id: id, action: action, what: what },
					function(data){
						if(data=='yes') {
							row = "tr#" + what + id;
							$(row).css({"background-color":"#ccc"});
							$(row).css({"color":"white"});
							setTimeout(function(){ $(row).fadeOut(500).fadeIn(500).fadeOut(300);}, 500);							
							return false;
						}
						/*
						else {					
						}
						*/		
					//alert("Data Loaded: " + data);
				});
				$("#delete-confirm").dialog('close');
			}
		}
	});	

	// Dialog		
	$('#dialog').dialog({
		autoOpen: false,
		width: 800,
		close: function(event, ui) {
				$("#dialog").html('<img src="images/ajax-loader.gif" width="16" height="11" />');
		},
		modal: true,
		position: 'top',
		draggable: true,
		buttons: {
			Cerrar: function() {
				$(this).dialog('close');
			}
		}
		//resizable: true,
		//show: 'slide',
		/*
		buttons: {
			"Download": function() {
				$("#dialog").load($("#download").attr('href'));				
			}, 
			"Exit": function() { 
				$(this).dialog("close"); 
			}
		} */
	});

	function load(num) {
		$('#load_content').load(num,
		function() {
			product_actions();			 
			$('li a.load').removeClass("selected");
			$("li a.load[href='" + num + "']").addClass("selected");
			return false;
		});
	}

	$.history.init(function(url) {
			if (url == "") {
				var link = document.location.href;
				var linkparts = link.split("/"); //alert(linkparts[linkparts.length-1]);
				load("products-list.php?product_status=" + (linkparts[linkparts.length-1] == "trash" ? 0 : 1) + "&module=" + linkparts[linkparts.length-1]);
			}
			else {
				load(url);
				$('a.load:contains("Productos")').addClass("view");
			}
		});

	$('li a.load').live('click', function(e) {
			var url = $(this).attr('href');
			url = url.replace(/^.*#/, '');
			var category_name = $(this).attr('title');
			$.history.load(url);
			return false;
		});
});

// Trigered Product Actions
function product_actions() {
   $("a#controlbtn").click(function(e) {      
        e.preventDefault();        
        var slidepx=$("div#linkblock").width() + 10;    	
    	if ( !$("div#maincontent").is(':animated') ) {
			if (parseInt($("div#maincontent").css('marginLeft'), 10) < slidepx) {
     			$(this).removeClass('close').html('&lt; Ocultar Categor&iacute;as');
      			margin = "+=" + slidepx;				
    		} else {
     			$(this).addClass('close').html('&gt; Mostrar Categor&iacute;as');
      			margin = "-=" + slidepx;
    		}
        	$("div#maincontent").animate({ 
        		marginLeft: margin
      		}, {
                    duration: 'slow',
                    easing: 'easeOutQuint'
            });
    	}
    });
	
	$('a.delete').click(function(e){
		e.preventDefault();
		var full_href = $(this).attr('href');
		var elements_href = full_href.replace("#", "");
		var elements_href_broken = elements_href.split("-");
		var action = elements_href_broken[0];
		var what = elements_href_broken[1];
		var id = elements_href_broken[2];
		$.post("delete.php", { id: id, action: action, what: what },
			function(data){
				if(data=='yes') {
					row = "tr#" + what + id;
					$(row).css({"background-color":"#ccc"});
					$(row).css({"color":"white"});
					setTimeout(function(){ $(row).fadeOut(500).fadeIn(500).fadeOut(300);}, 500);						
					return false;
				}
				/*
				else {					
				}
				*/		
			//alert("Data Loaded: " + data);
		});
	});
	
	// Update
	$('a.update').click(function(e){
		e.preventDefault();
		$("#load_content").load($(this).attr('href'),
			function() {
				product_actions();
				return false;
			});
	});
	// Dialog Link
	$('a.dialog_link').click(function(e){
		e.preventDefault();
		$("#dialog").load($(this).attr('href'));
		$("#dialog").dialog('option', 'title', $(this).attr('title'));
		$("#dialog").dialog('open');
		return false;
	});
	$('#live_filter').liveFilter('table');
	if (link_module_name != "trash")
	$("#minimalist").tablesorter({	headers: { 1: {sorter: false }, 2: {sorter: false }, 6: {sorter: false }}, sortList: [[0,3]], widgets: ['zebra']});
}

// Featured Products
function featured_product(id){	
	var status_checked;	// if 
	status_checked = (($('input[id=product-' + id + ']').is(':checked') )) ? 1 : 0;
	// window.alert("STATUS OF " + id + " IS " + status_checked);
	
	$.post("featured-product.php", { status: status_checked, id: id },
		function(data){
			if(data != 'yes') {
				if  (status_checked)
					$('input[id=product-' + id + ']').attr('checked', false);
				else
					$('input[id=product-' + id + ']').attr('checked', true);
				return false;
			}
		//alert("Data Loaded: " + data);				
	});	
	
	/*
	function show_checked() {
		window.alert($('input[name=foo]').is(':checked'));
	}
	function set_checked(checked) {
		$('input[name=foo]').attr('checked', checked);
	}*/
};
