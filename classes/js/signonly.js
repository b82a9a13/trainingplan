document.getElementById('sign_form').addEventListener('submit', (e)=>{
    e.preventDefault();
    const error = document.getElementById('sign_error');
    error.style.display = 'none';
    error.innerHTML = '';
    const signature = document.getElementById('signature');
    let imagedata = signature.getContext('2d').getImageData(0,0,signature.width,signature.height).data;
    let errorTxt = 'Invalid Signature.';
    for(let i = 0; i < imagedata.length; i += 12){
        const red = imagedata[i];
        const green = imagedata[i + 1];
        const blue = imagedata[i + 2];
        if(red !== 255 || green !== 255 || blue !== 255 ){
            errorTxt = '';
        }
    }
    if(errorTxt != ''){
        error.innerText = errorTxt;
        error.style.display = 'block';
    } else {
        const dataURL = signature.toDataURL('image/jpeg');
        const params = `sign=${dataURL}`;
        const xhr = new XMLHttpRequest();
        xhr.open('POST', './classes/inc/sign.inc.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function(){
            if(this.status == 200){
                const text = JSON.parse(this.responseText);
                console.log(text);
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
                        document.getElementById('sign_btm').click();
                    } else {
                        error.innerText = 'Creation error.';
                        error.style.display = 'block';
                    }
                }
            } else {
                error.innerText = 'Creation error.';
                error.style.display = 'block';
            }
        }
        xhr.send(params);
    }
});