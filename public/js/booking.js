/**
 * Premium Barber Shop - Booking Functionality
 * Path: /Barber_shop/public/js/booking.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // Cache DOM elements
    const dateInput        = document.getElementById('date');
    const serviceInput     = document.getElementById('service');
    const durationInput    = document.getElementById('serviceDuration');
    const staff            = document.getElementById('staff');
    const date             = document.getElementById('date');
    const slotsContainer   = document.getElementById('slotsContainer');
    const timeSlots        = document.getElementById('timeSlots');
    const selectedTime     = document.getElementById('selectedTime');
    const form             = document.getElementById('bookingForm');
    const submitBtn        = document.getElementById('submitBtn');
    const message          = document.getElementById('message');
    const selectedServiceDisplay = document.getElementById('selectedServiceDisplay');

    // Set minimum date to today
    if (dateInput) {
        dateInput.min = new Date().toISOString().split('T')[0];
    }

    // ── Load staff from database into dropdown ──
    fetch('/Barber_shop/api/staff.php')
        .then(r => r.json())
        .then(staffList => {
            if (!staff) return;
            staff.innerHTML = '<option value="">Select a stylist option...</option>';
            staffList.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name;
                staff.appendChild(opt);
            });
        })
        .catch(() => {
            // fallback — keep whatever is in HTML
        });

    // ── Expose to global scope for HTML onclick="selectService()" ──
    window.selectService = function(serviceId, duration) {
        if (!serviceInput || !durationInput) return;

        serviceInput.value = serviceId;
        durationInput.value = duration;

        const serviceNames = {
            '1': '✂️ Haircut — 350 ETB',
            '2': '🪒 Beard Trim & Shape — 250 ETB',
            '3': '👑 The Full Combination — 550 ETB'
        };

        if (selectedServiceDisplay) {
            selectedServiceDisplay.innerHTML = `<span class="form-display__value">${serviceNames[serviceId] || 'Service selected'}</span>`;
        }

        // Smooth scroll to booking section
        const bookingSection = document.getElementById('booking');
        if (bookingSection) {
            bookingSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Load slots if date & staff already picked
        if (staff?.value && date?.value) {
            loadSlots();
        }
    };

    // ── Load available time slots from API ──
    function loadSlots() {
        if (!serviceInput?.value || !staff?.value || !date?.value) return;

        const duration = durationInput?.value || 30;

        if (timeSlots) {
            timeSlots.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:var(--text-muted); padding:0.5rem;">Loading times...</div>';
        }
        if (slotsContainer) {
            slotsContainer.classList.add('active');
        }

        fetch(`/Barber_shop/api/availability.php?date=${date.value}&staff_id=${staff.value}&duration=${duration}`)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(slots => {
                if (!timeSlots) return;
                timeSlots.innerHTML = '';

                if (!slots || !Array.isArray(slots) || slots.length === 0) {
                    timeSlots.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:var(--text-muted); padding:0.5rem;">No available times for this date</div>';
                    return;
                }

                slots.forEach(time => {
                    const div = document.createElement('div');
                    div.className = 'time-slot';
                    div.textContent = time;
                    div.setAttribute('data-time', time);

                    div.onclick = () => {
                        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                        div.classList.add('selected');
                        if (selectedTime) selectedTime.value = time;
                    };

                    timeSlots.appendChild(div);

                    // Auto-click if this matches preferred time from suggestions
                    if (window._preferredTime && window._preferredTime === time) {
                        div.click();
                        window._preferredTime = null;
                    }
                });
            })
            .catch(err => {
                console.error('Failed to load slots:', err);
                if (timeSlots) {
                    timeSlots.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:#ef4444; padding:0.5rem;">Error loading times. Please try again.</div>';
                }
            });
    }

    // Make loadSlots available globally for applyPreferences()
    window.loadSlots = loadSlots;

    // Attach change listeners
    if (staff) staff.addEventListener('change', loadSlots);
    if (date)  date.addEventListener('change', loadSlots);

    // ── Handle form submission ──
    if (form) {
        form.addEventListener('submit', async e => {
            e.preventDefault();

            if (selectedTime && !selectedTime.value) {
                return showMessage('⚠️ Please select a time slot.', 'error');
            }
            if (!serviceInput?.value) {
                return showMessage('⚠️ Please select a service first.', 'error');
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('btn-loading');
            }
            if (message) message.classList.remove('active');

            try {
                const res = await fetch('/Barber_shop/api/book.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name:       document.getElementById('name')?.value?.trim()  || '',
                        email:      document.getElementById('email')?.value?.trim() || '',
                        phone:      document.getElementById('phone')?.value?.trim() || '',
                        service_id: serviceInput.value,
                        staff_id:   staff.value,
                        date:       date.value,
                        time:       selectedTime.value
                    })
                });

                const data = await res.json();

                if (data.success) {
                    showMessage('✅ Booking confirmed! Check your email for details.', 'success');
                    form.reset();
                    if (slotsContainer)          slotsContainer.classList.remove('active');
                    if (selectedTime)            selectedTime.value = '';
                    if (selectedServiceDisplay)  selectedServiceDisplay.innerHTML = '<span class="form-display__empty">Select a service above to begin</span>';
                    if (serviceInput)            serviceInput.value = '';
                    if (durationInput)           durationInput.value = '';
                } else {
                    showMessage('❌ ' + (data.error || 'Booking failed'), 'error');
                }
            } catch (err) {
                console.error('Submission error:', err);
                showMessage('❌ Network error. Please try again.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-loading');
                }
            }
        });
    }

    // ── Show message helper ──
    function showMessage(text, type) {
        if (!message) return;
        message.textContent = text;
        message.className = `message ${type} active`;
        message.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // ── Smooth scroll for anchor links ──
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const target = document.querySelector(targetId);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
});