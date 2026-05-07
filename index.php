<?php require_once __DIR__ . '/init.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="KrBank - Your Future. Your Bank. Premium online banking with seamless transfers, investments, and financial education.">
<title>KrBank - Your Future. Your Bank.</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<!-- NAVBAR -->
<nav class="navbar" id="navbar">
<div class="container">
<a href="index.php" class="nav-logo">Kr<span>Bank</span></a>
<ul class="nav-links" id="navLinks">
<li><a href="#features">Features</a></li>
<li><a href="#about">About</a></li>
<li><a href="#testimonials">Testimonials</a></li>
<li><a href="login.php" class="btn btn-outline btn-sm">Login</a></li>
<li><a href="register.php" class="btn btn-primary btn-sm">Get Started</a></li>
</ul>
<button class="mobile-toggle" onclick="document.getElementById('navLinks').classList.toggle('active')">
<i class="fas fa-bars"></i>
</button>
</div>
</nav>

<!-- HERO -->
<section class="hero">
<div class="container">
<div class="hero-content fade-in">
<h1>Banking Made <span>Simple, Secure</span> & Smart</h1>
<p>Experience the future of banking with KrBank. Manage your finances, invest wisely, and transfer globally — all from one premium platform built for your financial freedom.</p>
<div class="hero-actions">
<a href="register.php" class="btn btn-primary btn-lg">Open Free Account <i class="fas fa-arrow-right"></i></a>
<a href="#features" class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,0.3);color:#fff">Learn More</a>
</div>
<div class="hero-stats">
<div class="hero-stat"><h3>2M+</h3><p>Active Users</p></div>
<div class="hero-stat"><h3>$50B+</h3><p>Assets Managed</p></div>
<div class="hero-stat"><h3>99.9%</h3><p>Uptime</p></div>
<div class="hero-stat"><h3>150+</h3><p>Countries</p></div>
</div>
</div>
</div>
</section>

<!-- TRUST BAR -->
<div class="trust-bar">
<div class="container">
<div class="trust-items">
<div class="trust-item"><i class="fas fa-shield-halved"></i> Bank-Level Security</div>
<div class="trust-item"><i class="fas fa-lock"></i> 256-bit Encryption</div>
<div class="trust-item"><i class="fas fa-building-columns"></i> FDIC Insured</div>
<div class="trust-item"><i class="fas fa-certificate"></i> SOC 2 Certified</div>
<div class="trust-item"><i class="fas fa-clock"></i> 24/7 Support</div>
</div>
</div>
</div>

<!-- FEATURES -->
<section id="features" style="background:var(--light)">
<div class="container">
<div class="section-title">
<h2>Everything You Need to Thrive</h2>
<p>Powerful banking tools designed to help you manage, grow, and protect your money with confidence.</p>
</div>
<div class="features-grid">
<div class="card feature-card">
<div class="icon"><i class="fas fa-wallet"></i></div>
<h3>Multi-Account Management</h3>
<p>Checking, savings, and investment accounts — all in one place with real-time balance tracking.</p>
</div>
<div class="card feature-card">
<div class="icon" style="background:linear-gradient(135deg,#10b981,#34d399)"><i class="fas fa-paper-plane"></i></div>
<h3>Global Transfers</h3>
<p>Send money locally and internationally with competitive rates. Crypto transfers supported.</p>
</div>
<div class="card feature-card">
<div class="icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24)"><i class="fas fa-credit-card"></i></div>
<h3>Virtual & Physical Cards</h3>
<p>Issue Visa, Mastercard, or Amex cards instantly. Freeze, set limits, and manage with ease.</p>
</div>
<div class="card feature-card">
<div class="icon" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa)"><i class="fas fa-chart-line"></i></div>
<h3>Stock Trading</h3>
<p>Buy and sell stocks with real-time charts. Build your portfolio and track performance.</p>
</div>
<div class="card feature-card">
<div class="icon" style="background:linear-gradient(135deg,#ec4899,#f472b6)"><i class="fas fa-piggy-bank"></i></div>
<h3>Save & Invest</h3>
<p>Set savings goals with progress tracking. Auto-save features to build wealth effortlessly.</p>
</div>
<div class="card feature-card">
<div class="icon" style="background:linear-gradient(135deg,#06b6d4,#22d3ee)"><i class="fas fa-hand-holding-dollar"></i></div>
<h3>Loan Management</h3>
<p>Apply for personal, auto, or business loans. Track repayments and manage your debt smartly.</p>
</div>
</div>
</div>
</section>

<!-- WHY KRBANK -->
<section id="about">
<div class="container">
<div class="section-title">
<h2>Why KrBank?</h2>
<p>We believe banking should empower, educate, and inspire — not confuse.</p>
</div>
<div class="grid grid-3">
<div class="card" style="text-align:center;padding:40px">
<div style="font-size:3rem;margin-bottom:16px">🏦</div>
<h3 style="margin-bottom:8px">Radical Transparency</h3>
<p class="text-muted">No hidden fees. Every charge explained. We show you exactly where your money goes and why.</p>
</div>
<div class="card" style="text-align:center;padding:40px">
<div style="font-size:3rem;margin-bottom:16px">📚</div>
<h3 style="margin-bottom:8px">Financial Education</h3>
<p class="text-muted">Tooltips, guides, and "Why this matters" sections everywhere to help you make informed decisions.</p>
</div>
<div class="card" style="text-align:center;padding:40px">
<div style="font-size:3rem;margin-bottom:16px">🛡️</div>
<h3 style="margin-bottom:8px">Unmatched Security</h3>
<p class="text-muted">Multi-layer security with PIN protection, COT/IMF codes, and real-time fraud monitoring.</p>
</div>
</div>
</div>
</section>

<!-- TESTIMONIALS -->
<section id="testimonials" style="background:var(--light)">
<div class="container">
<div class="section-title">
<h2>Loved by Millions</h2>
<p>See what our customers are saying about their KrBank experience.</p>
</div>
<div class="testimonials-grid">
<div class="card testimonial-card">
<div class="stars">★★★★★</div>
<blockquote>"KrBank transformed how I manage my finances. The interface is beautiful and everything is so intuitive. Best banking decision I ever made."</blockquote>
<div class="author"><div class="avatar">JD</div><div><strong>James Davidson</strong><br><small class="text-muted">Business Owner</small></div></div>
</div>
<div class="card testimonial-card">
<div class="stars">★★★★★</div>
<blockquote>"International transfers used to be a nightmare. With KrBank, I send money to my family abroad in minutes with the lowest fees I've found."</blockquote>
<div class="author"><div class="avatar">SA</div><div><strong>Sarah Amara</strong><br><small class="text-muted">Software Engineer</small></div></div>
</div>
<div class="card testimonial-card">
<div class="stars">★★★★★</div>
<blockquote>"The investment tools and savings goals feature helped me save $15,000 in just 8 months. I love the progress tracking and auto-save."</blockquote>
<div class="author"><div class="avatar">MR</div><div><strong>Michael Roberts</strong><br><small class="text-muted">Teacher</small></div></div>
</div>
</div>
</div>
</section>

<!-- CTA -->
<section style="padding:60px 0">
<div class="cta-section">
<h2>Ready to Take Control of Your Finances?</h2>
<p>Join millions who trust KrBank for secure, smart, and seamless banking.</p>
<a href="register.php" class="btn btn-lg" style="background:#fff;color:var(--primary)">Create Free Account <i class="fas fa-arrow-right"></i></a>
</div>
</section>

<!-- FOOTER -->
<footer class="footer">
<div class="container">
<div class="footer-grid">
<div>
<h4 style="font-size:1.4rem;margin-bottom:12px" class="nav-logo">KrBank</h4>
<p style="font-size:0.9rem;line-height:1.7;margin-bottom:16px">Your trusted partner for modern banking. Building financial futures since 2020.</p>
<div style="display:flex;gap:12px">
<a href="#" style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center"><i class="fab fa-twitter"></i></a>
<a href="#" style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center"><i class="fab fa-linkedin"></i></a>
<a href="#" style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center"><i class="fab fa-instagram"></i></a>
</div>
</div>
<div><h4>Products</h4><a href="#">Checking</a><a href="#">Savings</a><a href="#">Cards</a><a href="#">Loans</a><a href="#">Investments</a></div>
<div><h4>Company</h4><a href="#">About</a><a href="#">Careers</a><a href="#">Press</a><a href="#">Blog</a><a href="#">Contact</a></div>
<div><h4>Legal</h4><a href="#">Privacy Policy</a><a href="#">Terms of Service</a><a href="#">Cookie Policy</a><a href="#">Security</a></div>
</div>
<div class="footer-bottom">
<p>&copy; <?= date('Y') ?> KrBank. All rights reserved. FDIC Insured. Equal Opportunity Lender.</p>
</div>
</div>
</footer>

<script>
// Navbar scroll effect
window.addEventListener('scroll',()=>{
document.getElementById('navbar').classList.toggle('scrolled',window.scrollY>50);
});
// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a=>{
a.addEventListener('click',e=>{e.preventDefault();const t=document.querySelector(a.getAttribute('href'));if(t)t.scrollIntoView({behavior:'smooth'})});
});
// Fade in on scroll
const observer=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('fade-in');observer.unobserve(e.target)}})},{threshold:0.1});
document.querySelectorAll('.card,.section-title').forEach(el=>observer.observe(el));
</script>
</body>
</html>
