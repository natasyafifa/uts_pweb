// Preloader Hide
$(window).on("load", function(){
  $("#preloader").fadeOut("slow");
});

// Navbar Scroll Effect
$(window).scroll(function(){
  if($(this).scrollTop() > 50){
    $(".navbar").addClass("scrolled");
  } else {
    $(".navbar").removeClass("scrolled");
  }
});

// Typing Effect
var typed = new Typed("#typing", {
  strings: ["Selamat Datang di Restoran Lezat", "Nikmati Hidangan Favoritmu", "Cita Rasa Tak Terlupakan"],
  typeSpeed: 60,
  backSpeed: 40,
  loop: true
});

// Smooth Scroll
$("a.nav-link").on("click", function(e) {
  if (this.hash !== "") {
    e.preventDefault();
    const hash = this.hash;

    $("html, body").animate({
      scrollTop: $(hash).offset().top - 70
    }, 1000, "swing");
  }
});

