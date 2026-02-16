// Add to Cart button functionality
function handleCartButtonClick(button) {
    button.classList.toggle('clicked');
    if (button.classList.contains('clicked')) {
        button.textContent = 'Added to Cart';
        button.style.backgroundColor = '#28a745';
    } else {
        button.textContent = 'Add to Cart';
        button.style.backgroundColor = '#3a5a40';
    }
}

// Wishlist button functionality
function handleWishlistButtonClick(button) {
    button.classList.toggle('active');
    if (button.classList.contains('active')) {
        button.style.transform = 'scale(1)';
        void button.offsetWidth; // Trigger reflow
        button.style.animation = 'heartBeat 0.3s ease-in-out';
    } else {
        button.style.animation = 'none';
    }
}

// Function to check if user is logged in
function isUserLoggedIn() {
    return document.querySelector('a[href="profile.php"]') !== null;
}

// Function to show popup
function showAuthPopup() {
    const existingPopup = document.querySelector('.auth-popup');
    if (existingPopup) return;

    const popup = document.createElement('div');
    popup.className = 'auth-popup';
    popup.innerHTML = `
        <div class="popup-content">
            <h3>Please Log In</h3>
            <p>You need to be logged in to use this feature.</p>
            <div class="popup-buttons">
                <button onclick="window.location.href='loginreg.php'" class="login-btn">Log In</button>
                <button onclick="closePopup()" class="close-btn">Close</button>
            </div>
        </div>
    `;
    document.body.appendChild(popup);

    // Trigger reflow and add active class for animation
    setTimeout(() => popup.classList.add('active'), 10);

    // Close popup when clicking outside
    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            closePopup();
        }
    });

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closePopup();
    });
}

// Function to close popup
function closePopup() {
    const popup = document.querySelector('.auth-popup');
    if (popup) {
        popup.classList.remove('active');
        setTimeout(() => popup.remove(), 300); // Wait for animation
    }
}

// Add event listeners when document is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Cart and wishlist buttons
    const actionButtons = document.querySelectorAll('.btn, .wishlist');
    actionButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!isUserLoggedIn()) {
                e.preventDefault();
                showAuthPopup();
            }
        });
    });

    const cartIcon = document.querySelector('a[href="cart.php"]');
    if (cartIcon) {
        cartIcon.addEventListener('click', (e) => {
            if (!isUserLoggedIn()) {
                e.preventDefault();
                showAuthPopup();
            }
        });
    }

    // Header wishlist icon
    const wishlistIcon = document.querySelector('a[href="wishlist.php"]');
    if (wishlistIcon) {
        wishlistIcon.addEventListener('click', (e) => {
            if (!isUserLoggedIn()) {
                e.preventDefault();
                showAuthPopup();
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const userIcon = document.getElementById('userIcon');
    const dropdown = document.getElementById('userDropdown');

    if (userIcon && dropdown) {
        userIcon.addEventListener('click', (e) => {
            if (isUserLoggedIn()) {
                e.preventDefault();
                dropdown.classList.toggle('show');
                e.stopPropagation();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.matches('#userIcon') && !e.target.matches('#svg')) {
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
    }
});