var $=jQuery.noConflict();
var umpajaxurl=$('meta[name="ump_adminajax"]').attr('content');
// Register form submit
$("#ump_register_form").submit(function(e) {
	e.preventDefault(); 
    var form = $(this);
    var urlw= $(this.redirect_URL);
    var postdata=form.serialize();
    $.ajax({
           type: "POST",
           url: umpajaxurl,
           data: postdata,
           success: function(data)
           {
           	var obj = $.parseJSON(data);
           	if(obj.status==false){
           		$.each(obj.error , function (key, value) {
            $('.'+key).addClass('err_div');
            $('.'+key).html('<span>'+value+'</span>');
				});

           	} else{
              window.location.href =urlw.val();
           	}

           }
         });
     return false;

});
$('#ump_username').blur(function(){

  var username=$('#ump_username').val();
  $.ajax({
      type: 'POST',
      url: umpajaxurl,
      data: {
          'action': 'ump_username_exist',
          'username': username,
      },
      success: function (data) {
        $('.ump_username_message').html('');
        var obj = $.parseJSON(data);
        if(obj.status==false){
          $('.ump_username_message').addClass('err_div');
          $('.ump_username_message').html(obj.message);
          $('#ump_username').val('');
        } else{
          $('.ump_username_message').addClass('err_div');
          $('.ump_username_message').html(obj.message);
          
        }
          return false;
      }
  });
});
$('#ump_epin').blur(function(){
  var epin=$('#ump_epin').val();
  $.ajax({
      type: 'POST',
      url: umpajaxurl,
      data: {
          'action': 'ump_epin_exist',
          'epin': epin,
      },
      success: function (data) {
        $('.ump_epin_message').html('');
        var obj = $.parseJSON(data);
        if(obj.status==false){
          $('.ump_epin_message').addClass('err_div');
          $('.ump_epin_message').html(obj.message);
          $('#ump_epin').val('');
        } else{
          $('.ump_epin_message').addClass('err_div');
          $('.ump_epin_message').html(obj.message);
          
        }
          return false;
      }
  });
});
// user email exist check
$('#ump_email').blur(function(){

  var email=$('#ump_email').val();
  $.ajax({
      type: 'POST',
      url: umpajaxurl,
      data: {
          'action': 'ump_email_exist',
          'email': email,
      },
      success: function (data) {
        $('.ump_email_message').html('');
        var obj = $.parseJSON(data);
        if(obj.status==false){
          $('.ump_email_message').addClass('err_div');
          $('.ump_email_message').html(obj.message);
          $('#ump_email').val('');
        } else{
          $('.ump_email_message').addClass('err_div');
          $('.ump_email_message').html(obj.message);
          
        }
          return false;
      }
  });

});
// user password exist check
$('#ump_confirm_password').blur(function(){

  var password=$('#ump_password').val();
  var confirm_password=$('#ump_confirm_password').val();

  $.ajax({
      type: 'POST',
      url: umpajaxurl,
      data: {
          'action': 'ump_password_validation',
          'password': password,
          'confirm_password': confirm_password,
      },
      success: function (data) {
        $('.ump_confirm_password_message').html('');
        var obj = $.parseJSON(data);
        if(obj.status==false){
          $('.ump_confirm_password_message').addClass('err_div');
          $('.ump_confirm_password_message').html(obj.message);
          $('#ump_confirm_password').val('');
          $('#ump_password').val('');
        } else {
          $('.ump_confirm_password_message').addClass('err_div');
          $('.ump_confirm_password_message').html(obj.message);
          
        }
          return false;
      }
  });

});
$('#ump_sponsor').blur(function(){

  var sponsor=$('#ump_sponsor').val();
  $.ajax({
      type: 'POST',
      url: umpajaxurl,
      data: {
          'action': 'ump_sponsor_exist',
          'sponsor': sponsor,
      },
      success: function (data) {
        $('.ump_sponsor_message').html('');
        var obj = jQuery.parseJSON(data);
        if(obj.status==false){
          $('.ump_sponsor_message').addClass('err_div');
          $('.ump_sponsor_message').html(obj.message);
          $('#ump_sponsor').val('');
        } else {
          $('.ump_sponsor_message').addClass('err_div');
          $('.ump_sponsor_message').html(obj.message);
          
        }
          return false;
      }
  });

});

$('#ump_join_sponsor').blur(function(){

  var sponsor=$('#ump_join_sponsor').val();
  $.ajax({
      type: 'POST',
      url: umpajaxurl,
      data: {
          'action': 'ump_sponsor_exist',
          'sponsor': sponsor,
      },
      success: function (data) {
        $('.ump_join_sponsor_message').html('');
        var obj = jQuery.parseJSON(data);
        if(obj.status==false){
          // $('.ump_join_sponsor_message').addClass('err_div');
          $('.ump_join_sponsor_message').html(obj.message);
          $('#ump_join_sponsor').val('');
        } else {
          // $('.ump_join_sponsor_message').addClass('err_div');
          $('.ump_join_sponsor_message').html(obj.message);
          
        }
          return false;
      }
  });

});
