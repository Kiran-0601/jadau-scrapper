<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Live Rates</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background: #fcf7f1;
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
      color: #3a2819;
    }

    .container {
      max-width: 1400px;
      margin: 40px auto;
      padding: 0 24px;
    }

    h1 {
      font-family: 'Playfair Display', serif;
      font-size: 40px;
      text-align: center;
      color: #4d2d14;
      margin-bottom: 12px;
    }

    .section-title {
      font-size: 26px;
      font-weight: 700;
      color: #5a3619;
      margin: 50px 0 20px;
      padding-left: 14px;
    }

    .card-grid-3 {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
      gap: 32px;
    }

    .card-grid-4 {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 32px;
    }

    .rate-card {
      background: #fffefc;
      border-left: 6px solid #d6a450;
      border-radius: 14px;
      padding: 28px 22px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    }

    .product-name {
      font-family: 'Poppins', sans-serif;
      font-size: 21px;
      color: #2e1c0f;
      margin-bottom: 34px;
      font-weight: 600;
    }

    .rate-row {
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      font-size: 20px;
    }

    .rate-label {
      font-weight: 700;
      color: #7a5939;
    }

    .rate-value {
      font-weight: 700;
      font-size: 18px;
      color: #1f1208;
    }

    .spinner, .error {
      text-align: center;
      padding: 20px;
      font-size: 17px;
    }

    .error {
      color: red;
    }

    @media (max-width: 768px) {
      .rate-row {
        font-size: 16px;
        flex-direction: column;
        gap: 4px;
      }
      .rate-value {
        font-size: 17px;
      }
    }
    .spinner {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 4px solid #f3f3f3;
      border-top: 4px solid #b8860b; /* Gold color */
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 20px auto;
      text-align: center;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .spinner-overlay {
      display: flex;
      justify-content: center;  /* horizontal center */
      align-items: center;      /* vertical center */
      height: 200px;            /* adjust height as needed */
      position: relative;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Live Gold & Silver Rates</h1>
    <div id="rate-container"><div class="spinner-overlay"><div class="spinner"></div></div></div>
  </div>

  <script>
    let lastProductHTML = '', lastSpotHTML = '', lastFutureHTML = '';

    function splitRate(str, char) {
      const match = str.match(/^(\d+)[^\d]*(\d+)$/);
      return match ? { rate: match[1], suffix: `${char} - ${match[2]}` } : { rate: str, suffix: '' };
    }

    async function loadRates() {
      try {
        const res = await fetch("/api/live-rates");
        const data = await res.json();
        if (!data.status) throw new Error(data.error || "Invalid response");

        lastProductHTML = data.productRates.map(r => {
          const m = splitRate(r.m_rate, 'L'), s = splitRate(r.sell, 'H');
          return `
            <div class="rate-card">
              <div class="product-name">${r.product}</div>
              <div class="rate-row"><span class="rate-label">M-Rate</span><span class="rate-value">${m.rate}${m.suffix ? ` (${m.suffix})` : ''}</span></div>
              <div class="rate-row"><span class="rate-label">Premium</span><span class="rate-value">${r.premium}</span></div>
              <div class="rate-row"><span class="rate-label">Sell</span><span class="rate-value">${s.rate}${s.suffix ? ` (${s.suffix})` : ''}</span></div>
            </div>`;
        }).join('');

        lastFutureHTML = data.futureRates.map(r => `
          <div class="rate-card">
            <div class="product-name">${r.product}</div>
            <div class="rate-row"><span class="rate-label">Bid</span><span class="rate-value">${r.bid}</span></div>
            <div class="rate-row"><span class="rate-label">Ask</span><span class="rate-value">${r.ask}</span></div>
            <div class="rate-row"><span class="rate-label">High</span><span class="rate-value">${r.high}</span></div>
            <div class="rate-row"><span class="rate-label">Low</span><span class="rate-value">${r.low}</span></div>
          </div>`).join('');

        lastSpotHTML = data.spotRates.map(r => `
          <div class="rate-card">
            <div class="product-name">${r.product}</div>
            <div class="rate-row"><span class="rate-label">Bid</span><span class="rate-value">${r.bid}</span></div>
            <div class="rate-row"><span class="rate-label">Ask</span><span class="rate-value">${r.ask}</span></div>
            <div class="rate-row"><span class="rate-label">High</span><span class="rate-value">${r.high}</span></div>
            <div class="rate-row"><span class="rate-label">Low</span><span class="rate-value">${r.low}</span></div>
          </div>`).join('');

        updateUI();
      } catch (e) {
        console.error(e);
        updateUI(true, e.message);
      }
    }

    function updateUI(error = false, msg = '') {
      const el = document.getElementById('rate-container');
      if (error && !lastProductHTML) {
        el.innerHTML = `<div class="error">Error: ${msg}</div>`;
        return;
      }
      el.innerHTML = `
        <div class="section-title">Product Rates</div>
        <div class="card-grid-3">${lastProductHTML}</div>
        <div class="section-title">Spot Market Rates</div>
        <div class="card-grid-3">${lastSpotHTML}</div>
        ${lastFutureHTML ? `<div class="section-title">Future Market Rates</div><div class="card-grid-4">${lastFutureHTML}</div>` : ''}
      `;
    }

    loadRates();
    setInterval(loadRates, 15000);
  </script>
  
  <script>
  // Disable right click
  // document.addEventListener('contextmenu', function (e) {
  //   e.preventDefault();
  // });

  // Disable DevTools shortcuts
  document.addEventListener('keydown', function (e) {
    // Block F12
    if (e.key === 'F12') {
      e.preventDefault();
      return false;
    }

    // Block Ctrl+U / Ctrl+S
    if (e.ctrlKey && (e.key === 'u' || e.key === 's')) {
      e.preventDefault();
      return false;
    }

    // Block Ctrl+Shift+K (Firefox DevTools)
    if (e.ctrlKey && e.shiftKey && e.key === 'k') {
      e.preventDefault();
      return false;
    }
  });

  // Optional: Detect if DevTools is open
  (function() {
    const element = new Image();
    Object.defineProperty(element, 'id', {
      get: function() {
        document.location.href = 'about:blank'; // or redirect to another page
      }
    });
  })();
</script>

</body>
</html>
