var matchpass = function(){
    p1=document.getElementsByName('password1')[0].value;
    btnerr = document.getElementById('btnerr');
    try {
        p2=document.getElementsByName('password2')[0].value;
        if(p1!='' && p2!='' && p2==p1){
            btnerr.disabled = false;
            btnerr.style.backgroundColor = '#87FFBF'; 
        }
        else{
            btnerr.disabled = true;
            btnerr.style.backgroundColor = '#EA3C53';
        }
    } catch (e) {
        if (e instanceof TypeError) {
            if(p1!=''){
                btnerr.disabled = false;
                btnerr.style.backgroundColor = '#87FFBF';
            }
            else{
                btnerr.disabled = true;
                btnerr.style.backgroundColor = '#EA3C53';
            }
        }
    }
}