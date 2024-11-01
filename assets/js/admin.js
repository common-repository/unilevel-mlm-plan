var $=jQuery.noConflict();
$("#account_details").click(function(){
 $(".p2").parent().addClass("ump_account_details");
});

function update_payment_status(url,id,status)
{
$.ajax({
           type: "POST",
           url: url,
           data: {
            'action':'update_payment_status_ump',
            'user_id':id,
            'status':status,
           },
           success: function(data)
           {
           }
         });
     return false;
}


$(document).ready(function(){

  $('.ump-pair').blur(function(){
    var value=$(this).val();
    var id=$(this).attr('id');
    var pre_id=id-1;
    var pre_data=$('#'+pre_id).val();
    if ((pre_id!='0') && (parseInt(pre_data) > parseInt(value))) {
      alert('Please Increase Referral Value');
      $(this).val('');
    }

  });
    $('[data-toggle="popover"]').popover();   
   
});
