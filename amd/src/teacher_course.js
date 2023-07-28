function course_clicked(id){
    const errorTxt = document.getElementById('trainingplan_error');
    errorTxt.style.display = 'none';
    const params = `id=${id}`;
    const xhr = new XMLHttpRequest();
    xhr.open('POST', './classes/inc/teacher_course.inc.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function(){
        if(this.status == 200){
            let text = JSON.parse(this.responseText);
            if(text['error']){
                errorTxt.innerText = text['error'];
                errorTxt.style.display = 'block';
            } else {
                if(text['return']){
                    document.getElementById('course_content_div').innerHTML = text['return'];
                }
            }
        }
    }
    xhr.send(params);
}