$j(document).ready(function(){
    $j('#login').blur();

    if($j("#login").val() != ''){
        $j("#login").prev().text('');
        $j("#pass").prev().text('');
    }

    $j("#mini-login-form .form-fields input").focus(function(e){
        var clicked = $j(e.target),
        clickedId = clicked.attr("id");
        if(clickedId == "login") {
            clicked.prev().text('');
        } else if(clickedId=="pass") {
            clicked.prev().text('');
        }
    });

    $j("#mini-login-form .form-fields input").blur(function(e){
        var clicked = $j(e.target),
        clickedId = clicked.attr("id");

        if(clickedId=="login") {
            if(clicked.val()=='') {
                clicked.prev().text(Translator.translate('Customer Number'));
            }
        } else if(clickedId=="pass") {
            if(clicked.val()=='') {
                clicked.prev().text(Translator.translate('Password'));
            }
        }
    });

});
