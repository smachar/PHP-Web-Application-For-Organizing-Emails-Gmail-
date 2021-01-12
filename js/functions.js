var displayed_content=1;
function startcollect(isForNew='no'){
    if(isForNew=='yes'){
        
        //disable button click
    }
    else{
        document.querySelector('.loadbtn').className = "lds-ellipsis";
        var startbtn = document.querySelector('.startbtn');
        startbtn.disabled=true;
        startbtn.className = '';
    }
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if(xhr.readyState==4 && xhr.status == 200){
            document.querySelector('.main-overview').innerHTML = xhr.responseText;
            if(displayed_content==1 && xhr.responseText!=""){
                document.querySelector('.main').innerHTML += "<div class='load-more-button' onClick='loadMore()'>Charger Plus</div>";
            }
            displayed_content=1;
        }
    };
    xhr.open('GET', 'collector2.php');
    xhr.send();
}
function displayEmails(){
    var f = document.forms['search'];
    var qr = f['qr'].value;
    if(qr!=''){
        var load_btn = document.querySelector('.load-more-button');
        if(load_btn.style.display !== "none"){
            load_btn.style.display = "none";
        }
        var sen = f['sen'].checked;
        var sub = f['sub'].checked;
        var sni = f['sni'].checked;
        var pla = f['pla'].checked;
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function(){
            if(xhr.readyState==4 && xhr.status == 200){
                if(xhr.responseText!=''){
                    document.querySelector('.main-overview').innerHTML = xhr.responseText;
                    displayed_content=1;
                    document.querySelector('.title').innerHTML = "<h2>E-mails correspondant à recherche pour : '"+qr+"'</h2>";
                }
            }
        };
        xhr.open('GET', "getEmails.php?qr="+qr+"&sen="+sen+"&sub="+sub+"&sni="+sni+"&pla="+pla);
        xhr.send();
    }
}
function displayMailbox(mailbox_name){
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if(xhr.readyState==4 && xhr.status == 200){
            if(xhr.responseText!=''){
                document.querySelector('.main-overview').innerHTML = xhr.responseText;
                displayed_content=1;
                if(mailbox_name=='all'){
                    document.querySelector('.title').innerHTML = "<h2>Tout afficher</h2>";
                } else{
                    document.querySelector('.title').innerHTML = "<h2>E-mails dans la boite: '"+mailbox_name+"'</h2>";
                }
            }
        }
    };
    if(mailbox_name=='all'){
        xhr.open('GET', "getEmails.php");
        xhr.send();
        var load_btn = document.querySelector('.load-more-button');
        if(load_btn.style.display == "none"){
            load_btn.style.display = "block";
        }
    } else{
        xhr.open('GET', "getMailbox.php?mailbox="+mailbox_name);
        xhr.send();
        var load_btn = document.querySelector('.load-more-button');
        if(load_btn.style.display !== "none"){
            load_btn.style.display = "none";
        }
    }
}
function loadMore(){
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if(xhr.readyState==4 && xhr.status == 200){
            if(xhr.responseText!=''){
                // var next_page = document.createElement('div');
                // next_page.setAttribute("class", "next-page-content");
                // next_page.innerHTML = xhr.responseText;
                //document.querySelector('.main-overview').appendChild(next_page);
                document.querySelector('.main-overview').innerHTML += xhr.responseText;
            }
            displayed_content++;
            if(xhr.responseText.slice(-1)===' '){
                console.log("haha");
                var load_btn = document.querySelector('.load-more-button');
                if(load_btn.style.display !== "none"){
                    load_btn.style.display = "none";
                }
            }
        }
    };
    xhr.open('GET', "getEmails.php?offset="+displayed_content*5); //5 is the limit
    xhr.send();
};