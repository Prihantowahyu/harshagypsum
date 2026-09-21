/**
 * Harsha Gypsum - Application Logic
 * High-performance vanilla JS with zero external dependencies
 */

document.addEventListener('DOMContentLoaded', () => {
  // State
  let currentCategory = 'all';
  let searchQuery = '';
  let sortBy = 'popular';
  let cart = JSON.parse(localStorage.getItem('gypsum_cart') || '[]');
  let activeModalProduct = null;

  // DOM Elements
  const productsGrid = document.getElementById('productsGrid');
  const searchInput = document.getElementById('searchInput');
  const sortSelect = document.getElementById('sortSelect');
  const filterBtns = document.querySelectorAll('.filter-btn');
  const cartCounter = document.getElementById('cartCounter');
  const cartDrawer = document.getElementById('cartDrawer');
  const cartOverlay = document.getElementById('cartOverlay');
  const cartItemsList = document.getElementById('cartItemsList');
  const cartTotalAmount = document.getElementById('cartTotalAmount');
  
  // Product Detail Modal Elements
  const productModal = document.getElementById('productModal');
  const modalCloseBtn = document.getElementById('modalCloseBtn');
  const modalImage = document.getElementById('modalImage');
  const modalCode = document.getElementById('modalCode');
  const modalTitle = document.getElementById('modalTitle');
  const modalDesc = document.getElementById('modalDesc');
  const modalPrice = document.getElementById('modalPrice');
  const modalPriceBulk = document.getElementById('modalPriceBulk');
  const modalSpecsBody = document.getElementById('modalSpecsBody');
  const modalQtyInput = document.getElementById('modalQtyInput');
  const modalQtyMinus = document.getElementById('modalQtyMinus');
  const modalQtyPlus = document.getElementById('modalQtyPlus');
  const modalBtnAddCart = document.getElementById('modalBtnAddCart');
  const modalBtnOrderWa = document.getElementById('modalBtnOrderWa');

  // Calculator Elements
  const calcRoomLength = document.getElementById('calcRoomLength');
  const calcRoomWidth = document.getElementById('calcRoomWidth');
  const calcProductSelect = document.getElementById('calcProductSelect');
  const calcWasteCheckbox = document.getElementById('calcWasteCheckbox');
  const calcResultPerimeter = document.getElementById('calcResultPerimeter');
  const calcResultSticks = document.getElementById('calcResultSticks');
  const calcResultCompound = document.getElementById('calcResultCompound');
  const calcResultTotalPrice = document.getElementById('calcResultTotalPrice');
  const btnSendCalcWa = document.getElementById('btnSendCalcWa');

  // Mobile Drawer Elements
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileDrawer = document.getElementById('mobileDrawer');
  const drawerCloseBtn = document.getElementById('drawerCloseBtn');
  const drawerOverlay = document.getElementById('drawerOverlay');

  // Initialize
  initProducts();
  initCalculatorOptions();
  updateCartUI();
  calculateGypsumNeeds();

  // =========================================================================
  // Product Rendering & Filtering
  // =========================================================================
  function formatRupiah(number) {
    return 'Rp ' + Number(number).toLocaleString('id-ID');
  }

  function getFilteredProducts() {
    return GYPSUM_PRODUCTS.filter(item => {
      const matchCategory = currentCategory === 'all' || item.category === currentCategory;
      const matchSearch = searchQuery === '' || 
        item.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        item.code.toLowerCase().includes(searchQuery.toLowerCase()) ||
        item.description.toLowerCase().includes(searchQuery.toLowerCase());
      return matchCategory && matchSearch;
    }).sort((a, b) => {
      if (sortBy === 'popular') return (b.sold || 0) - (a.sold || 0);
      if (sortBy === 'price-low') return a.price - b.price;
      if (sortBy === 'price-high') return b.price - a.price;
      if (sortBy === 'name') return a.name.localeCompare(b.name);
      return 0;
    });
  }

  function renderProducts() {
    const products = getFilteredProducts();

    if (products.length === 0) {
      productsGrid.innerHTML = `
        <div class="catalog-empty">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <h3>Motif atau Produk Tidak Ditemukan</h3>
          <p style="color: var(--text-secondary); margin-top: 8px;">Coba gunakan kata kunci lain atau pilih kategori yang berbeda.</p>
        </div>
      `;
      return;
    }

    productsGrid.innerHTML = products.map(product => {
      return `
        <div class="product-card" data-id="${product.id}">
          <div class="product-image-wrap" onclick="window.showProductDetail('${product.id}')">
            <img src="${product.image}" alt="${product.name}" loading="lazy">
            <div class="product-badges">
              ${product.isBestSeller ? '<span class="badge badge-gold">Best Seller</span>' : ''}
              ${product.isPopular ? '<span class="badge badge-green">Terlaris</span>' : ''}
            </div>
            <div class="product-code-pill">${product.code}</div>
          </div>
          <div class="product-info">
            <span class="product-category-label">${product.categoryLabel}</span>
            <h3 class="product-name" title="${product.name}">${product.name}</h3>
            
            <div class="product-dimensions">
              <span>📏 ${product.width}</span>
              <span>⚡ ${product.length}</span>
            </div>

            <div class="product-price-row">
              <div>
                <span class="product-price">${formatRupiah(product.price)}</span>
                <span class="product-unit">/ btg</span>
              </div>
              <span class="product-bulk-hint">Grosir: ${formatRupiah(product.priceBulk)}</span>
            </div>

            <div class="product-card-actions">
              <button class="btn-detail" onclick="window.showProductDetail('${product.id}')">Detail</button>
              <button class="btn-order-wa" onclick="window.quickOrderWA('${product.id}')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                Pesan
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  function initProducts() {
    renderProducts();

    // Category Buttons
    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentCategory = btn.getAttribute('data-category');
        renderProducts();
      });
    });

    // Search input with debounce
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        searchQuery = e.target.value.trim();
        renderProducts();
      }, 200);
    });

    // Sort select
    sortSelect.addEventListener('change', (e) => {
      sortBy = e.target.value;
      renderProducts();
    });
  }

  // =========================================================================
  // Product Detail Modal
  // =========================================================================
  window.showProductDetail = function(productId) {
    const product = GYPSUM_PRODUCTS.find(p => p.id === productId);
    if (!product) return;

    activeModalProduct = product;
    modalImage.src = product.image;
    modalImage.alt = product.name;
    modalCode.textContent = `KODE: ${product.code}`;
    modalTitle.textContent = product.name;
    modalDesc.textContent = product.description;
    modalPrice.textContent = formatRupiah(product.price);
    modalPriceBulk.textContent = `Harga Grosir: ${formatRupiah(product.priceBulk)}/btg (Min. ${product.minBulkQty || 30} batang)`;

    modalSpecsBody.innerHTML = product.specs.map(spec => `
      <tr>
        <td>${spec.label}</td>
        <td>${spec.value}</td>
      </tr>
    `).join('');

    modalQtyInput.value = 10; // Default order batang
    productModal.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  function closeModal() {
    productModal.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
  if (productModal) {
    productModal.addEventListener('click', (e) => {
      if (e.target === productModal) closeModal();
    });
  }

  // Qty adjusters in modal
  modalQtyMinus.addEventListener('click', () => {
    let current = parseInt(modalQtyInput.value) || 1;
    if (current > 1) modalQtyInput.value = current - 1;
  });

  modalQtyPlus.addEventListener('click', () => {
    let current = parseInt(modalQtyInput.value) || 1;
    modalQtyInput.value = current + 1;
  });

  // Modal actions
  modalBtnAddCart.addEventListener('click', () => {
    if (!activeModalProduct) return;
    const qty = parseInt(modalQtyInput.value) || 1;
    addToCart(activeModalProduct, qty);
    closeModal();
    showToast(`Berhasil menambahkan ${qty} btg ${activeModalProduct.name} ke daftar pesanan.`);
  });

  modalBtnOrderWa.addEventListener('click', () => {
    if (!activeModalProduct) return;
    const qty = parseInt(modalQtyInput.value) || 1;
    const isBulk = qty >= (activeModalProduct.minBulkQty || 30);
    const unitPrice = isBulk ? activeModalProduct.priceBulk : activeModalProduct.price;
    const totalPrice = unitPrice * qty;

    const message = `Halo ${WHATSAPP_CONFIG.storeName}, saya ingin memesan list gypsum berikut:\n\n` +
      `*Produk:* ${activeModalProduct.name} (${activeModalProduct.code})\n` +
      `*Jumlah:* ${qty} batang\n` +
      `*Estimasi Harga:* ${formatRupiah(totalPrice)} (${isBulk ? 'Harga Grosir' : 'Harga Ecer'})\n` +
      `*Ukuran:* Lebar ${activeModalProduct.width}, Panjang ${activeModalProduct.length}\n\n` +
      `Mohon info ketersediaan stok dan biaya kirim ke lokasi saya. Terima kasih!`;

    const waUrl = `https://wa.me/${WHATSAPP_CONFIG.phone}?text=${encodeURIComponent(message)}`;
    window.open(waUrl, '_blank');
  });

  // Quick Order button on Card
  window.quickOrderWA = function(productId) {
    const product = GYPSUM_PRODUCTS.find(p => p.id === productId);
    if (!product) return;

    const message = `Halo ${WHATSAPP_CONFIG.storeName}, saya tertarik untuk order list gypsum:\n\n` +
      `*Motif/Kode:* ${product.name} (${product.code})\n` +
      `*Ukuran:* ${product.width} x ${product.length}\n` +
      `*Harga:* ${formatRupiah(product.price)} / btg\n\n` +
      `Bisa tolong info ketersediaan stok & minimal ordernya? Terima kasih!`;

    const waUrl = `https://wa.me/${WHATSAPP_CONFIG.phone}?text=${encodeURIComponent(message)}`;
    window.open(waUrl, '_blank');
  };

  // =========================================================================
  // Order / Inquiry Cart
  // =========================================================================
  function addToCart(product, qty = 1) {
    const existingIndex = cart.findIndex(item => item.id === product.id);
    if (existingIndex > -1) {
      cart[existingIndex].qty += qty;
    } else {
      cart.push({
        id: product.id,
        code: product.code,
        name: product.name,
        price: product.price,
        priceBulk: product.priceBulk,
        minBulkQty: product.minBulkQty || 30,
        image: product.image,
        qty: qty
      });
    }
    saveCart();
    updateCartUI();
  }

  function saveCart() {
    localStorage.setItem('gypsum_cart', JSON.stringify(cart));
  }

  function updateCartUI() {
    const totalItems = cart.reduce((acc, item) => acc + item.qty, 0);
    cartCounter.textContent = totalItems;

    let grandTotal = 0;

    if (cart.length === 0) {
      cartItemsList.innerHTML = `
        <div style="text-align: center; padding: 40px 10px; color: var(--text-muted);">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px;">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          <p>Daftar pesanan Anda masih kosong.</p>
          <p style="font-size: 0.8rem; margin-top: 4px;">Pilih motif dari katalog dan klik Tambah ke Pesanan.</p>
        </div>
      `;
      cartTotalAmount.textContent = formatRupiah(0);
      return;
    }

    cartItemsList.innerHTML = cart.map((item, index) => {
      const isBulk = item.qty >= (item.minBulkQty || 30);
      const activePrice = isBulk ? item.priceBulk : item.price;
      const subtotal = activePrice * item.qty;
      grandTotal += subtotal;

      return `
        <div class="cart-item">
          <img src="${item.image}" alt="${item.name}" class="cart-item-img">
          <div class="cart-item-info">
            <h4 class="cart-item-name">${item.name} (${item.code})</h4>
            <div class="cart-item-price">${formatRupiah(activePrice)} x ${item.qty} btg = ${formatRupiah(subtotal)}</div>
            <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
              <button style="background: #0E1729; border: 1px solid var(--border-subtle); color: #FFF; width: 24px; height: 24px; border-radius: 4px;" onclick="window.adjustCartQty(${index}, -5)">-</button>
              <span style="font-weight: 700; font-size: 0.85rem;">${item.qty} btg</span>
              <button style="background: #0E1729; border: 1px solid var(--border-subtle); color: #FFF; width: 24px; height: 24px; border-radius: 4px;" onclick="window.adjustCartQty(${index}, 5)">+</button>
            </div>
          </div>
          <button class="cart-item-remove" onclick="window.removeCartItem(${index})" title="Hapus">✕</button>
        </div>
      `;
    }).join('');

    cartTotalAmount.textContent = formatRupiah(grandTotal);
  }

  window.adjustCartQty = function(index, delta) {
    if (!cart[index]) return;
    cart[index].qty += delta;
    if (cart[index].qty <= 0) {
      cart.splice(index, 1);
    }
    saveCart();
    updateCartUI();
  };

  window.removeCartItem = function(index) {
    cart.splice(index, 1);
    saveCart();
    updateCartUI();
    showToast('Item berhasil dihapus dari daftar pesanan.');
  };

  // Open & Close Cart
  const cartToggleBtns = document.querySelectorAll('.btn-cart-toggle, #btnOpenCartMobile');
  cartToggleBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      cartDrawer.classList.add('open');
      cartOverlay.classList.add('active');
    });
  });

  const cartCloseBtn = document.getElementById('cartCloseBtn');
  function closeCart() {
    cartDrawer.classList.remove('open');
    cartOverlay.classList.remove('active');
  }
  if (cartCloseBtn) cartCloseBtn.addEventListener('click', closeCart);
  if (cartOverlay) cartOverlay.addEventListener('click', closeCart);

  // Send Entire Cart to WhatsApp
  const btnCheckoutWA = document.getElementById('btnCheckoutWA');
  if (btnCheckoutWA) {
    btnCheckoutWA.addEventListener('click', () => {
      if (cart.length === 0) {
        showToast('Daftar pesanan Anda masih kosong!');
        return;
      }

      let grandTotal = 0;
      let itemsListText = '';

      cart.forEach((item, i) => {
        const isBulk = item.qty >= (item.minBulkQty || 30);
        const activePrice = isBulk ? item.priceBulk : item.price;
        const subtotal = activePrice * item.qty;
        grandTotal += subtotal;
        itemsListText += `${i + 1}. *${item.name}* (${item.code})\n   Jumlah: ${item.qty} btg @ ${formatRupiah(activePrice)} = ${formatRupiah(subtotal)}\n`;
      });

      const message = `Halo ${WHATSAPP_CONFIG.storeName}, saya ingin order daftar list gypsum berikut:\n\n` +
        itemsListText +
        `\n*Total Estimasi:* ${formatRupiah(grandTotal)}\n\n` +
        `Mohon informasi total berat, ongkir, serta jadwal pengirimannya. Terima kasih!`;

      const waUrl = `https://wa.me/${WHATSAPP_CONFIG.phone}?text=${encodeURIComponent(message)}`;
      window.open(waUrl, '_blank');
    });
  }

  // =========================================================================
  // Smart Room Gypsum Calculator
  // =========================================================================
  function initCalculatorOptions() {
    const listOptions = GYPSUM_PRODUCTS.filter(p => p.category === 'minimalis' || p.category === 'klasik' || p.category === 'shadowline');
    calcProductSelect.innerHTML = listOptions.map(p => {
      return `<option value="${p.id}" data-price="${p.price}" data-length="${p.lengthMeter}">${p.name} (${p.code}) - ${formatRupiah(p.price)}/btg</option>`;
    }).join('');

    [calcRoomLength, calcRoomWidth, calcProductSelect, calcWasteCheckbox].forEach(el => {
      el.addEventListener('input', calculateGypsumNeeds);
      el.addEventListener('change', calculateGypsumNeeds);
    });
  }

  function calculateGypsumNeeds() {
    const length = parseFloat(calcRoomLength.value) || 0;
    const width = parseFloat(calcRoomWidth.value) || 0;
    const selectedOption = calcProductSelect.options[calcProductSelect.selectedIndex];
    
    if (!selectedOption) return;

    const stickLength = parseFloat(selectedOption.getAttribute('data-length')) || 2.1;
    const stickPrice = parseFloat(selectedOption.getAttribute('data-price')) || 15000;
    const addWaste = calcWasteCheckbox.checked;

    // Keliling Ruangan (2 * L + 2 * W)
    const perimeter = (2 * length) + (2 * width);
    calcResultPerimeter.textContent = `${perimeter.toFixed(1)} meter`;

    if (perimeter <= 0) {
      calcResultSticks.textContent = '0 Batang';
      calcResultCompound.textContent = '0 Sak';
      calcResultTotalPrice.textContent = formatRupiah(0);
      return;
    }

    // Effective length with 10% waste tolerance for corner miter cuts
    const effectiveTotalLength = addWaste ? (perimeter * 1.10) : perimeter;
    const totalSticks = Math.ceil(effectiveTotalLength / stickLength);
    calcResultSticks.textContent = `${totalSticks} Batang`;

    // 1 Sak Compound is good for approx 40 meters of moulding
    const compoundSacks = Math.max(1, Math.ceil(perimeter / 40));
    calcResultCompound.textContent = `${compoundSacks} Sak (20kg)`;

    // Total Cost
    const totalCost = totalSticks * stickPrice;
    calcResultTotalPrice.textContent = formatRupiah(totalCost);
  }

  if (btnSendCalcWa) {
    btnSendCalcWa.addEventListener('click', () => {
      const length = parseFloat(calcRoomLength.value) || 0;
      const width = parseFloat(calcRoomWidth.value) || 0;
      const selectedOption = calcProductSelect.options[calcProductSelect.selectedIndex];
      const perimeter = ((2 * length) + (2 * width)).toFixed(1);
      const sticks = calcResultSticks.textContent;
      const compound = calcResultCompound.textContent;
      const totalCost = calcResultTotalPrice.textContent;
      const productName = selectedOption ? selectedOption.text : 'List Profil Gypsum';

      const message = `Halo ${WHATSAPP_CONFIG.storeName}, saya ingin konsultasi kebutuhan gypsum ruangan saya:\n\n` +
        `📐 *Dimensi Ruangan:* ${length} m x ${width} m (Keliling: ${perimeter} m)\n` +
        `🎨 *Motif Pilihan:* ${productName}\n` +
        `📦 *Estimasi Kebutuhan:* ${sticks}\n` +
        `🧱 *Kebutuhan Lem Kompon:* ${compound}\n` +
        `💰 *Estimasi Biaya List:* ${totalCost}\n\n` +
        `Apakah stok motif ini tersedia dan bisa sekalian dengan jasa pemasangannya? Terima kasih!`;

      const waUrl = `https://wa.me/${WHATSAPP_CONFIG.phone}?text=${encodeURIComponent(message)}`;
      window.open(waUrl, '_blank');
    });
  }

  // =========================================================================
  // Mobile Navigation Drawer
  // =========================================================================
  function toggleMobileDrawer(open) {
    if (open) {
      mobileDrawer.classList.add('open');
      drawerOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    } else {
      mobileDrawer.classList.remove('open');
      drawerOverlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', () => toggleMobileDrawer(true));
  if (drawerCloseBtn) drawerCloseBtn.addEventListener('click', () => toggleMobileDrawer(false));
  if (drawerOverlay) drawerOverlay.addEventListener('click', () => toggleMobileDrawer(false));

  const drawerLinks = document.querySelectorAll('.drawer-menu a');
  drawerLinks.forEach(link => {
    link.addEventListener('click', () => toggleMobileDrawer(false));
  });

  // =========================================================================
  // FAQ Accordion
  // =========================================================================
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    const questionBtn = item.querySelector('.faq-question');
    questionBtn.addEventListener('click', () => {
      const isActive = item.classList.contains('active');
      faqItems.forEach(i => i.classList.remove('active'));
      if (!isActive) item.classList.add('active');
    });
  });

  // =========================================================================
  // Toast Notification System
  // =========================================================================
  function showToast(message) {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
      </svg>
      <span>${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(15px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  window.showToast = showToast;
});
