// Sample Documents Modal System
document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const modal = document.getElementById('documentModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalIframe = document.getElementById('modalIframe');
    const modalClose = document.querySelector('.modal-close');
    const fullDocumentBtn = document.getElementById('fullDocumentBtn');
    
    // Document preview buttons
    const previewButtons = document.querySelectorAll('.preview-document');
    
    // Document data with Google Drive links
    const documentData = {
        'admission-letter': {
            title: 'Student Admission Letter',
            url: 'https://drive.google.com/file/d/1example_admission/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_admission/view'
        },
        'fee-structure': {
            title: 'School Fee Structure',
            url: 'https://drive.google.com/file/d/1example_fee_structure/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_fee_structure/view'
        },
        'payment-receipt': {
            title: 'Payment Receipt',
            url: 'https://drive.google.com/file/d/1example_payment_receipt/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_payment_receipt/view'
        },
        'financial-statement': {
            title: 'Financial Statement',
            url: 'https://drive.google.com/file/d/1example_financial_statement/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_financial_statement/view'
        },
        'student-id': {
            title: 'Student ID Card',
            url: 'https://drive.google.com/file/d/1example_student_id/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_student_id/view'
        },
        'access-card': {
            title: 'School Access Card',
            url: 'https://drive.google.com/file/d/1example_access_card/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_access_card/view'
        },
        'gate-pass': {
            title: 'Gate Pass',
            url: 'https://drive.google.com/file/d/1example_gate_pass/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_gate_pass/view'
        },
        'visitor-pass': {
            title: 'Visitor Pass',
            url: 'https://drive.google.com/file/d/1example_visitor_pass/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_visitor_pass/view'
        },
        'progress-report': {
            title: 'Student Progress Report',
            url: 'https://drive.google.com/file/d/1example_progress_report/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_progress_report/view'
        },
        'transcript': {
            title: 'Academic Transcript',
            url: 'https://drive.google.com/file/d/1example_transcript/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_transcript/view'
        },
        'certificate': {
            title: 'Achievement Certificate',
            url: 'https://drive.google.com/file/d/1example_certificate/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_certificate/view'
        },
        'attendance-report': {
            title: 'Attendance Report',
            url: 'https://drive.google.com/file/d/1example_attendance/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_attendance/view'
        },
        'class-schedule': {
            title: 'Class Schedule',
            url: 'https://drive.google.com/file/d/1example_schedule/preview',
            fullUrl: 'https://drive.google.com/file/d/1example_schedule/view'
        }
    };
    
    // Open modal function
    function openModal(documentType) {
        const doc = documentData[documentType];
        if (!doc) return;
        
        modalTitle.textContent = doc.title;
        modalIframe.src = doc.url;
        fullDocumentBtn.onclick = () => window.open(doc.fullUrl, '_blank');
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Add loading state
        modalIframe.style.opacity = '0.5';
        modalIframe.onload = () => {
            modalIframe.style.opacity = '1';
        };
    }
    
    // Close modal function
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        modalIframe.src = '';
    }
    
    // Event listeners for preview buttons
    previewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const documentType = this.getAttribute('data-document');
            openModal(documentType);
        });
    });
    
    // Close modal events
    modalClose.addEventListener('click', closeModal);
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
    
    // Handle iframe load errors
    modalIframe.addEventListener('error', function() {
        this.src = 'about:blank';
        this.contentDocument.body.innerHTML = '<div style="padding: 40px; text-align: center; font-family: Arial, sans-serif;"><h3>Document Preview Unavailable</h3><p>This document preview is currently unavailable. Please use the "View Full Document" button to access the complete document.</p></div>';
    });
    
    // Smooth scroll animation for document cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    entry.target.style.transition = 'all 0.6s ease';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, Math.random() * 200);
                
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe all document cards
    document.querySelectorAll('.document-card').forEach(card => {
        observer.observe(card);
    });
    
    // Add hover sound effect (optional)
    document.querySelectorAll('.document-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Loading animation for modal
    function showModalLoading() {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'modal-loading';
        loadingDiv.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 200px;">
                <div style="width: 50px; height: 50px; border: 4px solid #f3f3f3; border-top: 4px solid var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 20px; color: var(--text-light);">Loading document preview...</p>
            </div>
        `;
        
        return loadingDiv;
    }
    
    // Add spinner CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .modal-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
    `;
    document.head.appendChild(style);
});