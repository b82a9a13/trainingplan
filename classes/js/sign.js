//Define varaibles
const div = document.querySelector('.sign-div');
const canvas = document.querySelector('.sign-canvas');
const clearBtn = document.querySelector('.clear-btn');
const ctx = canvas.getContext('2d');
ctx.fillStyle = 'white';
ctx.lineWidth = 3;
ctx.lineJoin = ctx.lineCap = 'round';
ctx.fillRect(0, 0, canvas.width, canvas.height);
let writingMode = false;

//Clear canvas
const clear = ()=>{
    ctx.fillRect(0, 0, canvas.width, canvas.height);
}

//Event for clear btn
clearBtn.addEventListener('click', (event)=>{
    event.preventDefault();
    clear();
});

//Get position
const getPosition = (event)=>{
    const positionY = event.clientY - event.target.getBoundingClientRect().y;
    const positionX = event.clientX - event.target.getBoundingClientRect().x;
    return [positionX, positionY];
}

//Change writing mode on pointer down and draw line
canvas.addEventListener('pointerdown', (event)=>{
    writingMode = true;
    ctx.beginPath();
    const [positionX, positionY] = getPosition(event);
    ctx.moveTo(positionX, positionY);
}, {passive:true});

//Change writing mode on pointer up
canvas.addEventListener('pointerup', ()=>{
    writingMode = false;
}, {passive:true});

//Event for when pointer moves
canvas.addEventListener('pointermove', (event)=>{
    if(!writingMode) return;
    const [positionX, positionY] = getPosition(event);
    ctx.lineTo(positionX, positionY);
    ctx.stroke();
}, {passive:true});

//Change writing mode when the cursor leaves canvas
canvas.addEventListener('mouseout', ()=>{
    writingMode = false;
}, {passive:true});

