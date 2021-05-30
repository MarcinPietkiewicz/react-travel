
// countdown functionality
let countDownDate = new Date("July 1, 2021 09:00:00").getTime();

let x = setInterval(function () {

    let now = new Date().getTime();

    let distance = countDownDate - now;

    let days = Math.floor(distance / (1000 * 60 * 60 * 24));
    let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    let seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById("timer").innerHTML = days + ":" + hours + ":"
        + minutes + ":" + seconds;

    if (distance < 0) {
        clearInterval(x);
        document.getElementById("timer").innerHTML = "Not planned";
    }
}, 1000);


// page and animations are shown after page load

window.onload = function(){ 
    document.getElementById("hideAll").style.display = "none"; 
 
 let title = document.getElementById('hero-title');
 title.classList.add('hero-title-transition');
 
 let description = document.getElementById('hero-desc');
 description.classList.add('hero-desc-transition'); 

 let menuText = document.getElementById('hero-menu');
 menuText.classList.add('hero-menu-transition'); 

 let bg = document.getElementById('bg');
 bg.classList.add('bg-transition'); 
}