document.getElementById('setup_form').addEventListener('submit', (e)=>{
    e.preventDefault();
    const error = document.getElementById('setup_error');
    error.style.display = 'none';
    error.innerHTML = '';
    const totalmonths = document.getElementById('totalmonths').value;
    const totalhours = document.getElementById('totalhours').value;
    const eors = document.getElementById('eors').value;
    const coach = document.getElementById('coach').value;
    const morm = document.getElementById('morm').value;
    const startdate = document.getElementById('startdate').value;
    const hpw = document.getElementById('hpw').value;
    const alw = document.getElementById('alw').value;
    const trainplan = document.getElementById('trainplan').value;
    const signature = document.getElementById('signature');
    const option = document.getElementById('option').value;
    let imagedata = signature.getContext('2d').getImageData(0,0,signature.width,signature.height).data;
    let errorTxt = 'Invalid Signature.';
    //Check for non white pixel every third
    for(let i = 0; i < imagedata.length; i += 12){
        const red = imagedata[i];
        const green = imagedata[i + 1];
        const blue = imagedata[i + 2];
        if(red !== 255 || green !== 255 || blue !== 255){
            errorTxt = '';
        }
    }
    if(errorTxt != ''){
        error.innerText = errorTxt;
        error.style.display = 'block';
    } else {
        const dataURL = signature.toDataURL('image/jpeg');
        const params = `totalmonths=${totalmonths}&totalhours=${totalhours}&eors=${eors}&coach=${coach}&morm=${morm}&startdate=${startdate}&hpw=${hpw}&alw=${alw}&trainplan=${trainplan}&sign=${dataURL}&option=${option}`;
        const xhr = new XMLHttpRequest();
        xhr.open('POST', './classes/inc/setup.inc.php',true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function(){
            if(this.status == 200){
                const text = JSON.parse(this.responseText);
                if(text['error']){
                    if(text['error'].length > 0){
                        let i = 0;
                        error.innerText = 'Invalid: '
                        for(const errors of text['error']){
                            if(i == 0){
                                error.innerText += errors;
                                i++;
                            } else {
                                error.innerText += ', '+errors;
                            }
                        }
                        error.innerText += '.';
                    } else {
                        error.innerText = text['error'][0];
                    }
                    error.style.display = 'block';
                } else {
                    if(text['return']){
                        document.getElementById('setup_btm').click();
                    } else {
                        error.innerText = 'Creation error.';
                        error.style.display = 'block';
                    }
                }
            } else {
                error.innerText = 'Connection error.';
                error.style.display = 'block';
            }
        }
        xhr.send(params);
    }
});
function plan_clicked(){
    const select = document.getElementById('option');
    const td = document.getElementById('option_td');
    const th = document.getElementById('option_th');
    const int = document.getElementById("option_"+document.getElementById('trainplan').value).getAttribute('options');
    select.innerHTML = '';
    let option = document.createElement('option');
    option.value = '';
    option.text = 'Choose A Option';
    option.selected = true;
    option.disabled = true;
    option.hidden = true;
    select.appendChild(option);
    if(int > 0){
        select.required = true;
        for(let i = 1; i <= int; i++){
            option = document.createElement('option');
            option.value = i;
            option.text = 'Option '+i;
            select.appendChild(option);
        }
        th.style.display = 'block';
        td.style.display = 'block';
    } else {
        select.required = false;
        td.style.display = 'none';
        th.style.display = 'none';
    }
}