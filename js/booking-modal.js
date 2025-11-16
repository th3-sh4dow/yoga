// Booking Modal JavaScript
class BookingModal {
    constructor() {
        this.modal = null;
        this.currentProgram = null;
        this.programs = {
            'weekend': {
                title: 'Weekend Wellness Yoga Retreat',
                duration: '2 Days & 1 Night',
                durationDays: 1,
                prices: {
                    'garden_single': 10000,
                    'garden_double': 8000,
                    'premium_single': 12000,
                    'premium_double': 10000
                }
            },
            '3day': {
                title: '3-Day Wellness & Retreat',
                duration: '3 Days & 2 Nights',
                durationDays: 2,
                prices: {
                    'garden_single': 16000,
                    'garden_double': 14000,
                    'premium_single': 18000,
                    'premium_double': 16000
                }
            },
            '7day': {
                title: '7 Days Yoga & Wellness Detox Retreat',
                duration: '7 Days & 6 Nights',
                durationDays: 6,
                prices: {
                    'garden_single': 35000,
                    'garden_double': 30000,
                    'premium_single': 39000,
                    'premium_double': 34000
                }
            },
            'online': {
                title: 'Online Yoga & Meditation Classes',
                duration: 'Flexible Schedule',
                prices: {
                    'monthly': 2000,
                    'quarterly': 5000,
                    'yearly': 15000
                }
            }
        };
        this.init();
    }

    init() {
        this.createModal();
        this.attachEventListeners();
    }

    createModal() {
        const modalHTML = `
            <div id="bookingModal" class="booking-modal">
                <div class="booking-modal-content">
                    <div class="booking-modal-header">
                        <span class="close-modal">&times;</span>
                        <h2>Book Your Retreat</h2>
                        <p>Choose: Pay Now (Instant) or WhatsApp (Manual)</p>
                    </div>
                    <div class="booking-modal-body">
                        <form id="bookingForm" class="booking-form">
                            <!-- Personal Information -->
                            <div class="form-group full-width">
                                <label>Select Program *</label>
                                <small class="form-text text-muted mb-2">Choose your preferred retreat program</small>
                                <div class="program-selection" id="programSelection">
                                    <!-- Programs will be populated here -->
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="guestName">Full Name *</label>
                                    <input type="text" id="guestName" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="guestEmail">Email Address *</label>
                                    <input type="email" id="guestEmail" name="email" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="guestPhone">Phone Number *</label>
                                    <input type="tel" id="guestPhone" name="phone" required>
                                </div>
                                <div class="form-group">
                                    <label for="emergencyContact">Emergency Contact</label>
                                    <input type="tel" id="emergencyContact" name="emergency_contact">
                                </div>
                            </div>

                            <!-- Accommodation Selection -->
                            <div class="form-group full-width" id="accommodationSection">
                                <label>Accommodation Type & Occupancy *</label>
                                <small class="form-text text-muted mb-2">Choose your accommodation and occupancy preference</small>
                                <div class="accommodation-options" id="accommodationOptions">
                                    <!-- Accommodation options will be populated here -->
                                </div>
                            </div>

                            <!-- Date Selection -->
                            <div class="form-row" id="dateSection">
                                <div class="form-group">
                                    <label for="checkInDate">Check-in Date *</label>
                                    <input type="date" id="checkInDate" name="check_in_date" required>
                                    <small class="form-text text-muted">Select your arrival date</small>
                                </div>
                                <div class="form-group">
                                    <label for="checkOutDate">Check-out Date *</label>
                                    <input type="date" id="checkOutDate" name="check_out_date" required>
                                    <small class="form-text text-muted" id="checkOutHelper">Will be calculated automatically based on program duration</small>
                                </div>
                            </div>

                            <!-- Special Requirements -->
                            <div class="form-group full-width">
                                <label for="specialRequirements">Special Requirements / Dietary Restrictions</label>
                                <textarea id="specialRequirements" name="special_requirements" placeholder="Please mention any dietary restrictions, health conditions, or special requests..."></textarea>
                            </div>

                            <!-- Price Summary -->
                            <div class="price-summary" id="priceSummary">
                                <h4>Booking Summary</h4>
                                <div class="price-breakdown">
                                    <span>Program:</span>
                                    <span id="selectedProgram">-</span>
                                </div>
                                <div class="price-breakdown">
                                    <span>Accommodation:</span>
                                    <span id="selectedAccommodation">-</span>
                                </div>
                                <div class="price-breakdown">
                                    <span>Duration:</span>
                                    <span id="selectedDuration">-</span>
                                </div>
                                <div class="total-amount">
                                    <span>Total Amount:</span>
                                    <span id="totalAmount">₹0</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="booking-actions">
                                <button type="button" class="btn-cancel" id="cancelBooking">Cancel</button>
                                <button type="button" class="btn-whatsapp" id="whatsappBooking">
                                    <i class="fa fa-whatsapp"></i> Book via WhatsApp
                                </button>
                                <button type="submit" class="btn-book" id="submitBooking">
                                    <i class="fa fa-credit-card"></i> Pay Now
                                </button>
                            </div>
                        </form>

                        <!-- Loading State -->
                        <div class="loading-spinner" id="loadingSpinner">
                            <div class="spinner"></div>
                            <p>Processing your booking...</p>
                        </div>

                        <!-- Success State -->
                        <div class="success-message" id="successMessage">
                            <i class="fa fa-check-circle"></i>
                            <h3>Booking Created Successfully!</h3>
                            <p>You will be redirected to the payment page shortly.</p>
                            <p><strong>Booking ID:</strong> <span id="bookingId"></span></p>
                        </div>

                        <!-- Error Message -->
                        <div class="error-message" id="errorMessage">
                            <i class="fa fa-exclamation-triangle"></i>
                            <span id="errorText"></span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('bookingModal');
    }

    attachEventListeners() {
        // Close modal events
        const closeBtn = this.modal.querySelector('.close-modal');
        const cancelBtn = this.modal.querySelector('#cancelBooking');
        const whatsappBtn = this.modal.querySelector('#whatsappBooking');
        
        closeBtn.addEventListener('click', () => this.closeModal());
        cancelBtn.addEventListener('click', () => this.closeModal());
        whatsappBtn.addEventListener('click', () => this.handleWhatsAppBooking());
        
        // Close on outside click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.closeModal();
            }
        });

        // Form submission
        const form = this.modal.querySelector('#bookingForm');
        form.addEventListener('submit', (e) => this.handleFormSubmit(e));

        // Program and accommodation selection is now handled by click handlers

        // Date validation and auto-calculation
        const checkInDate = this.modal.querySelector('#checkInDate');
        const checkOutDate = this.modal.querySelector('#checkOutDate');
        
        checkInDate.addEventListener('change', () => {
            this.calculateCheckOutDate();
            this.validateDates();
        });
        checkOutDate.addEventListener('change', () => {
            this.validateDates();
            this.handleManualDateChange();
        });

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        checkInDate.min = today;
        checkOutDate.min = today;
    }

    openModal(programType = null) {
        this.currentProgram = programType;
        this.populatePrograms();
        this.populateAccommodationOptions();
        this.modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Pre-select program if specified
        if (programType && this.programs[programType]) {
            const programRadio = this.modal.querySelector(`input[value="${programType}"]`);
            if (programRadio) {
                programRadio.checked = true;
                this.handleProgramChange(programType);
            }
        }
    }

    closeModal() {
        this.modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        this.resetForm();
    }

    populatePrograms() {
        const container = this.modal.querySelector('#programSelection');
        container.innerHTML = '';

        Object.keys(this.programs).forEach(key => {
            const program = this.programs[key];
            const programHTML = `
                <div class="program-option" data-program="${key}">
                    <input type="radio" name="program" value="${key}" required>
                    <div class="program-title">${program.title}</div>
                    <div class="program-duration">${program.duration}</div>
                    <div class="program-price">Starting from ₹${Math.min(...Object.values(program.prices)).toLocaleString()}</div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', programHTML);
        });
        
        // Add click event listeners for program options
        this.addProgramClickHandlers();
    }

    addProgramClickHandlers() {
        const programOptions = this.modal.querySelectorAll('.program-option');
        programOptions.forEach(option => {
            option.addEventListener('click', () => {
                // Check the radio button
                const radio = option.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    
                    // Update visual selection
                    programOptions.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');
                    
                    // Handle program change
                    const programKey = option.dataset.program;
                    this.handleProgramChange(programKey);
                }
            });
        });
    }

    addAccommodationClickHandlers() {
        const accommodationOptions = this.modal.querySelectorAll('.accommodation-option');
        accommodationOptions.forEach(option => {
            option.addEventListener('click', () => {
                // Check the radio button
                const radio = option.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    
                    // Update visual selection
                    accommodationOptions.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');
                    
                    // Update price summary
                    this.updatePriceSummary();
                }
            });
        });
    }

    populateAccommodationOptions() {
        const container = this.modal.querySelector('#accommodationOptions');
        const accommodationSection = this.modal.querySelector('#accommodationSection');
        const dateSection = this.modal.querySelector('#dateSection');
        
        // Show/hide sections based on program type
        if (this.currentProgram === 'online') {
            accommodationSection.style.display = 'none';
            dateSection.style.display = 'none';
            return;
        } else {
            accommodationSection.style.display = 'block';
            dateSection.style.display = 'block';
        }

        container.innerHTML = `
            <div class="accommodation-option" data-value="garden_single">
                <input type="radio" name="accommodation" value="garden_single" required>
                <strong>Garden Cottage</strong><br>
                <small>Single Occupancy</small>
            </div>
            <div class="accommodation-option" data-value="garden_double">
                <input type="radio" name="accommodation" value="garden_double" required>
                <strong>Garden Cottage</strong><br>
                <small>Double Occupancy</small>
            </div>
            <div class="accommodation-option" data-value="premium_single">
                <input type="radio" name="accommodation" value="premium_single" required>
                <strong>Premium Cottage</strong><br>
                <small>Single Occupancy</small>
            </div>
            <div class="accommodation-option" data-value="premium_double">
                <input type="radio" name="accommodation" value="premium_double" required>
                <strong>Premium Cottage</strong><br>
                <small>Double Occupancy</small>
            </div>
        `;
        
        // Add click event listeners
        this.addAccommodationClickHandlers();

        // For online classes, show different options
        if (this.currentProgram === 'online') {
            container.innerHTML = `
                <div class="accommodation-option" data-value="monthly">
                    <input type="radio" name="accommodation" value="monthly" required>
                    <strong>Monthly Plan</strong><br>
                    <small>1 Month Access</small>
                </div>
                <div class="accommodation-option" data-value="quarterly">
                    <input type="radio" name="accommodation" value="quarterly" required>
                    <strong>Quarterly Plan</strong><br>
                    <small>3 Months Access</small>
                </div>
                <div class="accommodation-option" data-value="yearly">
                    <input type="radio" name="accommodation" value="yearly" required>
                    <strong>Yearly Plan</strong><br>
                    <small>12 Months Access</small>
                </div>
            `;
            
            // Add click event listeners
            this.addAccommodationClickHandlers();
        }
    }

    handleProgramChange(programKey) {
        this.currentProgram = programKey;
        
        // Update program selection UI
        const programOptions = this.modal.querySelectorAll('.program-option');
        programOptions.forEach(option => {
            option.classList.remove('selected');
            const radio = option.querySelector('input[type="radio"]');
            if (radio && radio.value === programKey) {
                option.classList.add('selected');
            }
        });

        this.populateAccommodationOptions();
        this.updatePriceSummary();
        
        // Auto-calculate check-out date if check-in is already selected
        this.calculateCheckOutDate();
    }

    updatePriceSummary() {
        const selectedProgram = this.modal.querySelector('input[name="program"]:checked');
        const selectedAccommodation = this.modal.querySelector('input[name="accommodation"]:checked');

        if (!selectedProgram || !selectedAccommodation) return;

        const program = this.programs[selectedProgram.value];
        const accommodationType = selectedAccommodation.value;
        const price = program.prices[accommodationType] || 0;

        // Update summary display
        this.modal.querySelector('#selectedProgram').textContent = program.title;
        this.modal.querySelector('#selectedDuration').textContent = program.duration;
        
        let accommodationText = '';
        if (this.currentProgram === 'online') {
            accommodationText = selectedAccommodation.parentElement.querySelector('strong').textContent;
        } else {
            const parts = accommodationType.split('_');
            accommodationText = `${parts[0].charAt(0).toUpperCase() + parts[0].slice(1)} Cottage (${parts[1].charAt(0).toUpperCase() + parts[1].slice(1)} Occupancy)`;
        }
        
        this.modal.querySelector('#selectedAccommodation').textContent = accommodationText;
        this.modal.querySelector('#totalAmount').textContent = `₹${price.toLocaleString()}`;

        // Update accommodation selection UI
        const accommodationOptions = this.modal.querySelectorAll('.accommodation-option');
        accommodationOptions.forEach(option => {
            option.classList.remove('selected');
            const radio = option.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                option.classList.add('selected');
            }
        });
    }

    calculateCheckOutDate() {
        const checkInInput = this.modal.querySelector('#checkInDate');
        const checkOutInput = this.modal.querySelector('#checkOutDate');
        const selectedProgram = this.modal.querySelector('input[name="program"]:checked');
        
        if (!checkInInput.value || !selectedProgram) return;
        
        const checkInDate = new Date(checkInInput.value);
        const program = this.programs[selectedProgram.value];
        
        // Get duration from program data
        let durationDays = program.durationDays || 1;
        
        if (selectedProgram.value === 'online') {
            // For online classes, no check-out date needed
            checkOutInput.value = '';
            checkOutInput.style.display = 'none';
            checkOutInput.parentElement.style.display = 'none';
            checkOutInput.classList.remove('auto-calculated');
            
            // Reset helper text
            const helper = this.modal.querySelector('#checkOutHelper');
            if (helper) {
                helper.textContent = 'Not applicable for online classes';
                helper.style.color = '#6c757d';
                helper.style.fontWeight = 'normal';
            }
            return;
        }
        
        // Calculate check-out date
        const checkOutDate = new Date(checkInDate);
        checkOutDate.setDate(checkInDate.getDate() + durationDays);
        
        // Format date as YYYY-MM-DD for input
        const formattedDate = checkOutDate.toISOString().split('T')[0];
        checkOutInput.value = formattedDate;
        
        // Show check-out date field for non-online programs
        checkOutInput.style.display = 'block';
        checkOutInput.parentElement.style.display = 'block';
        
        // Add visual styling to indicate auto-calculation
        checkOutInput.classList.remove('manually-changed');
        checkOutInput.classList.add('auto-calculated');
        
        // Update helper text
        const helper = this.modal.querySelector('#checkOutHelper');
        if (helper) {
            helper.textContent = `✅ Auto-calculated: ${program.duration}`;
            helper.style.color = '#28a745';
            helper.style.fontWeight = '600';
        }
    }

    handleManualDateChange() {
        const checkOutInput = this.modal.querySelector('#checkOutDate');
        const helper = this.modal.querySelector('#checkOutHelper');
        
        if (checkOutInput.classList.contains('auto-calculated')) {
            // User manually changed the auto-calculated date
            checkOutInput.classList.remove('auto-calculated');
            checkOutInput.classList.add('manually-changed');
            
            if (helper) {
                helper.textContent = '⚠️ Manually modified - may affect program schedule';
                helper.style.color = '#ffc107';
                helper.style.fontWeight = '600';
            }
        }
    }

    validateForm(form) {
        // Clear previous error states
        this.clearValidationErrors();

        // Check basic HTML5 validation first
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }

        // Check required radio button selections
        const selectedProgram = this.modal.querySelector('input[name="program"]:checked');
        const selectedAccommodation = this.modal.querySelector('input[name="accommodation"]:checked');

        if (!selectedProgram) {
            this.addValidationError('#programSelection', 'Please select a retreat program');
            return false;
        }

        if (!selectedAccommodation) {
            this.addValidationError('#accommodationOptions', 'Please select an accommodation type (occupancy)');
            return false;
        }

        // For non-online programs, check dates
        if (selectedProgram.value !== 'online') {
            const checkIn = this.modal.querySelector('#checkInDate').value;
            const checkOut = this.modal.querySelector('#checkOutDate').value;

            if (!checkIn) {
                this.addValidationError('#checkInDate', 'Please select a check-in date');
                return false;
            }

            if (!checkOut) {
                this.addValidationError('#checkOutDate', 'Please select a check-out date');
                return false;
            }

            // Validate date logic
            const checkInDate = new Date(checkIn);
            const checkOutDate = new Date(checkOut);

            if (checkOutDate <= checkInDate) {
                this.addValidationError('#checkOutDate', 'Check-out date must be after check-in date');
                return false;
            }
        }

        return true;
    }

    clearValidationErrors() {
        // Remove error classes from all elements
        this.modal.querySelectorAll('.validation-error').forEach(element => {
            element.classList.remove('validation-error');
        });
        
        // Hide error message
        this.modal.querySelector('#errorMessage').style.display = 'none';
    }

    addValidationError(selector, message) {
        const element = this.modal.querySelector(selector);
        if (element) {
            element.classList.add('validation-error');
            
            // Scroll to the error element
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        this.showError(message);
    }

    validateDates() {
        const checkIn = this.modal.querySelector('#checkInDate').value;
        const checkOut = this.modal.querySelector('#checkOutDate').value;

        if (checkIn && checkOut) {
            const checkInDate = new Date(checkIn);
            const checkOutDate = new Date(checkOut);

            if (checkOutDate <= checkInDate) {
                this.modal.querySelector('#checkOutDate').setCustomValidity('Check-out date must be after check-in date');
            } else {
                this.modal.querySelector('#checkOutDate').setCustomValidity('');
            }
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        // Validate required fields first
        const form = e.target;
        if (!this.validateForm(form)) {
            return;
        }
        
        const formData = new FormData(form);
        const bookingData = Object.fromEntries(formData.entries());
        
        // Debug: Log form data
        console.log('Form data entries:', Array.from(formData.entries()));
        console.log('Booking data from form:', bookingData);
        
        // Add calculated amount and parse accommodation data
        const selectedProgram = this.modal.querySelector('input[name="program"]:checked');
        const selectedAccommodation = this.modal.querySelector('input[name="accommodation"]:checked');
        
        if (selectedProgram && selectedAccommodation) {
            const program = this.programs[selectedProgram.value];
            bookingData.amount = program.prices[selectedAccommodation.value];
            bookingData.program = program.title;
            
            // Parse accommodation value to separate accommodation and occupancy
            const accommodationValue = selectedAccommodation.value;
            console.log('Selected accommodation value:', accommodationValue);
            console.log('Selected program value:', selectedProgram.value);
            
            if (selectedProgram.value === 'online') {
                bookingData.accommodation = accommodationValue; // monthly, quarterly, yearly
                bookingData.occupancy = 'online'; // for online classes
            } else {
                // For retreat programs: garden_single, garden_double, premium_single, premium_double
                const parts = accommodationValue.split('_');
                if (parts.length >= 2) {
                    bookingData.accommodation = parts[0] + '_cottage'; // garden_cottage, premium_cottage
                    bookingData.occupancy = parts[1]; // single, double
                } else {
                    // Fallback if split doesn't work as expected
                    bookingData.accommodation = accommodationValue;
                    bookingData.occupancy = 'single'; // default
                }
            }
            
            console.log('Final accommodation:', bookingData.accommodation);
            console.log('Final occupancy:', bookingData.occupancy);
        }

        // Debug: Log the data being sent
        console.log('Booking data being sent:', bookingData);

        this.showLoading();

        try {
            console.log('Making request to booking-system.php...');
            const response = await fetch('booking-system.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'create_booking',
                    ...bookingData
                })
            });

            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            console.log('Raw response:', responseText);

            let result;
            try {
                // Try to extract JSON from response (in case there are PHP warnings)
                const jsonMatch = responseText.match(/\{.*\}$/);
                const jsonString = jsonMatch ? jsonMatch[0] : responseText;
                result = JSON.parse(jsonString);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', responseText);
                
                // Check if response contains success indicators
                if (responseText.includes('"success":true') && responseText.includes('booking_id')) {
                    // Try to extract the JSON part manually
                    const jsonStart = responseText.lastIndexOf('{"success"');
                    if (jsonStart !== -1) {
                        try {
                            result = JSON.parse(responseText.substring(jsonStart));
                        } catch (e) {
                            throw new Error('Could not parse JSON response');
                        }
                    } else {
                        throw new Error('Invalid JSON response from server');
                    }
                } else {
                    throw new Error('Invalid JSON response from server');
                }
            }

            console.log('Parsed result:', result);

            if (result.success) {
                this.showSuccess(result.booking_id);
                
                // Redirect to payment after 3 seconds
                setTimeout(() => {
                    window.open(result.payment_link, '_blank');
                    this.closeModal();
                }, 3000);
            } else {
                this.showError(result.message || 'Unknown error occurred');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            this.showError(`Network error: ${error.message}. Please check if booking-system.php exists and is accessible.`);
        }
    }

    showLoading() {
        this.modal.querySelector('#bookingForm').style.display = 'none';
        this.modal.querySelector('#loadingSpinner').style.display = 'block';
        this.modal.querySelector('#successMessage').style.display = 'none';
        this.modal.querySelector('#errorMessage').style.display = 'none';
    }

    showSuccess(bookingId) {
        this.modal.querySelector('#bookingForm').style.display = 'none';
        this.modal.querySelector('#loadingSpinner').style.display = 'none';
        this.modal.querySelector('#successMessage').style.display = 'block';
        this.modal.querySelector('#bookingId').textContent = bookingId;
        this.modal.querySelector('#errorMessage').style.display = 'none';
    }

    showError(message) {
        this.modal.querySelector('#bookingForm').style.display = 'block';
        this.modal.querySelector('#loadingSpinner').style.display = 'none';
        this.modal.querySelector('#successMessage').style.display = 'none';
        this.modal.querySelector('#errorMessage').style.display = 'block';
        this.modal.querySelector('#errorText').textContent = message;
    }



    handleWhatsAppBooking() {
        const form = this.modal.querySelector('#bookingForm');
        
        // Validate required fields
        if (!this.validateForm(form)) {
            return;
        }
        
        const formData = new FormData(form);
        const selectedProgram = this.modal.querySelector('input[name="program"]:checked');
        const selectedAccommodation = this.modal.querySelector('input[name="accommodation"]:checked');
        
        // Get booking details
        const program = this.programs[selectedProgram.value];
        const amount = program.prices[selectedAccommodation.value];
        const name = formData.get('name');
        const email = formData.get('email');
        const phone = formData.get('phone');
        const checkIn = formData.get('check_in_date');
        const checkOut = formData.get('check_out_date');
        const specialRequirements = formData.get('special_requirements');
        
        // Create WhatsApp message
        let message = `🧘‍♀️ *Yoga Retreat Booking Request*\n\n`;
        message += `👤 *Name:* ${name}\n`;
        message += `📧 *Email:* ${email}\n`;
        message += `📱 *Phone:* ${phone}\n\n`;
        message += `🏛️ *Program:* ${program.title}\n`;
        message += `🏠 *Accommodation:* ${this.getAccommodationText(selectedAccommodation.value)}\n`;
        message += `💰 *Amount:* ₹${amount.toLocaleString()}\n\n`;
        
        if (checkIn) message += `📅 *Check-in:* ${checkIn}\n`;
        if (checkOut) message += `📅 *Check-out:* ${checkOut}\n`;
        if (specialRequirements) message += `📝 *Special Requirements:* ${specialRequirements}\n`;
        
        message += `\nPlease confirm my booking and share payment details. Thank you! 🙏`;
        
        // Open WhatsApp
        const whatsappNumber = '916203517866'; // Your WhatsApp number
        const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
        
        window.open(whatsappUrl, '_blank');
        this.closeModal();
    }
    
    getAccommodationText(accommodationType) {
        if (this.currentProgram === 'online') {
            const option = this.modal.querySelector(`input[value="${accommodationType}"]`).parentElement;
            return option.querySelector('strong').textContent;
        } else {
            const parts = accommodationType.split('_');
            return `${parts[0].charAt(0).toUpperCase() + parts[0].slice(1)} Cottage (${parts[1].charAt(0).toUpperCase() + parts[1].slice(1)} Occupancy)`;
        }
    }

    resetForm() {
        this.modal.querySelector('#bookingForm').reset();
        this.modal.querySelector('#bookingForm').style.display = 'block';
        this.modal.querySelector('#loadingSpinner').style.display = 'none';
        this.modal.querySelector('#successMessage').style.display = 'none';
        this.modal.querySelector('#errorMessage').style.display = 'none';
        
        // Reset UI selections
        this.modal.querySelectorAll('.program-option').forEach(option => {
            option.classList.remove('selected');
        });
        this.modal.querySelectorAll('.accommodation-option').forEach(option => {
            option.classList.remove('selected');
        });
        
        // Reset price summary
        this.modal.querySelector('#selectedProgram').textContent = '-';
        this.modal.querySelector('#selectedAccommodation').textContent = '-';
        this.modal.querySelector('#selectedDuration').textContent = '-';
        this.modal.querySelector('#totalAmount').textContent = '₹0';
    }
}

// Initialize booking modal when DOM is loaded
let bookingModal;
document.addEventListener('DOMContentLoaded', function() {
    try {
        bookingModal = new BookingModal();
    } catch (error) {
        console.error('Error initializing BookingModal:', error);
    }
    
    // Add click handlers to existing booking buttons
    document.querySelectorAll('a[href*="courses.html"], a[href*="contact.html"]').forEach(link => {
        if (link.textContent.toLowerCase().includes('book') || 
            link.textContent.toLowerCase().includes('retreat') ||
            link.textContent.toLowerCase().includes('join')) {
            
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Determine program type based on context
                let programType = null;
                const text = this.textContent.toLowerCase();
                const parentText = this.closest('.course-card, .program-card, .circle')?.textContent.toLowerCase() || '';
                
                if (text.includes('weekend') || parentText.includes('weekend')) {
                    programType = 'weekend';
                } else if (text.includes('3-day') || parentText.includes('3-day')) {
                    programType = '3day';
                } else if (text.includes('7 day') || text.includes('week') || parentText.includes('7 day')) {
                    programType = '7day';
                } else if (text.includes('online') || parentText.includes('online')) {
                    programType = 'online';
                }
                
                bookingModal.openModal(programType);
            });
        }
    });
});

// Global function to open booking modal (can be called from anywhere)
function openBookingModal(programType = null) {
    if (bookingModal) {
        bookingModal.openModal(programType);
    } else {
        // Try to initialize if not done
        setTimeout(() => {
            if (typeof BookingModal !== 'undefined') {
                bookingModal = new BookingModal();
                bookingModal.openModal(programType);
            } else {
                alert('Booking system is loading. Please try again in a moment.');
            }
        }, 100);
    }
}

// Fallback initialization for window load
window.addEventListener('load', function() {
    if (!bookingModal) {
        try {
            bookingModal = new BookingModal();
        } catch (error) {
            console.error('Fallback error initializing BookingModal:', error);
        }
    }
});