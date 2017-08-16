var html=[];
var coauthors;
jQuery(document).ready(function(){
    coauthors=document.getElementsByClassName('co-authors');
    if(coauthors[0]){
        coauthors=coauthors[0];
        for(var i=0;i<coauthors.childNodes.length;i++){
            var temp=coauthors.childNodes[i];
            if(temp.nodeName=='DIV' && temp.childNodes[1] && temp.childNodes[1].nodeName=='INPUT')
                html.push(temp);
        }
    }

    document.getElementById('post').onsubmit=function(){
        for(var i=0;i<html.length;i++)
            coauthors.appendChild(html[i]);
    };
});
function changeSearch(word){
    word=word.trim();
    if(coauthors!=undefined){
        coauthors.innerHTML="";

        if(word==''){
            for(var i=0;i<html.length;i++)
                coauthors.appendChild(html[i]);

            return;
        }

        for(var i=0;i<html.length;i++)
            if(html[i].id.includes(word))
                coauthors.appendChild(html[i]);
    }
}

var acc = document.getElementsByClassName("accordion");

for (var i = 0; i < acc.length; i++) {
  acc[i].onclick = function() {
    this.classList.toggle("active");
    var panel = this.nextElementSibling;
    if (panel.style.maxHeight){
      panel.style.maxHeight = null;
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
    } 
  }
}
