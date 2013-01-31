function addRelatedProduct() {	
	var id = $('#select-related :selected').val();
	if ( $("#rp-" + id ).length ) {
		alert ('Este producto ya esta como relacionado!');
		return;
	}
	else {
		$('#related .stack').append('<div id="rp-'+id+'" class="rp-row">+ <input size="70" value="' + $('#select-related :selected').val() + '" type="hidden" name="related_products[]" /> ' + $('#select-related :selected').text() + ' <a onclick="removeRelatedProduct(\''+id+'\')" >Remover</a></div>');
		$("span.nrelated").html( $(".rp-row").length );
		$('#select-related :selected').attr('disabled', 'disabled');
	}
	if ($(".rp-row").length > 12)
		alert ('Demasiados relacionados! Elimine algunos agotados o incoherentes.');
}
function showRelated() {
	$("#related_category_products").load("products-list.php?category_id=" + $("#related_category").val() + "&product_status=1&module=edit-product&product_id=" + $("#product_id").val() , function() {
		$('.add-related').click(function(e) {
			e.preventDefault();
			addRelatedProduct();
		});
	});
}
function showAttributes() {
	$("#load-attributes").html('<img src="loader.gif" width="12" height="12" />');
	$("#load-attributes").load("load-attributes.php?category=" + $("#category").val(), function(){$(this).show();});
	
}
function removeRelatedProduct(id) {
	$( '#rp-' +id ).remove();
	$("span.nrelated").html( $(".rp-row").length );
	if ( $('#select-related option[value=' + id + ']').length )
		$('#select-related option[value=' + id + ']').removeAttr("disabled");
}

function price(change)
{
	var amount= $("#"+change).val();
	$.post("currency-converter.php", 
	{
		amount:  amount,
		from: "USD",
		to: "MXN"
	}, 
		function(data){
				$("#"+change).val(data);					
			});
		
}

$(document).ready(function() {
	$('#info').hide();	
	$('#related').hide();

	$("form").submit(function() {		
		$.post("agregarajax.php", $("#form").serialize(), 
		function(data){
				if(data==1)
					parent.$.fancybox.close();
				else
					$('#errors').fadeOut(1400).html("<h2>"+data+"</h2>"+"<br/>");
						setTimeout(function(){ $('#errors').fadeIn(1400).fadeOut(1400);}, 1000);
			});
				
		return false;
	});
	
	$("#cancel").click(function() { parent.$.fancybox.close(); });	
	
	if ($("#moneda").val() =="Dolares")
	{
		if($("#price_buy_mx").val()!="")
		{
			price("price_buy_mx");
			if($("#featured").is(":checked"))
			{
				//price("price_buy_mx");
				price("price_buy_mx_offer");
			}
			$("#moneda").val("Pesos");
		}
	}
	
	
			
	$("input[name='product_status']").click(function(){
		if ($("input[name='product_status']").is(":checked"))
			$('.productstatus').css({'border-bottom-color': '#679902'});
		else{
			$('.productstatus').css({'border-bottom-color': 'red'});
			if ( $("#stock").val() > 0 ) alert("Ha marcado como NO existente este producto y su Stock tiene valor de " + $("#stock").val() + ". Para mantener la coherencia entre el estado del producto y su Stock: deje Stock en blanco o en su caso, marque el producto como existente.");
		}
	});
	
	// Activate Datepicker
	$(".datepicker").datepicker({
		minDate: 0,
		yearRange: '-1:+1',
		changeMonth: true,
		changeYear: true
	});	
	
	// Validate Skus & Part Number
	$('.isrepeated').blur(function() {
			// alert ("#"+$(this).attr("id"));
			var w = $(this).attr("id");
			var v = $(this).val();
			if (vacio(v) == false )
				return false;
			if ($("img." + w).length){ $("img." + w).hide(); }
			$.post("isrepeated.php", { w: w, v: v },
				function(data){
					if(data == "TRUE")
						$("input#" + w).after(' <img class="'+ w +'" src="stop.png" alt="Producto Repetido" width="12" height="12" /> ');
					else
						$("input#" + w).after(' <img class="'+ w +'" src="accept.png" alt="Producto no Existe" width="12" height="12" /> ');
			});
			return false;
	});
	
	// Show/Hide CheckButton for Featured End
	$(".extra").css("display","none");
	$("#featured").click(function(){
	if ($("#featured").is(":checked"))
		{	$(".extra").css("display","block");
			$('input#price_buy_mx_offer').focus();
		}
	else
		{	$(".extra").css("display","none");			
		}
	});
	
	// Calculate the price
	$('#price_mx').focus(function() {
		if( vacio($("#stock").val()) == false )
			$("#stock").val(0);
		if ( $("#stock").val() > 0 ) return false;
		if ( $('input[name=featured]').is(':checked') && vacio($('#price_buy_mx_offer').val()) == false && $("#stock").val() == 0) {
			$('#price_buy_mx_offer').focus();
			return false;
		}
		if( $("#pricechanged").val() == "TRUE" ) {
			$("#price_buy_mx").val($.trim($("#price_buy_mx").val()));
			$("#price_buy_mx_offer").val($.trim($("#price_buy_mx_offer").val()));
			$.post("load-price.php", $("#form").serialize(),
				function(data){
					// $('#test').html(data);
					if( vacio(data) == true && data != $("#price_mx").val()) {
						$("#price_mx").val(data);
						$('#updated').fadeOut(1200).html("Calculado!");
						setTimeout(function(){ $('#updated').fadeIn(1200).fadeOut(1200);}, 500);
					}
			});
			return false;
		}
	});
	
	
	// choose text for the show/hide link
	var showText = "Mostrar Relacionados";
	var hideText = "Ocultar Relacionados";
	// create the toggle link
	$("#related").before("<a href='#' class='toggle' id='toggle_link'>"+showText+"</a> (<span class='nrelated'></span>)<br />");
	$("span.nrelated").html( $(".rp-row").length );	
	// hide the content
	$('#related').hide();
	// capture clicks on the newly created link
	$('a#toggle_link').click(function() {
		// change the link text
		if ($('a#toggle_link').text()==showText) {
			$('a#toggle_link').text(hideText);
			$('a#toggle_link').addClass("minusbg");
		}
		else {
			$('a#toggle_link').text(showText);
			$('a#toggle_link').removeClass("minusbg");
		}
		// toggle the display
		$('#related').toggle('slow');
		// return false so any link destination is not followed
		return false;
	});
	
	$("#category").change(function () {	
		$("#related_category").val($(this).val()).attr("selected", "selected");
		showRelated();
		showAttributes();
	});
	
	$("#related_category").change(function () {	
		showRelated();
	});
	
});

function productSlug() {
	if ($("#name").val() != '' && $("#slug").val() == '') {
		var elements = $("#name").val().split(",");
		var slug = elements[0].replace(/[^a-zA-Z0-9]+/g, "-").toLowerCase();
		slug = slug.replace('-de-','-');
		slug = slug.replace('-para-','-');		
		if ($("#pn").val() != ''){
			var pn = $("#pn").val().replace(/[^a-zA-Z0-9]+/g, "-").toLowerCase();
			slug = slug + "-" + pn;
		}
		$("#slug").val(slug);
	}
}
setupTinyMCE();


//busca caracteres que no sean espacio en blanco en una cadena  
function vacio(q) {  
        for ( i = 0; i < q.length; i++ ) {  
                if ( q.charAt(i) != " " ) {  
                        return true  
                }  
        }  
        return false  
}  
  
//valida que el campo no este vacio y no tenga solo espacios en blanco  
function valida(F) {          
        if( vacio(F.campo.value) == false ) {  
                alert("Introduzca correctamente un valor para Stock.")
                return false  
        } /* else {  
                alert("OK")  
                //cambiar la linea siguiente por return true para que ejecute la accion del formulario  
                return false  
        } */  
}
