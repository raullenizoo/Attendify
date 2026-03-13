<?php
   session_start();
   
   /* =============================================================
   UNIVERSAL PROJECT ROOT DETECTION (best method for your setup)
   ============================================================= */
    $root = __DIR__;
    while (!file_exists($root . '/config/db.php') && dirname($root) !== $root) {
        $root = dirname($root);
    }
    define('ROOT_PATH', $root);
    define('BASE_URL', '/Attendifyv1/public/');   // ← matches your current folder structure
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendify | Make easier attendance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cardo:ital,wght@0,400;0,700;1,400&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="assets/img/logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="/Attendifyv1/public/assets/css/main.css">
</head>
<body>

<nav>
  <h2><a href="#">Attendify.</a></h2>
  <ul>
    <li><a href="#about">About</a></li>
    <li><a href="#features">Features</a></li>
    <li><a href="#how-it-works">How It Works</a></li>
    <li><a href="#testimonials">Testimonials</a></li>
    <li><a href="#pricing">Pricing</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a class="btn get-started" href="/Attendifyv1/public/get-started.php">Get Started</a></li>
  </ul>
  <!--<div class="theme-toggle">🌙</div>-->
  <div class="nav-icons">
      <div class="menu-icon">&#9776;</div>
      <div class="theme-toggle">🌙</div>
  </div>
</nav>

<div class="hero">
  <div>
    <h1>Hello, This is <span>Attendify</span></h1>
    <span id="typing-text" style="font-size: 2rem;"></span><span class="typing-cursor" style="font-size: 2rem;">|</span>
    <p>Smart and efficient attendance management system designed to simplify tracking, monitoring, and reporting for schools and organizations. Experience seamless integration and real-time insights.</p>
    <div class="buttons">
      <a href="#features" class="btn btn-primary">View Features</a>
      <a href="#contact" class="btn btn-secondary">Contact Us</a>
    </div>
  </div>
</div>

<section id="about">
  <div class="section-title">About Attendify</div>
  <div class="about">
    <div class="about-text">
      <p>
        Attendify is a modern web-based attendance system designed to simplify student tracking, 
        reporting, and real-time monitoring. With intuitive interfaces and robust features, it helps 
        educational institutions and organizations manage attendance effortlessly. Our platform ensures 
        data security, scalability, and ease of use for admins, teachers, and students alike.
      </p>
    </div>
    <div class="about-stats">
      <div class="stat">
        <h2>10K+</h2>
        <p>Users Worldwide</p>
      </div>
      <div class="stat">
        <h2>99.9%</h2>
        <p>Uptime Guarantee</p>
      </div>
      <div class="stat">
        <h2>500+</h2>
        <p>Schools Served</p>
      </div>
      <div class="stat">
        <h2>24/7</h2>
        <p>Support Available</p>
      </div>
    </div>
  </div>
</section>

<section id="features">
  <div class="section-title">Core Features</div>
  <div class="cards">
    <div class="card">
      <h3>Real-Time Tracking</h3>
      <p>Instantly monitor attendance records and student activity with live updates and notifications.</p>
    </div>
    <div class="card">
      <h3>Secure Login</h3>
      <p>Admin and user authentication with protected data handling, including two-factor authentication.</p>
    </div>
    <div class="card">
      <h3>Analytics Dashboard</h3>
      <p>View attendance trends, generate custom reports, and export data in various formats easily.</p>
    </div>
    <div class="card">
      <h3>Mobile Compatibility</h3>
      <p>Fully responsive design for seamless access on desktops, tablets, and smartphones.</p>
    </div>
    <div class="card">
      <h3>Integration Options</h3>
      <p>Easily integrate with existing systems like Google Workspace, Microsoft Teams, and more.</p>
    </div>
  </div>
</section>

<section id="how-it-works">
  <div class="section-title">How It Works</div>
  <div class="steps">
    <div class="step">
      <div class="step-number">1</div>
      <div class="step-content">
        <h3>Sign Up & Set Up</h3>
        <p>Create an account, add your organization details, and invite users in minutes.</p>
      </div>
    </div>
    <div class="step">
      <div class="step-number">2</div>
      <div class="step-content">
        <h3>Track Attendance</h3>
        <p>Use simple tools to mark attendance, with options for manual entry or automated check-ins.</p>
      </div>
    </div>
    <div class="step">
      <div class="step-number">3</div>
      <div class="step-content">
        <h3>Analyze & Report</h3>
        <p>Access dashboards for insights and generate reports to share with stakeholders.</p>
      </div>
    </div>
  </div>
</section>

<section id="testimonials">
  <div class="section-title">What Our Users Say</div>
  <div class="testimonials">
    <div class="testimonial">
      <p>"Attendify has transformed how we manage attendance. It's intuitive and saves us hours every week!"</p>
      <div class="testimonial-author">- School Principal, XYZ Academy</div>
    </div>
    <div class="testimonial">
      <p>"The real-time tracking feature is a game-changer for our organization. Highly recommended!"</p>
      <div class="testimonial-author">- HR Manager, ABC Corp</div>
    </div>
    <div class="testimonial">
      <p>"Secure, reliable, and easy to use. Our team loves the analytics dashboard."</p>
      <div class="testimonial-author">- Teacher, DEF School</div>
    </div>
  </div>
</section>

<section id="pricing"> 
  <div class="section-title">Pricing Plans</div>
  <div class="plans">
    <div class="plan">
      <h3>Basic</h3>
      <div class="price">$0/month</div>
      <ul>
        <li>Up to 50 users</li>
        <li>Basic tracking</li>
        <li>Email support</li>
      </ul>
      <a href="#contact" class="btn btn-secondary">Get Started</a>
    </div>
    <div class="plan">
      <h3>Pro</h3>
      <div class="price">$49/month</div>
      <ul>
        <li>Unlimited users</li>
        <li>Advanced analytics</li>
        <li>Priority support</li>
        <li>Integrations</li>
      </ul>
      <a href="#contact" class="btn btn-primary">Choose Pro</a>
    </div>
    <div class="plan">
      <h3>Enterprise</h3>
      <div class="price">Custom</div>
      <ul>
        <li>Custom features</li>
        <li>Dedicated support</li>
        <li>On-premise options</li>
        <li>API access</li>
      </ul>
      <a href="#contact" class="btn btn-secondary">Contact Us</a>
    </div>
  </div>
</section>

<section id="contact">
  <div class="section-title">Get In Touch</div>
  <div class="contact">
    <p>Ready to improve your attendance system? Fill out the form below and we'll get back to you soon.</p>
    <form>
      <input type="text" placeholder="Your Name" required>
      <input type="email" placeholder="Your Email" required>
      <textarea placeholder="Your Message" rows="5" required></textarea>
      <button type="submit">Send Message</button>
    </form>
  </div>
</section>

<footer>
  © 2026 Attendify. All Rights Reserved.
</footer>

<script src="/Attendifyv1/public/assets/js/main.js"></script>

</body>
</html>