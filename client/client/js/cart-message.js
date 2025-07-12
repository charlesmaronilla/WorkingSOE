// Cart Message Notification
function showCartMessage(message) {
    // Create message element if it doesn't exist
    let messageEl = document.getElementById('cart-message');
    if (!messageEl) {
        messageEl = document.createElement('div');
        messageEl.id = 'cart-message';
        messageEl.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 16px;
            z-index: 99999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
        `;
        document.body.appendChild(messageEl);
    }

    // Update message content
    messageEl.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;

    // Show message
    messageEl.style.display = 'flex';
    messageEl.style.opacity = '0';
    messageEl.style.transform = 'translateX(-50%) translateY(-20px)';
    
    // Trigger reflow
    void messageEl.offsetWidth;
    
    // Animate in
    messageEl.style.opacity = '1';
    messageEl.style.transform = 'translateX(-50%) translateY(0)';

    // Hide after 3 seconds
    setTimeout(() => {
        messageEl.style.opacity = '0';
        messageEl.style.transform = 'translateX(-50%) translateY(-20px)';
        
        // Remove from DOM after animation
        setTimeout(() => {
            messageEl.style.display = 'none';
        }, 300);
    }, 3000);
}

// Initialize form submission handlers
document.addEventListener('DOMContentLoaded', function() {
    // Handle add to cart form submissions
    document.addEventListener('submit', function(e) {
        if (e.target && e.target.matches('.add-to-cart-form')) {
            e.preventDefault();
            
            const form = e.target;
            const input = form.querySelector('.quantity-input');
            const itemName = form.querySelector('input[name="name"]').value;
            const quantity = input ? parseInt(input.value) || 1 : 1;
            
            // Show message
            showCartMessage(`✓ Added ${quantity} ${itemName}${quantity > 1 ? 's' : ''} to cart`);
            
            // Submit form after a short delay
            setTimeout(() => {
                form.submit();
            }, 100);
        }
    });
});
