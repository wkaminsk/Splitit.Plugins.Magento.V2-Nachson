var baseCurrencyCode = "";
var baseCurrencySymbol = "";
var jqueryIsHere = 0;
var curUrl = window.location.href; 
var baseUrl = "";
baseUrl = curUrl.substring(0, curUrl.indexOf('admin'));
var jqueryInterval = setInterval(function(){    
    if(window.jQuery){
      jqueryIsHere = 1;
      clearInterval(jqueryInterval);
      runMyScripts();
     }else{
      console.log('jquery not loaded!!');
     }       
  }, 1000);

function runMyScripts(){
  console.log(jQuery);
  var tableHtml = getTableHtml();
  jQuery("#payment_us_splitit_paymentmethod_depending_on_cart_total").replaceWith(tableHtml);
  // disable or enable Fixed and Depanding on cart total
  if(jQuery("#payment_us_splitit_paymentmethod_select_installment_setup").val() == 'fixed'){
    jQuery('#tiers_table.splitit').closest('td').addClass('not-allowed-td');
    jQuery('#payment_us_splitit_paymentmethod_fixed_installment').removeAttr('disabled');
  }else{
    jQuery('#tiers_table.splitit').closest('td').removeClass('not-allowed-td');
    jQuery('#payment_us_splitit_paymentmethod_fixed_installment').attr('disabled', 'disabled');
  }

  jQuery(document).on("mousedown", "#save", function(event) {

      var flag1 = 0;
      var flag2 = 0;
      var percentageFlag = 0;
      var overlaps = 0;
      var fromBigger = 0;
      var hasGap = 0;
    
      // validation for depanding on cart
      // $(this).unbind('click');
      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();
      //alert('Please fill the required fields in Splitit section "Depending on cart total"');
      jQuery("#payment_us_splitit_paymentmethod_percentage_of_order").css("border","1px solid #ccc");
      if(jQuery('#payment_us_splitit_paymentmethod_select_installment_setup').val() == 'depending_on_cart_total'){
        var fromToArr = {};
        var i=0;
        jQuery("#tier_price_container tr").each(function(){
        
        
        var doctv_from = parseFloat(jQuery(this).find(".doctv_from").val());
        var doctv_to = parseFloat(jQuery(this).find(".doctv_to").val());
        jQuery(this).find(".doctv_from").css("border","1px solid #ccc");
        jQuery(this).find(".doctv_to").css("border","1px solid #ccc");
        jQuery(this).find("select.doctv_installments").css("border","1px solid #ccc");
        // validation for installments
        var installmentsCount = jQuery(this).find("select.doctv_installments  :selected").length;
        if(installmentsCount == 0){
          jQuery(this).find("select.doctv_installments").css("border-color","red");
          flag1++;
           //return false;
        }
        // validation for from and to amount
        if((doctv_from == "" || isNaN(doctv_from)) && (doctv_to == "" || isNaN(doctv_to))){
          // all empty and string
          jQuery(this).find(".doctv_from").css("border","1px solid red");
          jQuery(this).find(".doctv_to").css("border","1px solid red");
          flag1++;
           //return false;
          
          
        }else if(doctv_from != "" || isNaN(doctv_from)){
          if( doctv_to == "" || isNaN(doctv_to)){
            // check from less than 1000 and to is empty
            if(doctv_from < 1000){
              jQuery(this).find(".doctv_to").css("border","1px solid red");
              flag1++;
               //return false;
            }
          }
          if(doctv_from == "" || isNaN(doctv_from)){
              // when from empty
              jQuery(this).find(".doctv_from").css("border"," 1px solid red");
              flag1++;
              //return false;
          } 
          
        }

        //  validation that there are no overlaps with the periods
        fromToArr[i] = {};
        fromToArr[i]["from"] = doctv_from;
        fromToArr[i]["to"] = doctv_to;
        if(flag1 == 0 && Object.keys(fromToArr).length > 1){
          for(var j=0; j<Object.keys(fromToArr).length-1; j++){
            if((doctv_from >= fromToArr[j]["from"] && doctv_from <= fromToArr[j]["to"]) || (doctv_to >= fromToArr[j]["from"] && doctv_to <= fromToArr[j]["to"]) ){
              console.log("forrrr");
              jQuery(this).find(".doctv_from").css("border","1px solid red");
              jQuery(this).find(".doctv_to").css("border","1px solid red");
              flag1++;
              overlaps++;
            }
            // check if there is gap between previous to and next from
            if((fromToArr[j]["to"]+1) != fromToArr[j+1]["from"]){
              jQuery(this).find(".doctv_from").css("border","1px solid red");
              jQuery(this).find(".doctv_to").css("border","1px solid red");
              flag1++;
              hasGap++;  
            }  
          }
        }

        i++;

        // check if from is bigger than to
        if(doctv_from > doctv_to){
          jQuery(this).find(".doctv_from").css("border","1px solid red");
          jQuery(this).find(".doctv_to").css("border","1px solid red");  
          fromBigger++;
        }

        });
      }

      // 
      if(jQuery("select#payment_us_splitit_paymentmethod_first_payment").val() == "percentage"){
        var percentageOfOrder = jQuery("#payment_us_splitit_paymentmethod_percentage_of_order").val();
        if(percentageOfOrder > 50 ){
          percentageFlag++; 
          flag1++; 
        }
        
      }
      if(flag1 == 0){
        if(jQuery('#payment_us_splitit_paymentmethod_select_installment_setup').val() == 'depending_on_cart_total'){
          createJsonOfDependingOnCartTotal(); 
        }
        eval(configForm.submit());
      }else{
        if(fromBigger){
          alert("From amount should be lesser than To.");
        }
        else if(overlaps){
          alert("From and To amount should not Overlap");
        }
        else if(percentageFlag){
          jQuery("#payment_pis_cc_percentage_of_order").css("border","1px solid red");
          alert("Please enter Percentage of order % <= 50");
        }else if(hasGap){
          alert("There should not be Gap between To and From amounts.");
        }else{
          alert('Please fill the required fields in Splitit section "Depending on cart total"');  
        }
        
         return false;
      }
       

    });

  getCurrency();

  jQuery(document).on('change', '#payment_us_splitit_paymentmethod_select_installment_setup', function(){
    if(jQuery(this).val() == 'fixed'){
      jQuery('#tiers_table.splitit').closest('td').addClass('not-allowed-td');
      jQuery('#payment_us_splitit_paymentmethod_fixed_installment').removeAttr('disabled');
    }else{
      jQuery('#tiers_table.splitit').closest('td').removeClass('not-allowed-td');
      jQuery('#payment_us_splitit_paymentmethod_fixed_installment').attr('disabled', 'disabled');
    }
  });

  jQuery(document).on("click","#payment_us_splitit_paymentmethod_check_setting", function(){
    checkSetting();  
  });
}

function getCurrency(){
  
  
  jQuery.ajax({
    url: baseUrl + "splititpaymentmethod/getcurrency/getcurrency", 
    success: function(result){
      jQuery("input.doctv_currency").val(result.currencyCode);
      jQuery("span.base-currency-symbol").html(result.currencySymbol);
      baseCurrencyCode = result.currencyCode;
      baseCurrencySymbol = result.currencySymbol;
    }
  });
}

function getTableHtml(){
  var html = '<table class="data border splitit" id="tiers_table" cellspacing="0" border="1">'
        +'<div class="tiers_table_overlay"></div>'
        +'<thead>'
        +'<tr class="headings">'
        +'<th style="min-width: 100px;">Cart total</th>'
        +'<th style="min-width: 160px;">#Installments</th>'
        +'<th >Currency</th>'
        +'<th class="last">Action</th>'
        +'</tr>'
        +'</thead>'
        +'<tbody id="tier_price_container">';
    html += getTableInnerContent();    
    html += '</tbody>'
        +'<tfoot>'
        +'<tr>'
        +'<td style="display:none"></td>'
        +'<td colspan="4" align="right"><button id="" title="Add Tier" type="button" class="scalable add add-tier" onclick="addRow();" style=""><span><span><span>Add Tier</span></span></span></button></td>'
        +'</tr>'
        +'</tfoot>'
        +'</table>';
    return html;
}

function getTableInnerContent(){
  var jsonValue = jQuery("#payment_us_splitit_paymentmethod_depanding_on_cart_total_values").val();
  if(jsonValue == "" || jsonValue == undefined){
    return getRowHtml();
  }else{
    return getRowHtmlFromJson();
  }
}

function getRowHtml(){
  var rowHtml = '<tr>'
         +'<td style="padding: 8px;">'
          +'From<br><span class="base-currency-symbol">'+baseCurrencySymbol+'</span><input type="text" class="doctv_from" name="doctv_from" /><br>To<br><span class="base-currency-symbol">'+baseCurrencySymbol+'</span><input type="text" class="doctv_to" name="doctv_to" />'
         +'</td>'
         +'<td style="padding: 8px;">'
          +'<select id="doctv_installments" name="doctv_installments" class=" select multiselect doctv_installments" size="10" multiple="multiple">'
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
           +'<input style="width: 60px;" disabled class="doctv_currency" value="'+baseCurrencyCode+'"/>'
         +'</td>'
         +'<td style="padding: 8px; text-align: center;">'
          +'<button title="Delete Tier" type="button" class="scalable delete icon-btn delete-product-option" id="" onclick="deleteRow(this);"><span><span><span>Delete</span></span></span></button>'
         +'</td>'
      +'</tr>';

    return rowHtml;  
}

function getRowHtmlFromJson(){
  var doctv = JSON.parse(jQuery("#payment_us_splitit_paymentmethod_depanding_on_cart_total_values").val());
  var rowHtml = "";
  jQuery.each( doctv, function( index, value ){
      rowHtml += '<tr>';
        rowHtml += '<td style="padding: 8px;"> From<br><span class="base-currency-symbol">$</span><input type="text" class="doctv_from" name="doctv_from" value="'+value.doctv.from+'" /><br>To<br><span class="base-currency-symbol">$</span><input type="text" class="doctv_to" name="doctv_to" value="'+value.doctv.to+'" /> </td>';
        rowHtml += '<td style="padding: 8px;"> <select id="" name="doctv_installments" class=" select multiselect doctv_installments" size="10" multiple="multiple">';
        var installments = value.doctv.installments.split(',');
        var i = 2;
        var selected = "";
        for(i=2; i<=12; i++){
          if(jQuery.inArray(i.toString(), installments) != -1){
            selected = 'selected="selected"';
          }
          rowHtml += '<option value="'+i+'" '+selected+'>'+i+' Installments</option>';  
            selected = "";

        }
        rowHtml += '</select></td>';
        rowHtml += '<td style="padding: 8px; text-align: center;"><input style="width: 60px;" disabled class="doctv_currency" value="'+value.doctv.currency+'"/></td>'; 
        rowHtml += '<td style="padding: 8px; text-align: center;">'
                +'<button title="Delete Tier" type="button" class="scalable delete icon-btn delete-product-option" id="" onclick="deleteRow(this);"><span><span><span>Delete</span></span></span></button>'
              +'</td>'
              +'</tr>';

  });
  return rowHtml;
}

function addRow(){
  var rowHtml = getRowHtml();
  jQuery("table.splitit tbody#tier_price_container").append(rowHtml);
}
function deleteRow(curObj){
    var count = jQuery(curObj).closest("tbody").find("tr").length;
    if(count > 1){
        jQuery(curObj).closest("tr").remove();    
    }
    
}

function createJsonOfDependingOnCartTotal(){
     var i = 0;
     var object = {};

     jQuery("table.splitit tbody").find('tr').each(function() {
        var $this = jQuery(this);
        var installments = [];
        object[i] = {};
        object[i]["doctv"] = {};
        object[i]["doctv"]["from"] = $this.find("td:first-child").find("input.doctv_from").val();
        object[i]["doctv"]["to"] = $this.find("td:first-child").find("input.doctv_to").val(); 
        $this.find("td:nth-child(2)").find(".doctv_installments :selected").each(function(i, selected){ 
           installments.push(jQuery(selected).val());
        });
        object[i]["doctv"]["installments"] = installments.toString();
        object[i]["doctv"]["currency"] = $this.find("td:nth-child(3)").find(".doctv_currency").val();
       
        i++;      
    });
    object = JSON.stringify(object);
    jQuery("#payment_us_splitit_paymentmethod_depanding_on_cart_total_values").val(object);

}

function checkSetting(){

  jQuery.ajax({
    url: baseUrl + "splititpaymentmethod/checksetting/checksetting", 
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