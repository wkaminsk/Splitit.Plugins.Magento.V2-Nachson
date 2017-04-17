var url = window.location.hostname;
var http = window.location.protocol;

url = http+"//"+url+"/";
// for local development
//url = url+"magento2newdeploy/";

var jqueryInterval = setInterval(function(){  
    
    if(window.jQuery){
      clearInterval(jqueryInterval);      
      console.log('jQuery found!!');   
      runMyScripts(); 
     }else{
      console.log('jQuery not found!!');
     }       
  }, 1000);

function runMyScripts(){
	jQuery.ajax({
		url: url + "splititpaymentmethod/showinstallmentprice/getinstallmentprice", 
		success: function(result1){
			var result = jQuery.parseJSON(result1); 
			var numOfInstallmentForDisplay = result.numOfInstallmentForDisplay;
			
			if(result.isActive){
				var priceSpan = "";
				var productprice = "";
				var installments = 0;
				var currencySymbol = "";
				var installmentNewSpan = "";
				var displayInstallmentPriceOnPage = result.displayInstallmentPriceOnPage;
				// for category page only
				if(jQuery('.product-items').length && displayInstallmentPriceOnPage.indexOf("category") >= 0){
					jQuery(".product-items li").each(function(){
						priceSpan = jQuery(this).find(".price");
						productprice = jQuery(priceSpan).text();
						currencySymbol = productprice.charAt(0);
						productprice = Number(productprice.replace(/[^0-9\.]+/g,""));
						installments = (productprice/result.numOfInstallmentForDisplay).toFixed(2);
						installmentNewSpan = '<br><span class="cart-installment">'+currencySymbol+installments+' x '+result.numOfInstallmentForDisplay+' '+result.installmetPriceText+'</span>';
						jQuery(priceSpan).after(installmentNewSpan);
						
					});	
				}
				// for product detail page
				if(jQuery('.product-info-price').length && displayInstallmentPriceOnPage.indexOf("product") >= 0){
					priceSpan = jQuery(".product-info-price").find(".price");
					productprice = jQuery(priceSpan).text();
					currencySymbol = productprice.charAt(0);
					productprice = Number(productprice.replace(/[^0-9\.]+/g,""));
					installments = (productprice/result.numOfInstallmentForDisplay).toFixed(2);
					installmentNewSpan = '<br><span class="cart-installment">'+currencySymbol+installments+' x '+result.numOfInstallmentForDisplay+' '+result.installmetPriceText+'</span>';
					jQuery('.product-info-price').after(installmentNewSpan);

				}
				// for cart page only
				if(jQuery('table.totals').length && displayInstallmentPriceOnPage.indexOf("cart") >= 0){
					
					productprice = result.grandTotal;
					currencySymbol = result.currencySymbol;
					productprice = Number(productprice.replace(/[^0-9\.]+/g,""));
					installments = (productprice/result.numOfInstallmentForDisplay).toFixed(2);
					installmentNewSpan = '<br><span class="cart-installment">'+currencySymbol+installments+' x '+result.numOfInstallmentForDisplay+' '+result.installmetPriceText+'</span>';
					jQuery('table.totals tr:last').after('<tr><td>'+installmentNewSpan+'</td></tr>');
					

				}
				// onepage checkout only
				if(jQuery("div.iwd-grand-total-item").length && displayInstallmentPriceOnPage.indexOf("checkout") >= 0){
					productprice = result.grandTotal;
					currencySymbol = result.currencySymbol;
					productprice = Number(productprice.replace(/[^0-9\.]+/g,""));
					installments = (productprice/result.numOfInstallmentForDisplay).toFixed(2);
					installmentNewSpan = '<br><span class="cart-installment-onepage">'+currencySymbol+installments+' x '+result.numOfInstallmentForDisplay+' '+result.installmetPriceText+'</span>';
					jQuery('div.iwd-grand-total-item').after(installmentNewSpan);
					
				}
				
			}	
				
		}
	});

	// find if url has #payment
	
    
    if((window.location.href).indexOf("checkout") >= 0 && (window.location.href).indexOf("checkout/cart") < 0){
    	var hashInterval = setInterval(function(){  
    		if(jQuery("table.table-totals").length){
    			clearInterval(hashInterval);      
			    console.log('# payment found!!');   
			    runMyScriptForCheckout(); 		
    		}else{
    			console.log('else interval # payment not found!!');   
    		}
	      
	      }, 3000);
	     }else{
	      console.log('# payment not found!!');
	     }       
	  
}

function runMyScriptForCheckout(){
	jQuery.ajax({
		url: url + "splititpaymentmethod/showinstallmentprice/getinstallmentprice", 
		success: function(result1){
			var result = jQuery.parseJSON(result1); 
			var numOfInstallmentForDisplay = result.numOfInstallmentForDisplay;
			
			if(result.isActive){
				var priceSpan = "";
				var productprice = "";
				var installments = 0;
				var currencySymbol = "";
				var installmentNewSpan = "";
				var displayInstallmentPriceOnPage = result.displayInstallmentPriceOnPage;
				
				// onepage checkout only
				if(jQuery("table.table-totals").length && displayInstallmentPriceOnPage.indexOf("checkout") >= 0){
					productprice = result.grandTotal;
					currencySymbol = result.currencySymbol;
					productprice = Number(productprice.replace(/[^0-9\.]+/g,""));
					installments = (productprice/result.numOfInstallmentForDisplay).toFixed(2);
					installmentNewSpan = '<br><span class="cart-installment-onepage">'+currencySymbol+installments+' x '+result.numOfInstallmentForDisplay+' '+result.installmetPriceText+'</span>';
					jQuery('table.table-totals tr:last').after('<tr><td>'+installmentNewSpan+'</td></tr>');
					
				}
				
			}	
				
		}
	});
}