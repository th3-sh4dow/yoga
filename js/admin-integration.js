// Admin Integration Script
// This script loads offers and announcements from localStorage and updates the notification banner

$(document).ready(function() {
    updateNotificationBanner();
});

function updateNotificationBanner() {
    const offers = JSON.parse(localStorage.getItem('yogaOffers') || '[]');
    const announcements = JSON.parse(localStorage.getItem('yogaAnnouncements') || '[]');
    
    // Filter active items
    const activeOffers = offers.filter(offer => offer.active);
    const activeAnnouncements = announcements.filter(announcement => announcement.active);
    
    // Build notification content
    let notificationContent = '';
    
    // Add offers
    if (activeOffers.length > 0) {
        const latestOffer = activeOffers[activeOffers.length - 1]; // Get latest offer
        notificationContent += `<strong>Special Offer:</strong> ${latestOffer.title} - ${latestOffer.discount}% OFF! `;
    }
    
    // Add announcements
    if (activeAnnouncements.length > 0) {
        const latestAnnouncement = activeAnnouncements[activeAnnouncements.length - 1]; // Get latest announcement
        notificationContent += `<strong>New:</strong> ${latestAnnouncement.text}`;
    }
    
    // Show/hide notification banner based on content
    if (notificationContent) {
        $('.notification-content').html(notificationContent);
        $('.notification-banner').show();
    } else {
        $('.notification-banner').hide(); // Hide banner if no active offers/announcements
    }
}

// Function to refresh notification banner (can be called from other scripts)
window.refreshNotificationBanner = updateNotificationBanner;