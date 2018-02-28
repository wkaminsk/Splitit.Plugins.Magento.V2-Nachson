var baseCurrencyCode1 = "";
var baseCurrencySymbol1 = "";
var currencyCodeSymbol1 = "";
var jqueryIsHere1 = 0;
var curUrl1 = window.location.href; 
var baseUrl1 = "";
baseUrl1 = curUrl1.substring(0, curUrl1.indexOf('admin'));

var jqueryInterval1 = setInterval(function(){  
    var dependingOnCart = document.getElementById('payment_us_splitit_paymentredirect_depending_on_cart_total');  
    if(dependingOnCart){
      jqueryIsHere1 = 1;
      clearInterval(jqueryInterval1);      
      runMyScripts1();     
     }else{
      console.log('Element not found!!');
     }       
  }, 1000);

function runMyScripts1(){

  // run on page load.  
  getCurrency1();
  

  jQuery(document).on("click", "#save", function(event) {
//alert("paymentForm");
      var flag1P = 0;
      var flag2P = 0;
      var percentageFlagP = 0;
      var overlapsP = 0;
      var fromBiggerP = 0;
      var hasGapP = 0;
    
      // validation for depanding on cart
      // $(this).unbind('click');
      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();
      //alert('Please fill the required fields in Splitit section "Depending on cart total"');
      jQuery("#payment_us_splitit_paymentredirect_percentage_of_order").css("border","1px solid #ccc");
      if(jQuery('#payment_us_splitit_paymentredirect_select_installment_setup').val() == 'depending_on_cart_total'){
        var fromToArrP = {};
        var iP=0;
        jQuery("#tier_price_containerP tr").each(function(){
        
        
        var doctv_fromP = parseFloat(jQuery(this).find(".doctv_from").val());
        var doctv_toP = parseFloat(jQuery(this).find(".doctv_to").val());
        var doctv_currencyP = jQuery(this).find(".doctv_currency").val();

        jQuery(this).find(".doctv_from").css("border","1px solid #ccc");
        jQuery(this).find(".doctv_to").css("border","1px solid #ccc");
        jQuery(this).find("select.doctv_installments").css("border","1px solid #ccc");
        // validation for installments
        var installmentsCountP = jQuery(this).find("select.doctv_installments  :selected").length;
        if(installmentsCountP == 0){
          jQuery(this).find("select.doctv_installments").css("border-color","red");
          flag1P++;
           //return false;
        }
        // validation for from and to amount
        if((doctv_fromP == "" || isNaN(doctv_fromP)) && (doctv_toP == "" || isNaN(doctv_toP))){
          // all empty and string
          jQuery(this).find(".doctv_from").css("border","1px solid red");
          jQuery(this).find(".doctv_to").css("border","1px solid red");
          flag1P++;
           //return false;
          
          
        }else if(doctv_fromP != "" || isNaN(doctv_fromP)){
          if( doctv_toP == "" || isNaN(doctv_toP)){
            // check from less than 1000 and to is empty
            if(doctv_fromP < 1000){
              jQuery(this).find(".doctv_to").css("border","1px solid red");
              flag1P++;
               //return false;
            }
          }
          if(doctv_fromP == "" || isNaN(doctv_fromP)){
              // when from empty
              jQuery(this).find(".doctv_from").css("border"," 1px solid red");
              flag1P++;
              //return false;
          } 
          
        }

        //  validation that there are no overlaps with the periods
        if (!fromToArrP.hasOwnProperty(doctv_currencyP)) {
          fromToArrP[doctv_currencyP] = {};  
        }
        var countObjP = Object.keys(fromToArrP[doctv_currencyP]).length;
        fromToArrP[doctv_currencyP][countObjP] = {};
        fromToArrP[doctv_currencyP][countObjP]["from"] = doctv_fromP;
        fromToArrP[doctv_currencyP][countObjP]["to"] = doctv_toP;
        fromToArrP[doctv_currencyP][countObjP]["currency"] = doctv_currencyP;
        if(flag1P == 0 && Object.keys(fromToArrP[doctv_currencyP]).length > 1){
          for(var jP=0; jP<Object.keys(fromToArrP[doctv_currencyP]).length-1; jP++){
             if(((doctv_fromP >= fromToArrP[doctv_currencyP][jP]["from"] && doctv_fromP <= fromToArrP[doctv_currencyP][jP]["to"]) || (doctv_toP >= fromToArrP[doctv_currencyP][jP]["from"] && doctv_toP <= fromToArrP[doctv_currencyP][jP]["to"])) && doctv_currencyP == fromToArrP[doctv_currencyP][jP]["currency"]){
              console.log("forrrr");
              jQuery(this).find(".doctv_from").css("border","1px solid red");
              jQuery(this).find(".doctv_to").css("border","1px solid red");
              flag1P++;
              overlapsP++;
            }
            // check if there is gap between previous to and next from
            if(((fromToArrP[doctv_currencyP][jP]["to"]+1) != fromToArrP[doctv_currencyP][jP+1]["from"]) && doctv_currencyP == fromToArrP[doctv_currencyP][jP]["currency"]){
              jQuery(this).find(".doctv_from").css("border","1px solid red");
              jQuery(this).find(".doctv_to").css("border","1px solid red");
              flag1P++;
              hasGapP++;  
            } 
          }
        }

        iP++;

        // check if from is bigger than to
        if(doctv_fromP > doctv_toP){
          jQuery(this).find(".doctv_from").css("border","1px solid red");
          jQuery(this).find(".doctv_to").css("border","1px solid red");  
          fromBiggerP++;
        }

        });
      }

      // 
      if(jQuery("select#payment_us_splitit_paymentredirect_first_payment").val() == "percentage"){
        var percentageOfOrderP = jQuery("#payment_us_splitit_paymentredirect_percentage_of_order").val();
        if(percentageOfOrderP > 50 ){
          percentageFlagP++; 
          flag1P++; 
        }
        
      }
//      alert(jQuery('#payment_us_splitit_paymentredirect_select_installment_setup').val());
      if(flag1P == 0){
        if(jQuery('#payment_us_splitit_paymentredirect_select_installment_setup').val() == 'depending_on_cart_total'){
          createJsonOfDependingOnCartTotal1(); 
        }
        eval(configForm.submit());
      }else{
        if(fromBiggerP){
          alert("From amount should be lesser than To.");
        }
        else if(overlapsP){
          alert("From and To amount should not Overlap");
        }
        else if(percentageFlagP){
          jQuery("#payment_pis_cc_percentage_of_order").css("border","1px solid red");
          alert("Please enter Percentage of order % <= 50");
        }else if(hasGapP){
          alert("There should not be Gap between To and From amounts.");
        }else{
          alert('Please fill the required fields in Splitit section "Depending on cart total"');  
        }
        
         return false;
      }
       

    });

  

  jQuery(document).on('change', '#payment_us_splitit_paymentredirect_select_installment_setup', function(){
    if(jQuery(this).val() == 'fixed'){
      jQuery('#tiers_tableP.splitit').closest('td').addClass('not-allowed-td');
      jQuery('#payment_us_splitit_paymentredirect_fixed_installment').removeAttr('disabled');
    }else{
      jQuery('#tiers_tableP.splitit').closest('td').removeClass('not-allowed-td');
      jQuery('#payment_us_splitit_paymentredirect_fixed_installment').attr('disabled', 'disabled');
    }
  });

  jQuery(document).on("click","#payment_us_splitit_paymentredirect_check_setting", function(){
    checkSetting1();  
  });
}

function getCurrency1(){
  
  
  jQuery.ajax({
    url: baseUrl1 + "splititpaymentmethod/getcurrency/getcurrency", 
    showLoader: true,
    success: function(result){
      jQuery('#tiers_tableP.splitit').find("input.doctv_currency").val(result.currencyCode);
      jQuery('#tiers_tableP.splitit').find("span.base-currency-symbol").html(result.currencySymbol);
      baseCurrencyCode1 = result.currencyCode;
      baseCurrencySymbol1 = result.currencySymbol;
      currencyCodeSymbol1 = result.currencyCodeSymbol;

      // console.log(jQuery("#payment_us_splitit_paymentredirect_depending_on_cart_total").length);
      // show depanding on cart table
      var tableHtmlP = getTableHtml1();
      jQuery("#payment_us_splitit_paymentredirect_depending_on_cart_total").replaceWith(tableHtmlP);
      // disable or enable Fixed and Depanding on cart total
      if(jQuery("#payment_us_splitit_paymentredirect_select_installment_setup").val() == 'fixed'){
        jQuery('#tiers_tableP.splitit').closest('td').addClass('not-allowed-td');
        jQuery('#payment_us_splitit_paymentredirect_fixed_installment').removeAttr('disabled');
      }else{
        jQuery('#tiers_tableP.splitit').closest('td').removeClass('not-allowed-td');
        jQuery('#payment_us_splitit_paymentredirect_fixed_installment').attr('disabled', 'disabled');
      }
    }
  });
}

function getTableHtml1(){
  var htmlP = '<table class="data border splitit" id="tiers_tableP" cellspacing="0" border="1">'
        +'<div class="tiers_tableP_overlay"></div>'
        +'<thead>'
        +'<tr class="headings">'
        +'<th style="min-width: 100px;">Cart total</th>'
        +'<th style="min-width: 160px;">#Installments</th>'
        +'<th >Currency</th>'
        +'<th class="last">Action</th>'
        +'</tr>'
        +'</thead>'
        +'<tbody id="tier_price_containerP">';
    htmlP += getTableInnerContent1();    
    htmlP += '</tbody>'
        +'<tfoot>'
        +'<tr>'
        +'<td style="display:none"></td>'
        +'<td colspan="4" align="right"><button id="" title="Add Tier" type="button" class="scalable add add-tier" onclick="addRow1();" style=""><span><span><span>Add Tier</span></span></span></button></td>'
        +'</tr>'
        +'</tfoot>'
        +'</table>';
    return htmlP;
}

function getTableInnerContent1(){
  var jsonValueP = jQuery("#payment_us_splitit_paymentredirect_depanding_on_cart_total_values").val();
  console.log(jsonValueP);
  if(jsonValueP == "" || jsonValueP == undefined){
    return getRowHtml1();
  }else{
    return getRowHtmlFromJson1();
  }
}

function getRowHtml1(){
  var rowHtmlP = '<tr>'
         +'<td style="padding: 8px;">'
          +'From<br><span class="base-currency-symbol">'+getCurrencyCode1("")+'</span><input type="text" class="doctv_from" name="doctv_from_pf" /><br>To<br><span class="base-currency-symbol">'+getCurrencyCode1("")+'</span><input type="text" class="doctv_to" name="doctv_to_pf" />'
         +'</td>'
         +'<td style="padding: 8px;">'
          +'<select id="doctv_installments" name="doctv_installments_pf" class=" select multiselect doctv_installments" size="10" multiple="multiple">'
            +'<option value="2">2 Installments</option>'
            +'<option value="3">3 Installments</option>'
            +'<option value="4">4 Installments</option>'
            +'<option value="5">5 Installments</option>'
            +'<option value="6">6 Installments</option>'
            +'<option value="7">7 Installments</option>'
            +'<option value="8">8 Installments</option>'
            +'<option value="9">9 Installments</option>'
            +'<option value="10">10 Installments</option>'
            +'<option value="11">11 Installments</option>'
            +'<option value="12">12 Installments</option>'
            +'</select>'
         +'</td>'
         +'<td style="padding: 8px; text-align: center;">'
           +getCurrencyDropdown1("")
         +'</td>'
         +'<td style="padding: 8px; text-align: center;">'
          +'<button title="Delete Tier" type="button" class="scalable delete icon-btn delete-product-option" id="" onclick="deleteRow1(this);"><span><span><span>Delete</span></span></span></button>'
         +'</td>'
      +'</tr>';

    return rowHtmlP;  
}

function getRowHtmlFromJson1(){
  var doctvP = JSON.parse(jQuery("#payment_us_splitit_paymentredirect_depanding_on_cart_total_values").val());
  var rowHtmlP = "";
  jQuery.each( doctvP, function( indexP, valueP ){
      rowHtmlP += '<tr>';
        rowHtmlP += '<td style="padding: 8px;"> From<br><span class="base-currency-symbol">'+getCurrencyCode1(valueP.doctv.currency)+'</span><input type="text" class="doctv_from" name="doctv_from_pf" value="'+valueP.doctv.from+'" /><br>To<br><span class="base-currency-symbol">'+getCurrencyCode1(valueP.doctv.currency)+'</span><input type="text" class="doctv_to" name="doctv_to_pf" value="'+valueP.doctv.to+'" /> </td>';
        rowHtmlP += '<td style="padding: 8px;"> <select id="" name="doctv_installments_pf" class=" select multiselect doctv_installments" size="10" multiple="multiple">';
        var installmentsP = valueP.doctv.installments.split(',');
        var iP = 2;
        var selectedP = "";
        for(iP=2; iP<=12; iP++){
          if(jQuery.inArray(iP.toString(), installmentsP) != -1){
            selectedP = 'selected="selected"';
          }
          rowHtmlP += '<option value="'+iP+'" '+selectedP+'>'+iP+' Installments</option>';  
            selectedP = "";

        }
        rowHtmlP += '</select></td>';
        rowHtmlP += '<td style="padding: 8px; text-align: center;">'+getCurrencyDropdown1(valueP.doctv.currency)+'</td>'; 
        rowHtmlP += '<td style="padding: 8px; text-align: center;">'
                +'<button title="Delete Tier" type="button" class="scalable delete icon-btn delete-product-option" id="" onclick="deleteRow1(this);"><span><span><span>Delete</span></span></span></button>'
              +'</td>'
              +'</tr>';

  });
  return rowHtmlP;
}

function addRow1(){
  var rowHtmlP = getRowHtml1();
  jQuery("#tiers_tableP.splitit tbody#tier_price_containerP").append(rowHtmlP);
}
function deleteRow1(curObjP){
    var countP = jQuery(curObjP).closest("tbody").find("tr").length;
    if(countP > 1){
        jQuery(curObjP).closest("tr").remove();    
    }
    
}
function getCurrencyDropdown1(selectedCurrencyP){
    var currencyDropdownP = '<select id="" class="doctv_currency" name="doctv_currency_pf" class=" select">';
    var selectedP = "";       
    jQuery.each(currencyCodeSymbol1, function(iP, valP) {
      selectedP = ""
        if(selectedCurrencyP == iP){
            selectedP = 'selected="selected"';
        }
        currencyDropdownP += '<option value="'+iP+'" '+selectedP+'>'+iP+'</option>';    
    });
    return currencyDropdownP += '</select>';

}

function getCurrencyCode1(selectedCurrencyP){
    var currencyCodeP = "";

    if(selectedCurrencyP == ""){
        var flagP = 0;
        jQuery.each(currencyCodeSymbol1, function(iP, valP) {
            if(flagP == 0){
              currencyCodeP = valP;
            }
            flagP++;
        });
        return currencyCodeP;

    }
    if (currencyCodeSymbol1.hasOwnProperty(selectedCurrencyP)){
        return currencyCodeSymbol1[selectedCurrencyP];
    } 
}

function createJsonOfDependingOnCartTotal1(){
     var iP = 0;
     var objectP = {};

     jQuery("#tiers_tableP.splitit tbody").find('tr').each(function() {
        var $thisP = jQuery(this);
        var installmentsP = [];
        objectP[iP] = {};
        objectP[iP]["doctv"] = {};
        objectP[iP]["doctv"]["from"] = $thisP.find("td:first-child").find("input.doctv_from").val();
        objectP[iP]["doctv"]["to"] = $thisP.find("td:first-child").find("input.doctv_to").val(); 
        $thisP.find("td:nth-child(2)").find(".doctv_installments :selected").each(function(iP, selectedP){ 
           installmentsP.push(jQuery(selectedP).val());
        });
        objectP[iP]["doctv"]["installments"] = installmentsP.toString();
        objectP[iP]["doctv"]["currency"] = $thisP.find("td:nth-child(3)").find(".doctv_currency").val();
       
        iP++;      
    });
    objectP = JSON.stringify(objectP);
    console.log(objectP);
    jQuery("#payment_us_splitit_paymentredirect_depanding_on_cart_total_values").val(objectP);

}

function checkSetting1(){

  jQuery.ajax({
    url: baseUrl1 + "splititpaymentmethod/checksetting/checksetting", 
    showLoader: true,
    success: function(result){
      if(result.status){
        alert(result.successMsg);  
      } else{
        alert(result.errorMsg);
      } 
    }
  });
}