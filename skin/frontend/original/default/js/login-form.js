$(document).ready(function(){

if($("#login").val()!='')
{
    $("#login").prev().text('');
    $("#pass").prev().text('');
}

$("div > input").focus(
function(e)
{
    var clicked = $(e.target),
    clickedId = clicked.attr("id");
	
    if(clickedId=="login")
    {
         clicked.prev().text('');
    }

    else if(clickedId=="pass")
    {
         clicked.prev().text('');
     }

});


$("div > input").blur(
function(e)
{
    var clicked = $(e.target),
    clickedId = clicked.attr("id");


    if(clickedId=="login")
    {
        if(clicked.val()=='') clicked.prev().text('Kunden-Nr.');
    }

    else if(clickedId=="pass")
    {
        if(clicked.val()=='') clicked.prev().text('Passwort');
    }

});

});
