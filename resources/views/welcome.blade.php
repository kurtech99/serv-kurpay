<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
  <title>KURTECH VPN Remote - Layanan Tunnel Remote MikroTik & Multi-Port Tercepat</title>
  <meta name="description" content="Akses router MikroTik (WinBox, Mikhmon, WebFig), Server ACS (GenieACS), OLT, dan perangkat jaringan dari mana saja tanpa IP publik statis dengan KURTECH VPN Remote.">
  <meta name="keywords" content="VPN Remote MikroTik, Remote WinBox Tanpa IP Publik, Mikhmon Online, Remote Webfig, VPN Multi Port, KURTECH VPN">
  <meta name="author" content="KURTECH">
  <meta name="theme-color" content="#059669">

  <!-- Open Graph / WhatsApp Preview -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://vpnremote.kurtech.my.id/">
  <meta property="og:title" content="KURTECH VPN Remote - Remote MikroTik & Multi-Port Tercepat">
  <meta property="og:description" content="Akses router MikroTik (WinBox, Mikhmon, WebFig), Server ACS, dan OLT dari mana saja tanpa perlu IP publik statis. Stabil, aman, & low latency.">
  <meta property="og:image" content="{{ asset('images/logo-vpn-shield.png?v=2026') }}">

  <!-- Favicon -->
    <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('images/logo-vpn-shield.png?v=20260824_v1') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-vpn-shield.png?v=20260824_v1') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/logo-vpn-shield.png?v=20260824_v1') }}">

  <!-- Google Fonts & Font Awesome -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    /* ==================== DESIGN SYSTEM & COLOR TOKENS ==================== */
    :root {
      --primary: #059669;
      --primary-hover: #047857;
      --primary-light: #10B981;
      --primary-gradient: linear-gradient(135deg, #059669 0%, #0D9488 50%, #0284C7 100%);
      --accent-cyan: #0EA5E9;
      --accent-soft: #ECFDF5;
      --radius-sm: 8px;
      --radius-md: 14px;
      --radius-lg: 20px;
      --radius-xl: 28px;
      --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Light Mode (Default) */
    html[data-theme="light"] {
      --bg-main: #F8FAFC;
      --bg-card: #FFFFFF;
      --bg-card-subtle: #F1F5F9;
      --header-bg: 255, 255, 255;
      --text-main: #0F172A;
      --text-secondary: #334155;
      --text-muted: #64748B;
      --border: #E2E8F0;
      --border-hover: #CBD5E1;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
      --shadow-md: 0 6px 18px -2px rgba(0,0,0,0.06);
      --shadow-lg: 0 20px 30px -8px rgba(0,0,0,0.08);
      --badge-bg: #ECFDF5;
      --badge-color: #059669;
      --hero-glow: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, rgba(248, 250, 252, 0) 70%);
      --code-bg: #0F172A;
      --code-color: #34D399;
    }

    /* Dark Mode */
    html[data-theme="dark"] {
      --bg-main: #090D16;
      --bg-card: #111827;
      --bg-card-subtle: #1E293B;
      --header-bg: 17, 24, 39;
      --text-main: #F8FAFC;
      --text-secondary: #CBD5E1;
      --text-muted: #94A3B8;
      --border: #1E293B;
      --border-hover: #334155;
      --shadow-sm: 0 2px 4px rgba(0,0,0,0.4);
      --shadow-md: 0 8px 24px rgba(0,0,0,0.5);
      --shadow-lg: 0 20px 40px rgba(0,0,0,0.6);
      --badge-bg: rgba(16, 185, 129, 0.15);
      --badge-color: #34D399;
      --hero-glow: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, rgba(9, 13, 22, 0) 70%);
      --code-bg: #030712;
      --code-color: #6EE7B7;
    }

    /* ==================== GLOBAL BASE & RESET ==================== */
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      -webkit-tap-highlight-color: transparent;
    }

    html {
      scroll-behavior: smooth;
      scroll-padding-top: 85px;
      font-size: 16px;
    }

    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background-color: var(--bg-main);
      color: var(--text-main);
      line-height: 1.6;
      overflow-x: hidden;
      width: 100%;
      min-height: 100vh;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    h1, h2, h3, h4, h5, h6 {
      font-family: 'Outfit', sans-serif;
      font-weight: 700;
      color: var(--text-main);
      line-height: 1.25;
    }

    a {
      text-decoration: none;
      color: inherit;
      transition: var(--transition);
    }

    img {
      max-width: 100%;
      height: auto;
      display: block;
    }

    .container {
      width: 100%;
      max-width: 1240px;
      margin: 0 auto;
      padding: 0 clamp(1rem, 3.5vw, 2rem);
    }

    .gradient-text {
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      display: inline-block;
    }

    /* ==================== BUTTONS ==================== */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 10px 22px;
      font-size: clamp(0.85rem, 1.5vw, 0.92rem);
      font-weight: 600;
      border-radius: var(--radius-sm);
      cursor: pointer;
      border: 1px solid transparent;
      transition: var(--transition);
      text-align: center;
      white-space: nowrap;
      min-height: 42px;
    }

    .btn-sm {
      padding: 8px 16px;
      font-size: 0.84rem;
      min-height: 38px;
    }

    .btn-lg {
      padding: 13px 26px;
      font-size: clamp(0.92rem, 1.6vw, 1.02rem);
      border-radius: var(--radius-md);
      min-height: 48px;
    }

    .btn-primary {
      background: var(--primary);
      color: #FFFFFF;
      box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
    }

    .btn-primary:hover {
      background: var(--primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(5, 150, 105, 0.45);
    }

    .btn-outline {
      background: var(--bg-card);
      color: var(--text-main);
      border: 1px solid var(--border);
    }

    .btn-outline:hover {
      background: var(--bg-card-subtle);
      border-color: var(--primary);
      color: var(--primary);
      transform: translateY(-2px);
    }

    .theme-toggle-btn {
      width: 40px;
      height: 40px;
      min-width: 40px;
      border-radius: 50%;
      background: var(--bg-card-subtle);
      color: var(--text-main);
      border: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      font-size: 1.05rem;
    }

    .theme-toggle-btn:hover {
      background: var(--primary);
      color: #FFFFFF;
      border-color: var(--primary);
      transform: rotate(15deg);
    }

    /* ==================== NAVBAR ==================== */
    .header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      background: rgba(var(--header-bg), 0.92);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border);
      transition: var(--transition);
    }

    .navbar {
      height: 72px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .brand-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-shrink: 0;
    }

    .brand-logo-img {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      object-fit: cover;
      border: 2px solid var(--primary);
    }

    .brand-text h1 {
      font-size: clamp(1.05rem, 2.5vw, 1.22rem);
      font-weight: 800;
      letter-spacing: -0.5px;
    }

    .brand-text p {
      font-size: 0.7rem;
      color: var(--text-muted);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      list-style: none;
    }

    .nav-link {
      font-size: 0.92rem;
      font-weight: 600;
      color: var(--text-secondary);
      position: relative;
      padding: 6px 0;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .nav-link:hover, .nav-link.active {
      color: var(--primary);
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }

    .nav-toggle {
      display: none;
      width: 40px;
      height: 40px;
      min-width: 40px;
      background: var(--bg-card-subtle);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      color: var(--text-main);
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      cursor: pointer;
      transition: var(--transition);
    }

    .mobile-drawer-auth {
      display: none;
    }

    /* ==================== HERO SECTION ==================== */
    .hero {
      position: relative;
      padding: clamp(100px, 14vw, 140px) 0 clamp(40px, 7vw, 80px);
      overflow: hidden;
    }

    .hero-glow {
      position: absolute;
      width: min(700px, 90vw);
      height: min(700px, 90vw);
      background: var(--hero-glow);
      top: 40%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 0;
      pointer-events: none;
    }

    .hero-grid {
      position: relative;
      z-index: 1;
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: clamp(2rem, 4vw, 3.5rem);
      align-items: center;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 14px;
      background: var(--badge-bg);
      color: var(--badge-color);
      border: 1px solid rgba(16, 185, 129, 0.3);
      border-radius: 30px;
      font-size: clamp(0.72rem, 2vw, 0.82rem);
      font-weight: 700;
      margin-bottom: 1.25rem;
      max-width: 100%;
    }

    .pulse-dot {
      width: 8px;
      height: 8px;
      min-width: 8px;
      border-radius: 50%;
      background: var(--primary);
      box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
      70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
      100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .hero-title {
      font-size: clamp(1.9rem, 4.2vw, 3.2rem);
      font-weight: 800;
      letter-spacing: -0.8px;
      margin-bottom: 1rem;
      word-break: break-word;
    }

    .hero-desc {
      font-size: clamp(0.92rem, 1.8vw, 1.05rem);
      color: var(--text-secondary);
      line-height: 1.7;
      margin-bottom: 1.75rem;
    }

    .hero-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 2.25rem;
    }

    .hero-stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border);
    }

    .stat-item {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 10px 12px;
      text-align: center;
    }

    .stat-item h3 {
      font-size: clamp(1.2rem, 2.5vw, 1.5rem);
      color: var(--primary);
      font-weight: 800;
    }

    .stat-item p {
      font-size: clamp(0.72rem, 1.5vw, 0.8rem);
      color: var(--text-muted);
      font-weight: 600;
      white-space: nowrap;
    }

    /* Hero Visual Card Stack */
    .hero-visual {
      position: relative;
      width: 100%;
    }

    .hero-terminal-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-xl);
      padding: clamp(1.25rem, 3vw, 1.75rem);
      box-shadow: var(--shadow-lg);
      position: relative;
      width: 100%;
    }

    .terminal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid var(--border);
    }

    .terminal-dots {
      display: flex;
      gap: 6px;
    }

    .dot {
      width: 11px;
      height: 11px;
      border-radius: 50%;
    }
    .dot-red { background: #EF4444; }
    .dot-yellow { background: #F59E0B; }
    .dot-green { background: #10B981; }

    .terminal-badge {
      font-size: 0.75rem;
      font-weight: 700;
      padding: 3px 10px;
      background: var(--badge-bg);
      color: var(--badge-color);
      border-radius: 12px;
      white-space: nowrap;
    }

    .code-box {
      background: var(--code-bg);
      color: var(--code-color);
      border-radius: var(--radius-sm);
      padding: 0.85rem 1rem;
      font-family: 'Consolas', 'Fira Code', 'Courier New', monospace;
      font-size: clamp(0.75rem, 1.8vw, 0.82rem);
      line-height: 1.6;
      margin-bottom: 1.25rem;
      word-break: break-all;
      white-space: pre-wrap;
      overflow-x: auto;
    }

    .hero-live-rows {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .live-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding: 9px 12px;
      background: var(--bg-card-subtle);
      border-radius: var(--radius-sm);
      font-size: clamp(0.78rem, 1.8vw, 0.85rem);
    }

    .live-row-label {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
    }

    .live-status {
      display: flex;
      align-items: center;
      gap: 5px;
      font-weight: 700;
      color: var(--primary);
      white-space: nowrap;
      font-size: 0.78rem;
    }

    /* ==================== SECTIONS ==================== */
    .section {
      padding: clamp(45px, 6vw, 85px) 0;
      position: relative;
      width: 100%;
    }

    .section-header {
      text-align: center;
      max-width: 680px;
      margin: 0 auto clamp(2rem, 4vw, 3rem);
    }

    .section-tag {
      display: inline-block;
      padding: 5px 14px;
      background: var(--badge-bg);
      color: var(--badge-color);
      font-size: 0.78rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      border-radius: 20px;
      margin-bottom: 8px;
    }

    .section-title {
      font-size: clamp(1.6rem, 3.2vw, 2.4rem);
      margin-bottom: 0.75rem;
      word-break: break-word;
    }

    .section-subtitle {
      font-size: clamp(0.88rem, 1.8vw, 0.98rem);
      color: var(--text-muted);
      line-height: 1.6;
    }

    /* ==================== FEATURES GRID ==================== */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
      gap: clamp(1rem, 2.5vw, 1.75rem);
    }

    .feature-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: clamp(1.35rem, 3vw, 1.85rem);
      transition: var(--transition);
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .feature-card:hover {
      transform: translateY(-4px);
      border-color: var(--primary);
      box-shadow: var(--shadow-md);
    }

    .feature-icon-wrap {
      width: 48px;
      height: 48px;
      border-radius: var(--radius-md);
      background: var(--badge-bg);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.35rem;
      margin-bottom: 4px;
    }

    .feature-title {
      font-size: clamp(1.1rem, 2vw, 1.25rem);
      font-weight: 700;
    }

    .feature-desc {
      font-size: clamp(0.85rem, 1.6vw, 0.9rem);
      color: var(--text-secondary);
      line-height: 1.65;
    }

    /* ==================== PORTS GRID ==================== */
    .ports-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr));
      gap: clamp(1rem, 2vw, 1.5rem);
    }

    .port-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: clamp(1.25rem, 2.5vw, 1.65rem);
      transition: var(--transition);
      position: relative;
    }

    .port-card:hover {
      transform: translateY(-4px);
      border-color: var(--primary);
      box-shadow: var(--shadow-md);
    }

    .port-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
    }

    .port-icon {
      width: 44px;
      height: 44px;
      border-radius: var(--radius-md);
      background: var(--badge-bg);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
    }

    .port-number {
      font-family: monospace;
      font-size: 0.78rem;
      font-weight: 700;
      padding: 4px 9px;
      background: var(--bg-card-subtle);
      border-radius: 6px;
      color: var(--text-muted);
      border: 1px solid var(--border);
    }

    .port-title {
      font-size: clamp(1.1rem, 2vw, 1.2rem);
      margin-bottom: 6px;
    }

    .port-desc {
      font-size: clamp(0.84rem, 1.5vw, 0.88rem);
      color: var(--text-secondary);
      line-height: 1.6;
    }

    /* ==================== HOW IT WORKS ==================== */
    .steps-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: clamp(1rem, 2.5vw, 2rem);
    }

    .step-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: clamp(1.5rem, 3vw, 2rem) 1.5rem;
      text-align: center;
      position: relative;
    }

    .step-number {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: var(--primary);
      color: #FFFFFF;
      font-weight: 800;
      font-size: 1.15rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.1rem;
      box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);
    }

    .step-title {
      font-size: clamp(1.05rem, 2vw, 1.18rem);
      margin-bottom: 8px;
    }

    .step-desc {
      font-size: clamp(0.84rem, 1.5vw, 0.88rem);
      color: var(--text-secondary);
      line-height: 1.6;
    }

    /* ==================== CTA BANNER ==================== */
    .cta-banner {
      background: var(--primary-gradient);
      border-radius: var(--radius-xl);
      padding: clamp(32px, 5vw, 56px) clamp(20px, 4vw, 44px);
      color: #FFFFFF;
      text-align: center;
      box-shadow: 0 20px 40px rgba(5, 150, 105, 0.25);
    }

    .cta-banner h3 {
      font-size: clamp(1.6rem, 3.5vw, 2.3rem);
      color: #FFFFFF;
      margin-bottom: 0.85rem;
      word-break: break-word;
    }

    .cta-banner p {
      font-size: clamp(0.92rem, 1.8vw, 1.05rem);
      color: rgba(255, 255, 255, 0.92);
      max-width: 650px;
      margin: 0 auto 1.75rem;
      line-height: 1.6;
    }

    .cta-banner-buttons {
      display: flex;
      gap: 12px;
      justify-content: center;
      flex-wrap: wrap;
    }

    /* ==================== FOOTER ==================== */
    .footer {
      background: var(--bg-card);
      border-top: 1px solid var(--border);
      padding: clamp(40px, 5vw, 60px) 0 30px;
      width: 100%;
    }

    .footer-grid {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr;
      gap: clamp(1.75rem, 3vw, 3rem);
      margin-bottom: 35px;
    }

    .footer-links {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .footer-links a {
      font-size: 0.88rem;
      color: var(--text-secondary);
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .footer-links a:hover {
      color: var(--primary);
      padding-left: 4px;
    }

    .footer-bottom {
      padding-top: 22px;
      border-top: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.82rem;
      color: var(--text-muted);
      flex-wrap: wrap;
      gap: 10px;
    }

    /* ==================== FLOATING WA ==================== */
    .floating-wa-btn {
      position: fixed;
      bottom: calc(18px + env(safe-area-inset-bottom, 0px));
      right: 16px;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: #25D366;
      color: #FFFFFF;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
      z-index: 999;
      transition: var(--transition);
    }

    .floating-wa-btn:hover {
      transform: scale(1.08);
      box-shadow: 0 10px 25px rgba(37, 211, 102, 0.6);
    }

    /* ==================== RESPONSIVE BREAKPOINTS ==================== */
    /* Tablet & Medium Screen (<= 1024px) */
    @media (max-width: 1024px) {
      .hero-grid {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 2.25rem;
      }
      .hero-badge {
        margin: 0 auto 1.25rem;
      }
      .hero-buttons {
        justify-content: center;
      }
      .hero-stats {
        grid-template-columns: repeat(4, 1fr);
      }
      .steps-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
      }
      .footer-grid {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
      }
    }

    /* Small Tablet & Large Mobile (<= 768px) */
    @media (max-width: 768px) {
      .navbar {
        height: 64px;
      }
      .nav-links {
        position: fixed;
        top: 64px;
        left: -100%;
        width: 100%;
        height: calc(100vh - 64px);
        background: var(--bg-card);
        flex-direction: column;
        align-items: stretch;
        padding: 1.5rem 1.25rem;
        gap: 1rem;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-bottom: 1px solid var(--border);
        box-shadow: var(--shadow-lg);
        overflow-y: auto;
        z-index: 999;
      }
      .nav-links.open {
        left: 0;
      }
      .nav-link {
        font-size: 1.05rem;
        padding: 12px 14px;
        border-radius: var(--radius-sm);
        background: var(--bg-card-subtle);
        border: 1px solid var(--border);
      }
      .nav-toggle {
        display: flex;
      }
      /* Hide desktop navbar auth buttons on mobile header, show in drawer */
      .desktop-auth-btn {
        display: none !important;
      }
      .mobile-drawer-auth {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 10px;
        padding-top: 15px;
        border-top: 1px solid var(--border);
      }
      .mobile-drawer-auth .btn {
        width: 100%;
      }
      .hero-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
      }
      .steps-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      .footer-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
      }
      .footer-bottom {
        flex-direction: column;
        text-align: center;
      }
    }

    /* Small Android / iPhone (<= 480px) */
    @media (max-width: 480px) {
      .hero-buttons {
        flex-direction: column;
        width: 100%;
      }
      .hero-buttons .btn {
        width: 100%;
      }
      .cta-banner-buttons {
        flex-direction: column;
        width: 100%;
      }
      .cta-banner-buttons .btn {
        width: 100%;
      }
      .live-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
      }
    }
  </style>
</head>
<body>

  <!-- ==================== HEADER ==================== -->
  <header class="header" id="header">
    <div class="container">
      <nav class="navbar">
        <a href="{{ url('/') }}" class="brand-logo">
          <img src="{{ asset('images/logo-vpn-shield.png?v=20260824_v1') }}" alt="Logo KURTECH" class="brand-logo-img">
          <div class="brand-text">
            <h1>KURTECH VPN</h1>
            <p>Multi-Tenant Tunnel</p>
          </div>
        </a>

        <ul class="nav-links" id="navLinks">
          <li><a href="#fitur" class="nav-link"><i class="fa-solid fa-star"></i> Fitur Unggulan</a></li>
          <li><a href="#ports" class="nav-link"><i class="fa-solid fa-network-wired"></i> Layanan & Port</a></li>
          <li><a href="#panduan" class="nav-link"><i class="fa-solid fa-list-check"></i> Cara Kerja</a></li>
          <li><a href="https://kurtech.my.id" class="nav-link" target="_blank"><i class="fa-solid fa-globe"></i> Website Utama</a></li>

          <!-- Mobile Only Auth CTAs inside Drawer -->
          <li class="mobile-drawer-auth">
            <a href="{{ route('login') }}" class="btn btn-outline btn-lg">
              <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Portal
            </a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
              <i class="fa-solid fa-user-plus"></i> Daftar Akun Baru
            </a>
          </li>
        </ul>

        <div class="nav-actions">
          <button class="theme-toggle-btn" id="themeToggle" title="Ganti Mode Terang/Gelap" aria-label="Toggle Theme">
            <i class="fa-solid fa-moon"></i>
          </button>
          <a href="{{ route('login') }}" class="btn btn-outline btn-sm desktop-auth-btn">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk
          </a>
          <a href="{{ route('register') }}" class="btn btn-primary btn-sm desktop-auth-btn">
            <i class="fa-solid fa-user-plus"></i> Daftar
          </a>
          <button class="nav-toggle" id="navToggle" aria-label="Toggle Navigation">
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>
      </nav>
    </div>
  </header>

  <!-- ==================== HERO SECTION ==================== -->
  <section class="hero" id="beranda">
    <div class="hero-glow"></div>
    <div class="container">
      <div class="hero-grid">
        <div>
          <div class="hero-badge">
            <span class="pulse-dot"></span>
            <i class="fa-solid fa-bolt" style="color: var(--primary); font-size: 0.85rem;"></i> <span>VPN REMOTE MULTI-PORT ROUTEROS</span>
          </div>
          <h2 class="hero-title">
            Remote Perangkat Anda <span class="gradient-text">Tanpa Perlu IP Public.</span>
          </h2>
          <p class="hero-desc">
            Akses router MikroTik (WinBox, Mikhmon, WebFig), Server ACS (GenieACS), OLT, hingga CCTV dari mana saja secara aman, stabil, dan berkecepatan tinggi tanpa hambatan NAT operator.
          </p>
          <div class="hero-buttons">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
              <i class="fa-solid fa-bolt"></i> Mulai Berlangganan Sekarang
            </a>
            <a href="#ports" class="btn btn-outline btn-lg">
              <i class="fa-solid fa-network-wired"></i> Lihat Daftar Port
            </a>
          </div>

          <div class="hero-stats">
            <div class="stat-item">
              <h3>99.9%</h3>
              <p>Server Uptime</p>
            </div>
            <div class="stat-item">
              <h3>&lt; 5ms</h3>
              <p>Tunnel Latency</p>
            </div>
            <div class="stat-item">
              <h3>10+ Port</h3>
              <p>Multi-Service</p>
            </div>
            <div class="stat-item">
              <h3>24/7</h3>
              <p>Monitoring Aktif</p>
            </div>
          </div>
        </div>

        <div class="hero-visual">
          <div class="hero-terminal-card">
            <div class="terminal-header">
              <div class="terminal-dots">
                <span class="dot dot-red"></span>
                <span class="dot dot-yellow"></span>
                <span class="dot dot-green"></span>
              </div>
              <span class="terminal-badge"><i class="fa-solid fa-shield-halved"></i> Active Cloud Tunnel</span>
            </div>

            <div class="code-box">
              <span style="color: #9CA3AF;"># 1-Line Script Setup MikroTik (L2TP/SSTP)</span><br>
              <span style="color: #FBBF24;">/interface l2tp-client add</span> connect-to=<span style="color: #34D399;">vpn.kurtech.my.id</span> user=tenant_demo password=kurtech-safe
            </div>

            <div class="hero-live-rows">
              <div class="live-row">
                <div class="live-row-label">
                  <i class="fa-solid fa-desktop" style="color: var(--primary);"></i>
                  <span>WinBox (Port 8291)</span>
                </div>
                <span class="live-status"><i class="fa-solid fa-circle" style="font-size: 0.55rem;"></i> Terhubung</span>
              </div>
              <div class="live-row">
                <div class="live-row-label">
                  <i class="fa-solid fa-globe" style="color: var(--accent-cyan);"></i>
                  <span>Mikhmon Web Hotspot</span>
                </div>
                <span class="live-status"><i class="fa-solid fa-circle" style="font-size: 0.55rem;"></i> Terhubung</span>
              </div>
              <div class="live-row">
                <div class="live-row-label">
                  <i class="fa-solid fa-server" style="color: #F59E0B;"></i>
                  <span>Server ACS / GenieACS</span>
                </div>
                <span class="live-status"><i class="fa-solid fa-circle" style="font-size: 0.55rem;"></i> Terhubung</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== FITUR UNGGULAN SECTION ==================== -->
  <section class="section" id="fitur" style="background: var(--bg-card-subtle);">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Keunggulan Layanan</span>
        <h3 class="section-title">Mengapa Memilih KURTECH VPN Remote?</h3>
        <p class="section-subtitle">Didesain khusus untuk teknisi jaringan, pengelola RT/RW Net, dan IT Administrator yang menginginkan konektivitas stabil dan handal.</p>
      </div>

      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon-wrap">
            <i class="fa-solid fa-gauge-high"></i>
          </div>
          <h4 class="feature-title">Koneksi Super Cepat & Low Latency</h4>
          <p class="feature-desc">Server Cloud CHR dioptimasi khusus untuk tunnel routing terpendek, memastikan latensi rendah dan responsif saat remote WinBox, SSH, RDP, atau WebFig.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon-wrap" style="color: var(--accent-cyan); background: rgba(14, 165, 233, 0.15);">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <h4 class="feature-title">Keamanan Enkripsi End-to-End</h4>
          <p class="feature-desc">Jalur tunnel dienkripsi penuh menggunakan standar VPN terkini (SSTP, L2TP, IPSec). Data traffic dan konfigurasi router Anda aman dari intaian pihak luar.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon-wrap" style="color: #F59E0B; background: rgba(245, 158, 11, 0.15);">
            <i class="fa-solid fa-server"></i>
          </div>
          <h4 class="feature-title">Uptime 99.9% Cloud Server</h4>
          <p class="feature-desc">Infrastruktur cloud andal dengan sistem auto-healing memastikan layanan VPN Remote selalu aktif dan siap diakses kapan pun Anda membutuhkannya.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon-wrap" style="color: #8B5CF6; background: rgba(139, 92, 246, 0.15);">
            <i class="fa-solid fa-terminal"></i>
          </div>
          <h4 class="feature-title">Zero Config (1-Baris Script)</h4>
          <p class="feature-desc">Tidak perlu setting firewall rumit atau port forwarding di modem ISP. Cukup salin 1 baris script ke terminal WinBox, router langsung terhubung.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon-wrap" style="color: #EC4899; background: rgba(236, 72, 153, 0.15);">
            <i class="fa-solid fa-users-gear"></i>
          </div>
          <h4 class="feature-title">Dashboard Multi-Tenant</h4>
          <p class="feature-desc">Kelola banyak router cabang, pantau status traffic dan uptime router secara live dari satu panel dashboard yang terintegrasi.</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon-wrap" style="color: #10B981; background: rgba(16, 185, 129, 0.15);">
            <i class="fa-brands fa-whatsapp"></i>
          </div>
          <h4 class="feature-title">Integrasi WhatsApp Support</h4>
          <p class="feature-desc">Didukung tim teknisi KURTECH berpengalaman di Tambun Selatan yang siap membantu konsultasi topologi dan troubleshoot jaringan.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== SUPPORTED SERVICES & PORTS ==================== -->
  <section class="section" id="ports">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Kompatibilitas Layanan</span>
        <h3 class="section-title">Akses Semua Port & Service Sekaligus</h3>
        <p class="section-subtitle">Satu akun VPN Remote KURTECH mendukung berbagai protokol manajemen jaringan penting.</p>
      </div>

      <div class="ports-grid">
        <div class="port-card">
          <div class="port-top">
            <div class="port-icon"><i class="fa-solid fa-sliders"></i></div>
            <span class="port-number">Port 8291</span>
          </div>
          <h4 class="port-title">WinBox MikroTik</h4>
          <p class="port-desc">Akses GUI WinBox secara langsung dari laptop/PC di luar jaringan lokal tanpa perlu login teamviewer atau anydesk.</p>
        </div>

        <div class="port-card">
          <div class="port-top">
            <div class="port-icon"><i class="fa-solid fa-ticket"></i></div>
            <span class="port-number">Port 80 / 8080</span>
          </div>
          <h4 class="port-title">Mikhmon Hotspot</h4>
          <p class="port-desc">Buka dashboard Mikhmon dari HP atau browser luar untuk cetak voucher hotspot kapan saja dan di mana saja.</p>
        </div>

        <div class="port-card">
          <div class="port-top">
            <div class="port-icon"><i class="fa-solid fa-window-maximize"></i></div>
            <span class="port-number">Port 80 / 443</span>
          </div>
          <h4 class="port-title">WebFig RouterOS</h4>
          <p class="port-desc">Manajemen web interface MikroTik langsung dari peramban web modern dengan enkripsi SSL/TLS aman.</p>
        </div>

        <div class="port-card">
          <div class="port-top">
            <div class="port-icon"><i class="fa-solid fa-server"></i></div>
            <span class="port-number">Port 7547 / 3000</span>
          </div>
          <h4 class="port-title">GenieACS (TR-069)</h4>
          <p class="port-desc">Koneksikan server ACS lokal ke router ONT client untuk otomatisasi provisioning modem dan monitoring redaman FO.</p>
        </div>

        <div class="port-card">
          <div class="port-top">
            <div class="port-icon"><i class="fa-solid fa-terminal"></i></div>
            <span class="port-number">Port 22 / 23</span>
          </div>
          <h4 class="port-title">SSH & Telnet Console</h4>
          <p class="port-desc">Akses CLI Terminal MikroTik, server Linux, atau switch manageable menggunakan PuTTY / Termius jarak jauh.</p>
        </div>

        <div class="port-card">
          <div class="port-top">
            <div class="port-icon"><i class="fa-solid fa-video"></i></div>
            <span class="port-number">Port 554 / 8000</span>
          </div>
          <h4 class="port-title">Remote OLT & CCTV</h4>
          <p class="port-desc">Monitoring OLT EPON/GPON, switch distribusi tiang, serta streaming DVR/NVR CCTV workshop & rumah.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== HOW IT WORKS ==================== -->
  <section class="section" id="panduan" style="background: var(--bg-card-subtle);">
    <div class="container">
      <div class="section-header">
        <span class="section-tag">Mudah & Cepat</span>
        <h3 class="section-title">Hanya 3 Langkah Sederhana</h3>
        <p class="section-subtitle">Tidak perlu setting firewall rumit atau port forwarding di modem ISP.</p>
      </div>

      <div class="steps-grid">
        <div class="step-card">
          <div class="step-number">1</div>
          <h4 class="step-title">Daftar Akun Portal</h4>
          <p class="step-desc">Buat akun Anda secara instan di portal KURTECH VPN Remote dan pilih paket / port yang diinginkan.</p>
        </div>

        <div class="step-card">
          <div class="step-number">2</div>
          <h4 class="step-title">Copy Script 1-Baris</h4>
          <p class="step-desc">Salin script konfigurasi SSTP / L2TP yang sudah di-generate otomatis dari dashboard ke Terminal WinBox.</p>
        </div>

        <div class="step-card">
          <div class="step-number">3</div>
          <h4 class="step-title">Langsung Terhubung!</h4>
          <p class="step-desc">Gunakan alamat domain dan port yang diberikan untuk membuka WinBox, Mikhmon, atau WebFig dari mana saja.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== CTA BANNER ==================== -->
  <section class="section">
    <div class="container">
      <div class="cta-banner">
        <h3>Siap Mengelola Jaringan Anda Dari Mana Saja?</h3>
        <p>Bergabunglah dengan ratusan pengelola RT/RW Net dan teknisi IT yang mempercayakan koneksi remote kepada KURTECH VPN.</p>
        <div class="cta-banner-buttons">
          <a href="{{ route('register') }}" class="btn btn-outline btn-lg" style="background: #FFFFFF; color: var(--primary); border: none;">
            <i class="fa-solid fa-user-plus"></i> Buat Akun Sekarang
          </a>
          <a href="https://wa.me/6283826164767?text=Halo%20KURTECH,%20saya%20tertarik%20dengan%20layanan%20VPN%20Remote." target="_blank" class="btn btn-outline btn-lg" style="background: rgba(0,0,0,0.2); color: #FFFFFF; border-color: rgba(255,255,255,0.4);">
            <i class="fa-brands fa-whatsapp"></i> Tanya Teknisi
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- ==================== FOOTER ==================== -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="brand-logo" style="margin-bottom: 12px;">
            <img src="{{ asset('images/logo-vpn-shield.png?v=20260824_v1') }}" alt="Logo KURTECH" class="brand-logo-img">
            <div class="brand-text">
              <h1>KURTECH VPN</h1>
              <p>Solusi Server & Jaringan Terpercaya</p>
            </div>
          </div>
          <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.6;">
            Bagian dari ekosistem digital <strong>KURTECH</strong>. Melayani solusi terpadu jaringan RT/RW Net, server sekolah, rakit PC, hingga perbaikan komputer di Tambun Selatan, Bekasi.
          </p>
        </div>

        <div>
          <h5 style="margin-bottom: 12px; font-size: 1rem;">Navigasi Cepat</h5>
          <ul class="footer-links">
            <li><a href="#fitur"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem; color: var(--primary);"></i> Fitur Unggulan</a></li>
            <li><a href="#ports"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem; color: var(--primary);"></i> Daftar Layanan Port</a></li>
            <li><a href="#panduan"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem; color: var(--primary);"></i> Cara Pemasangan</a></li>
            <li><a href="{{ route('login') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem; color: var(--primary);"></i> Masuk Dashboard Client</a></li>
            <li><a href="{{ route('register') }}"><i class="fa-solid fa-chevron-right" style="font-size: 0.75rem; color: var(--primary);"></i> Registrasi Akun Baru</a></li>
          </ul>
        </div>

        <div>
          <h5 style="margin-bottom: 12px; font-size: 1rem;">Ekosistem KURTECH</h5>
          <ul class="footer-links">
            <li><a href="https://kurtech.my.id" target="_blank"><i class="fa-solid fa-globe"></i> Website Resmi kurtech.my.id</a></li>
            <li><a href="https://wa.me/6283826164767" target="_blank"><i class="fa-brands fa-whatsapp"></i> WhatsApp Support: 0838-2616-4767</a></li>
            <li><span style="color: var(--text-muted); font-size: 0.85rem;"><i class="fa-solid fa-location-dot"></i> Tambun Selatan, Kab. Bekasi</span></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} KURTECH (vpnremote.kurtech.my.id). All rights reserved.</p>
        <p>Ditenagai oleh Infrastruktur Cloud Tunnel High Performance</p>
      </div>
    </div>
  </footer>

  <!-- ==================== FLOATING WHATSAPP BUTTON ==================== -->
  <a href="https://wa.me/6283826164767?text=Halo%20KURTECH,%20saya%20ingin%20tanya%20mengenai%20VPN%20Remote." target="_blank" class="floating-wa-btn" title="Chat WhatsApp Teknisi">
    <i class="fa-brands fa-whatsapp"></i>
  </a>

  <!-- ==================== JAVASCRIPT ==================== -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // 1. Dark / Light Mode Switcher
      const html = document.documentElement;
      const themeToggle = document.getElementById('themeToggle');
      const savedTheme = localStorage.getItem('vpn_theme') || localStorage.getItem('app-theme') || 'light';

      function setTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('vpn_theme', theme);
        localStorage.setItem('app-theme', theme);
        if (themeToggle) {
          themeToggle.innerHTML = theme === 'dark' ? '<i class="fa-solid fa-sun" style="color: #FBBF24;"></i>' : '<i class="fa-solid fa-moon"></i>';
        }
      }

      setTheme(savedTheme);

      if (themeToggle) {
        themeToggle.addEventListener('click', () => {
          const currentTheme = html.getAttribute('data-theme');
          const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
          setTheme(newTheme);
        });
      }

      // 2. Smooth Scroll for all internal anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
          const targetId = this.getAttribute('href');
          if (targetId && targetId !== '#') {
            const targetEl = document.querySelector(targetId);
            if (targetEl) {
              e.preventDefault();
              const headerOffset = 76;
              const elementPosition = targetEl.getBoundingClientRect().top;
              const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
              window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
              });
            }
          }
        });
      });

      // 3. Mobile Nav Drawer Toggle & Auto Close
      const navToggle = document.getElementById('navToggle');
      const navLinks = document.getElementById('navLinks');

      if (navToggle && navLinks) {
        navToggle.addEventListener('click', (e) => {
          e.stopPropagation();
          navLinks.classList.toggle('open');
          const icon = navToggle.querySelector('i');
          if (navLinks.classList.contains('open')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-xmark');
            document.body.style.overflow = 'hidden';
          } else {
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars');
            document.body.style.overflow = '';
          }
        });

        // Close drawer when clicking any link
        navLinks.querySelectorAll('a').forEach(link => {
          link.addEventListener('click', () => {
            navLinks.classList.remove('open');
            document.body.style.overflow = '';
            const icon = navToggle.querySelector('i');
            if (icon) {
              icon.classList.remove('fa-xmark');
              icon.classList.add('fa-bars');
            }
          });
        });

        // Close drawer when clicking outside
        document.addEventListener('click', (e) => {
          if (!navLinks.contains(e.target) && !navToggle.contains(e.target) && navLinks.classList.contains('open')) {
            navLinks.classList.remove('open');
            document.body.style.overflow = '';
            const icon = navToggle.querySelector('i');
            if (icon) {
              icon.classList.remove('fa-xmark');
              icon.classList.add('fa-bars');
            }
          }
        });
      }
    });
  </script>
</body>
</html>




