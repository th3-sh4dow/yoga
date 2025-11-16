// javascript Animate onscroll Start
$(document).ready(function () {
    // AOS animations removed
});
/* ============================================================
   Sidenav
 * ============================================================*/
function openNav() {
    document.getElementById("mySidenav").style.width = "250px";
    document.body.classList.add("sidenav-open");
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
    document.body.classList.remove("sidenav-open");
}

// Optional: Add class to body to hide scrollbar completely
document.addEventListener('DOMContentLoaded', function() {
    // Uncomment next line if you want to completely hide scrollbar
    // document.body.classList.add('no-scrollbar');
});

$(document).on('click', function (e) {
    if ($(e.target).closest("#mySidenav, .slide-menu").length === 0) {
        document.getElementById("mySidenav").style.width = "0";
        document.body.classList.remove("sidenav-open");
    }
});
/* ============================================================
   Onscroll - Enhanced for all devices (Desktop + Mobile)
 * ============================================================*/

// Initialize variables after DOM is loaded
var navbar, header, stickyOffset = 50; // Reduced offset for earlier trigger

// Main scroll function
function handleStickyNav() {
    // Get elements each time to ensure they exist
    navbar = document.getElementById("navbar");
    header = document.querySelector("header");
    
    var scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    
    console.log('Scroll position:', scrollTop); // Debug log
    
    // Use a fixed offset instead of navbar.offsetTop for better desktop compatibility
    if (scrollTop > stickyOffset) {
        console.log('Adding sticky class'); // Debug log
        if (navbar) {
            navbar.classList.add("sticky");
        }
        if (header) {
            header.classList.add("sticky");
        }
    } else {
        console.log('Removing sticky class'); // Debug log
        if (navbar) {
            navbar.classList.remove("sticky");
        }
        if (header) {
            header.classList.remove("sticky");
        }
    }
}

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize elements
    navbar = document.getElementById("navbar");
    header = document.querySelector("header");
    
    console.log('DOM loaded, navbar:', navbar, 'header:', header); // Debug log
    
    // Primary scroll listener
    window.addEventListener('scroll', handleStickyNav);
    
    // Legacy support
    window.onscroll = handleStickyNav;
});

// jQuery backup for better compatibility
$(document).ready(function() {
    console.log('jQuery ready'); // Debug log
    
    $(window).scroll(function() {
        var scrollTop = $(this).scrollTop();
        
        console.log('jQuery scroll:', scrollTop); // Debug log
        
        if (scrollTop > stickyOffset) {
            $("#navbar").addClass("sticky");
            $("header").addClass("sticky");
            console.log('jQuery adding sticky'); // Debug log
        } else {
            $("#navbar").removeClass("sticky");
            $("header").removeClass("sticky");
            console.log('jQuery removing sticky'); // Debug log
        }
    });
});

$(function () {
    $(".scroll").click(function () {
        $("html,body").animate({
            scrollTop: $(".top").offset().top
        }, "1000");
        return false
    })
})
/* ============================================================
   Scroll to Top
 * ============================================================*/
$(window).scroll(function () {
    if ($(this).scrollTop() >= 50) { // If page is scrolled more than 50px
        $('#return-to-top').fadeIn(200); // Fade in the arrow
    } else {
        $('#return-to-top').fadeOut(200); // Else fade out the arrow
    }
});
$('#return-to-top').click(function () {
    // When arrow is clicked

    $('body, html').animate({
        scrollTop: 0 // Scroll to top of body
    }, 500);
});
// Test function to verify sticky animation
function testStickyAnimation() {
    console.log('Testing sticky animation...');
    var header = document.querySelector("header");
    var navbar = document.getElementById("navbar");
    
    if (header) {
        header.classList.add("sticky");
        console.log('Added sticky class to header');
    } else {
        console.log('Header not found!');
    }
    
    if (navbar) {
        navbar.classList.add("sticky");
        console.log('Added sticky class to navbar');
    } else {
        console.log('Navbar not found!');
    }
}

// Call test function after 2 seconds
setTimeout(testStickyAnimation, 2000);