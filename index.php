<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>CNLRRS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 pt-20">

  <!-- Header -->
<header id="main-header" class="bg-white text-black px-6 py-3 shadow-lg fixed top-0 left-0 w-full z-50 transition-transform duration-300">
  <div class="container mx-auto flex justify-between items-center">
    <!-- Logo and Title -->
    <div class="flex items-center space-x-4">
      <img src="Images/logo.png" alt="CNLRRS Logo" class="h-14 w-auto object-contain">
      <h1 class="text-xl font-bold text-[#115D5B] leading-tight">Camarines Norte Lowland <br class="hidden sm:block" /> Rainfed Research Station</h1>
    </div>

    <!-- Hamburger Button -->
    <button id="menu-toggle" class="xl:hidden text-black text-3xl focus:outline-none">☰</button>

    <!-- Navigation Menu -->
    <nav id="nav-menu" class="hidden xl:block">
      <ul class="flex flex-col xl:flex-row xl:space-x-6 bg-[#115D5B] xl:bg-transparent xl:static absolute left-0 top-full w-full xl:w-auto p-4 xl:p-0 shadow-md xl:shadow-none">
        <li><a href="#" class="hover:text-gray-300 block py-2">Home</a></li>
        <li><a href="#" class="hover:text-gray-300 block py-2">Our Services</a></li>
        <li><a href="#" class="hover:text-gray-300 block py-2">Contact Us</a></li>
        <li><a href="elibrary.html" class="bg-green-1000 px-4 py-2 rounded-lg hover:text-gray-300 block py-2">Library</a></li>
        <li><a href="account.php" class="bg-green-900 px-6 py-2 rounded-lg hover:text-gray-300 block py-2">Log In</a></li>
      </ul>
    </nav>
  </div>
</header>

  <!-- Main Content -->
  <section class="relative">
    <img src="Images/Banner.png" alt="Pineapple Farm" class="w-full h-[750px] object-cover" />
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center text-white bg-black bg-opacity-50">
      <h2 class="text-3xl font-bold">Welcome to Camarines Norte Lowland Rainfed Research Station</h2>
      <p class="mt-2 text-lg">Learn and Discover the Secrets Behind the Sweetest Pineapple in Camarines Norte</p>
      <a href="#" class="mt-4 bg-green-500 px-4 py-2 rounded-md text-white font-semibold hover:bg-green-700">Explore More</a>
    </div>
  </section>

  <!-- Image Grid Section -->
  <section class="container mx-auto my-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="rounded-lg overflow-hidden shadow-lg border">
        <img src="Images/farm.jpg" alt="Farm Image" class="w-full h-[300px] object-cover" />
      </div>
      <div class="rounded-lg overflow-hidden shadow-lg border">
        <img src="Images/facility.jpg" alt="Facility Image" class="w-full h-[300px] object-cover" />
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-[#115D5B] text-white text-center py-4 mt-6">
    <p>&copy; 2025 My Simple Website. All rights reserved.</p>
  </footer>

  <!-- Scripts -->
  <script>
    // Hamburger toggle
    const menuToggle = document.getElementById('menu-toggle');
    const navMenu = document.getElementById('nav-menu');
    menuToggle.addEventListener('click', () => {
      navMenu.classList.toggle('hidden');
    });

    // Hide header on scroll down, show on scroll up
    let lastScrollTop = 0;
    const header = document.getElementById('main-header');

    window.addEventListener('scroll', () => {
      const scrollTop = window.scrollY || document.documentElement.scrollTop;
      if (scrollTop > lastScrollTop) {
        header.style.transform = 'translateY(-100%)'; // hide
      } else {
        header.style.transform = 'translateY(0)'; // show
      }
      lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // For Mobile
    });
  </script>
</body>
</html>
