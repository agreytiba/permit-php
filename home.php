<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Additional styling for the slideshow */
        .slideshow-container {
            max-width: 100%;
            position: relative;
            margin: auto;
        }

        .mySlides {
            display: none;
        }

        .active {
            display: block !important;
        }

        .fade {
            animation: fadeEffect 1.5s;
        }

        @keyframes fadeEffect {
            from {
                opacity: 0.4;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Slideshow -->
    <div class="slideshow-container mt-4 h-srceen">
        <div class="mySlides fade">
            <img src="public/image/picture1.jpg" style="width:100%">
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center">
                <h2 class="text-white text-4xl font-bold">Slide 1 Title</h2>
                <p class="text-white mt-2">This is a subtitle for Slide 1</p>
                <p class="text-white mt-2">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
        </div>

        <div class="mySlides fade">
            <img src="public/image/picture2.jpg" style="width:100%">
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center">
                <h2 class="text-white text-4xl font-bold">Slide 2 Title</h2>
                <p class="text-white mt-2">This is a subtitle for Slide 2</p>
                <p class="text-white mt-2">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
        </div>

        <div class="mySlides fade">
            <img src="public/image/picture1.jpg" style="width:100%">
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center">
                <h2 class="text-white text-4xl font-bold">Slide 3 Title</h2>
                <p class="text-white mt-2">This is a subtitle for Slide 3</p>
                <p class="text-white mt-2">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-800">Welcome to Our Website</h2>
            <p class="mt-4 text-gray-600">We are pleased to have you here. Explore our services and feel free to reach out to us for any inquiries.</p>
        </div>
        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-800">Our Mission</h3>
                <p class="mt-4 text-gray-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-800">Our Vision</h3>
                <p class="mt-4 text-gray-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-semibold text-gray-800">Our Values</h3>
                <p class="mt-4 text-gray-600">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.</p>
            </div>
        </div>
    </div>

    <script>
        let slideIndex = 0;
        showSlides();

        function showSlides() {
            let slides = document.getElementsByClassName("mySlides");
            for (let i = 0; i < slides.length; i++) {
                slides[i].classList.remove("active");
            }
            slideIndex++;
            if (slideIndex > slides.length) {
                slideIndex = 1;
            }
            slides[slideIndex - 1].classList.add("active");
            setTimeout(showSlides, 4000); // Change image every 4 seconds
        }
    </script>
</body>

</html>