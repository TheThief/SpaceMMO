$(document).ready(function(){
    $(".navbutton").click(function(){
        event.preventDefault();
        var hrefurl = $(this).attr("href");
        var jsonurl = hrefurl + (hrefurl.indexOf("?")<0?"?":"&") + "api=json";
        $.getJSON(jsonurl,function(data){
            if(data.status=="ok"){
		        if(window.history.replaceState){
                    window.history.replaceState(null,null,hrefurl);
                }
                $(".navbutton.up").attr("href",data.navbuttons.up);
                $(".navbutton.down").attr("href",data.navbuttons.down);
                $(".navbutton.right").attr("href",data.navbuttons.right);
                $(".navbutton.left").attr("href",data.navbuttons.left);
                $(".navbutton.zmin").attr("href",data.navbuttons.zoomin);
                $(".navbutton.zmout").attr("href",data.navbuttons.zoomout);
                var tempStarmap = $(document.createElement('div'));

                $.each(data.systems, function(key,val){
                    var container = $(document.createElement('div'));
                    $(container).addClass("systemcontainer");
                    $(container).css("width",val.container.width + "em");
                    $(container).css("height",val.container.height + "em");
                    $(container).css("left",val.container.x + "em");
                    $(container).css("top",val.container.y + "em");
                    var link = $(document.createElement('a'));
                    $(link).attr("href",val.link);
                    var star = $(document.createElement('img'));
                    $(star).attr("src",val.star.image);
                    $(star).addClass("star");
                    $(star).css("width",val.star.width + "em");
                    $(star).css("height",val.star.height + "em");
                    $(star).css("margin",val.star.offsetX + "em 0 0 " + val.star.offsetY + "em");
                    $(link).append(star);
                    if(val.indicator){
                        var indicator = $(document.createElement('img'));
                        $(indicator).attr("src",val.indicator.image);
                        $(indicator).css("width",val.indicator.width + "em");
                        $(indicator).css("height",val.indicator.height + "em");
                        $(link).append(indicator);
                    }
                    $(container).append(link);
                    var info = $(document.createElement('div'));
                    $(info).addClass("info");
                    $(info).css("padding-top",val.info.padding + "em");
                    $(info).append("<h3>" + val.info.syscode + "</h3>");
                    $(info).append(val.info.info);
                    $(container).append(info);
                    $(tempStarmap).append(container);
                });

                $("#starmaparea").html($(tempStarmap).html());
            }
        });
        return false;
    });
});