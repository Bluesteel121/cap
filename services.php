<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>CNLRRS - Our Services</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
          <li><a href="index.php" class="text-white xl:text-black font-bold hover:text-gray-300 block py-2">Home</a></li>
          <li><a href="services.php" class="text-white xl:text-black font-bold hover:text-gray-300 block py-2">Our Services</a></li>
          <li><a href="#contact" class="text-white xl:text-black font-bold hover:text-gray-300 block py-2">Contact Us</a></li>
          <li><a href="#faq" class="text-white xl:text-black font-bold hover:text-gray-300 block py-2">FAQ</a></li>
          <li><a href="elibrary.php" class="text-white xl:text-black font-bold bg-green-1000 px-4 py-2 rounded-lg hover:text-gray-300 block py-2">Library</a></li>
          <li><a href="account.php" class="text-white xl:text-black font-bold bg-green-900 px-6 py-2 rounded-lg hover:text-gray-300 block py-2">Log In</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Banner Section -->
  <section class="relative">
    <img src="Images/Banner.png" alt="Pineapple Field" class="w-full h-[400px] object-cover" />
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center text-white bg-black bg-opacity-50">
      <h2 class="text-4xl font-bold">Our Services</h2>
      <p class="mt-2 text-lg max-w-3xl mx-auto px-4">Empowering Pineapple Farmers in Camarines Norte Through Research, Innovation, and Sustainable Agriculture</p>
    </div>
  </section>

  <!-- Introduction -->
  <section class="container mx-auto my-12 px-4">
    <div class="max-w-4xl mx-auto">
      <h2 class="text-3xl font-bold text-[#115D5B] mb-6">CNLRRS and Pineapple Farming</h2>
      <p class="text-lg mb-6">The Camarines Norte Lowland Rainfed Research Station (CNLRRS) is dedicated to advancing pineapple agriculture in our region. As the home of some of the sweetest pineapples in the Philippines, we work closely with local farmers to improve cultivation techniques, ensure sustainable practices, and enhance crop quality and yield.</p>
      <p class="text-lg">Our comprehensive approach combines cutting-edge research with practical knowledge transfer, helping farmers overcome challenges and capitalize on opportunities in the ever-evolving agricultural landscape.</p>
    </div>
  </section>

  <!-- Main Services -->
  <section class="container mx-auto my-12 px-4">
    <h2 class="text-3xl font-bold text-center text-[#115D5B] mb-12">Our Core Services for Pineapple Farmers</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <!-- Service 1 -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="h-60 bg-[#115D5B] flex items-center justify-center p-6">
          <i class="fas fa-flask text-6xl text-white"></i>
        </div>
        <div class="p-6">
          <h3 class="text-xl font-bold text-[#115D5B] mb-3">Research & Development</h3>
          <ul class="space-y-2 list-disc pl-5">
            <li>Development of high-yielding, disease-resistant pineapple varieties</li>
            <li>Soil health optimization for pineapple cultivation</li>
            <li>Water management solutions for rainfed conditions</li>
            <li>Integrated pest management systems</li>
            <li>Post-harvest handling techniques to extend shelf life</li>
          </ul>
        </div>
      </div>
      
      <!-- Service 2 -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="h-60 bg-[#115D5B] flex items-center justify-center p-6">
          <i class="fas fa-chalkboard-teacher text-6xl text-white"></i>
        </div>
        <div class="p-6">
          <h3 class="text-xl font-bold text-[#115D5B] mb-3">Training & Extension Services</h3>
          <ul class="space-y-2 list-disc pl-5">
            <li>Regular farmer field schools and workshops</li>
            <li>Technical training on modern cultivation techniques</li>
            <li>Advisory services for farm management</li>
            <li>On-farm demonstrations of best practices</li>
            <li>Technology transfer programs for small-scale farmers</li>
          </ul>
        </div>
      </div>
      
      <!-- Service 3 -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="h-60 bg-[#115D5B] flex items-center justify-center p-6">
          <i class="fas fa-seedling text-6xl text-white"></i>
        </div>
        <div class="p-6">
          <h3 class="text-xl font-bold text-[#115D5B] mb-3">Planting Materials & Resources</h3>
          <ul class="space-y-2 list-disc pl-5">
            <li>Production of high-quality planting materials</li>
            <li>Distribution of certified seeds and seedlings</li>
            <li>Technical resources and farming guides</li>
            <li>Soil testing and analysis services</li>
            <li>Farm input recommendations and sourcing assistance</li>
          </ul>
        </div>
      </div>
      
      <!-- Service 4 -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="h-60 bg-[#115D5B] flex items-center justify-center p-6">
          <i class="fas fa-chart-line text-6xl text-white"></i>
        </div>
        <div class="p-6">
          <h3 class="text-xl font-bold text-[#115D5B] mb-3">Market Linkage & Development</h3>
          <ul class="space-y-2 list-disc pl-5">
            <li>Market research and intelligence gathering</li>
            <li>Connecting farmers to potential buyers and processors</li>
            <li>Value chain analysis and optimization</li>
            <li>Export market preparation and compliance support</li>
            <li>Cooperative development and strengthening</li>
          </ul>
        </div>
      </div>
      
      <!-- Service 5 -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="h-60 bg-[#115D5B] flex items-center justify-center p-6">
          <i class="fas fa-leaf text-6xl text-white"></i>
        </div>
        <div class="p-6">
          <h3 class="text-xl font-bold text-[#115D5B] mb-3">Sustainable Farming Practices</h3>
          <ul class="space-y-2 list-disc pl-5">
            <li>Climate-smart agriculture techniques</li>
            <li>Organic pineapple farming systems</li>
            <li>Biodiversity conservation in farming landscapes</li>
            <li>Soil conservation and erosion control</li>
            <li>Low-input farming methods for small-scale producers</li>
          </ul>
        </div>
      </div>
      
      <!-- Service 6 -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="h-60 bg-[#115D5B] flex items-center justify-center p-6">
          <i class="fas fa-industry text-6xl text-white"></i>
        </div>
        <div class="p-6">
          <h3 class="text-xl font-bold text-[#115D5B] mb-3">Value Addition & Processing</h3>
          <ul class="space-y-2 list-disc pl-5">
            <li>Product development from pineapple by-products</li>
            <li>Processing technology for small enterprises</li>
            <li>Quality control and food safety systems</li>
            <li>Packaging innovation for extended shelf life</li>
            <li>Technical support for processing facilities</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Success Stories -->
  <section class="bg-gray-200 py-12">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-center text-[#115D5B] mb-8">Success Stories</h2>
      
      <div class="max-w-4xl mx-auto">
        <!-- Story 1 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <div class="flex flex-col md:flex-row gap-6">
            <div class="md:w-1/3">
              <img src="Images/farm1.png" alt="Farmer Portrait" class="rounded-lg w-full h-auto">
            </div>
            <div class="md:w-2/3">
              <h3 class="text-xl font-bold text-[#115D5B] mb-2">Juan Mendoza - Daet, Camarines Norte</h3>
              <p class="mb-4">"With CNLRRS's guidance, I was able to double my pineapple yield within two growing seasons. Their soil management training and integrated pest management recommendations have transformed my 2-hectare farm from a struggling operation to a profitable business."</p>
              <p><strong>Result:</strong> 120% increase in yield, 80% reduction in pesticide use, and successful certification for organic production.</p>
            </div>
          </div>
        </div>
        
        <!-- Story 2 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <div class="flex flex-col md:flex-row gap-6">
            <div class="md:w-1/3">
              <img src="Images/facility.jpg" alt="Farmer Portrait" class="rounded-lg w-full h-auto">
            </div>
            <div class="md:w-2/3">
              <h3 class="text-xl font-bold text-[#115D5B] mb-2">Maria Santos - Cooperative President, Labo</h3>
              <p class="mb-4">"Our cooperative of 35 small-scale pineapple farmers has benefited tremendously from CNLRRS's market linkage program. Through their connections, we now supply directly to major supermarkets in Metro Manila and have begun exporting to Japan."</p>
              <p><strong>Result:</strong> 65% increase in farmer income, development of a local processing facility, and creation of 28 new jobs in the community.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Impact Statistics -->
  <section class="container mx-auto my-12 px-4">
    <h2 class="text-3xl font-bold text-center text-[#115D5B] mb-12">Our Impact in Numbers</h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-[#115D5B] text-white rounded-lg p-6 text-center">
        <div class="text-5xl font-bold mb-2">1,500+</div>
        <div class="text-xl">Farmers Trained</div>
      </div>
      
      <div class="bg-[#115D5B] text-white rounded-lg p-6 text-center">
        <div class="text-5xl font-bold mb-2">40%</div>
        <div class="text-xl">Average Yield Increase</div>
      </div>
      
      <div class="bg-[#115D5B] text-white rounded-lg p-6 text-center">
        <div class="text-5xl font-bold mb-2">5</div>
        <div class="text-xl">New Pineapple Varieties</div>
      </div>
      
      <div class="bg-[#115D5B] text-white rounded-lg p-6 text-center">
        <div class="text-5xl font-bold mb-2">3,200</div>
        <div class="text-xl">Hectares Improved</div>
      </div>
    </div>
  </section>

  <!-- How to Access Services -->
  <section class="bg-gray-200 py-12">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-center text-[#115D5B] mb-8">How to Access Our Services</h2>
      
      <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
          <ol class="space-y-4">
            <li class="flex items-start">
              <div class="bg-[#115D5B] text-white rounded-full w-8 h-8 flex items-center justify-center mr-4 flex-shrink-0 mt-1">1</div>
              <div>
                <h3 class="font-bold text-lg mb-1">Register at our office</h3>
                <p>Visit our main office to register as a farmer-partner. Bring basic information about your farm location, size, and current farming practices.</p>
              </div>
            </li>
            
            <li class="flex items-start">
              <div class="bg-[#115D5B] text-white rounded-full w-8 h-8 flex items-center justify-center mr-4 flex-shrink-0 mt-1">2</div>
              <div>
                <h3 class="font-bold text-lg mb-1">Consultation and assessment</h3>
                <p>Our technical staff will schedule an initial farm visit to assess your specific needs and challenges. This consultation is free of charge.</p>
              </div>
            </li>
            
            <li class="flex items-start">
              <div class="bg-[#115D5B] text-white rounded-full w-8 h-8 flex items-center justify-center mr-4 flex-shrink-0 mt-1">3</div>
              <div>
                <h3 class="font-bold text-lg mb-1">Customized service plan</h3>
                <p>Based on the assessment, we'll develop a tailored plan outlining which services will benefit your operation most.</p>
              </div>
            </li>
            
            <li class="flex items-start">
              <div class="bg-[#115D5B] text-white rounded-full w-8 h-8 flex items-center justify-center mr-4 flex-shrink-0 mt-1">4</div>
              <div>
                <h3 class="font-bold text-lg mb-1">Implementation and support</h3>
                <p>Our team will work with you to implement the recommended practices and provide ongoing support as needed.</p>
              </div>
            </li>
          </ol>
          
          <div class="mt-6 bg-gray-100 p-4 rounded-lg">
            <p class="font-bold">Note:</p>
            <p>Most of our services are provided free of charge or at subsidized rates for registered small-scale farmers. Commercial operations may be subject to service fees for certain specialized assistance.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="bg-[#115D5B] text-white py-12">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-center mb-8">Contact Us</h2>
      
      <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
          <h3 class="text-xl font-semibold mb-4">Get in Touch</h3>
          <p class="mb-6">Have questions about our services for pineapple farmers? Contact us today to learn how we can help improve your agricultural operation.</p>
          
          <div class="space-y-4">
            <div class="flex items-start">
              <i class="fas fa-map-marker-alt mt-1 mr-4"></i>
              <div>
                <h4 class="font-semibold">Address</h4>
                <p>123 Research Station Road, Camarines Norte, Philippines 4600</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <i class="fas fa-phone-alt mt-1 mr-4"></i>
              <div>
                <h4 class="font-semibold">Phone</h4>
                <p>+63 (054) 123-4567</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <i class="fas fa-envelope mt-1 mr-4"></i>
              <div>
                <h4 class="font-semibold">Email</h4>
                <p>info@cnlrrstation.gov.ph</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <i class="fas fa-clock mt-1 mr-4"></i>
              <div>
                <h4 class="font-semibold">Operating Hours</h4>
                <p>Monday to Friday: 8:00 AM - 5:00 PM</p>
                <p>Saturday: 8:00 AM - 12:00 PM</p>
                <p>Sunday: Closed</p>
              </div>
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="text-xl font-semibold mb-4">Send Us a Message</h3>
          <form class="space-y-4">
            <div>
              <label for="name" class="block mb-1">Name</label>
              <input type="text" id="name" class="w-full px-4 py-2 rounded text-gray-800" required>
            </div>
            
            <div>
              <label for="email" class="block mb-1">Email</label>
              <input type="email" id="email" class="w-full px-4 py-2 rounded text-gray-800" required>
            </div>
            
            <div>
              <label for="subject" class="block mb-1">Subject</label>
              <input type="text" id="subject" class="w-full px-4 py-2 rounded text-gray-800" required>
            </div>
            
            <div>
              <label for="message" class="block mb-1">Message</label>
              <textarea id="message" rows="4" class="w-full px-4 py-2 rounded text-gray-800" required></textarea>
            </div>
            
            <button type="submit" class="bg-white text-[#115D5B] px-6 py-2 rounded font-semibold hover:bg-gray-200 transition">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-[#115D5B] text-white text-center py-4">
    <p>&copy; 2025 Camarines Norte Lowland Rainfed Research Station. All rights reserved.</p>
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