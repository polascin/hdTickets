<section class="social-proof slide-up">
    <div class="section-header">
        <h2 class="section-title">Trusted by Sports Fans Worldwide</h2>
        <p class="section-subtitle">
            Join thousands of satisfied customers who never miss their team again
        </p>
    </div>
    
    <div class="testimonials-grid">
        <div class="testimonial-card">
            <div class="testimonial-rating">
                <span class="star">⭐</span>
                <span class="star">⭐</span>
                <span class="star">⭐</span>
                <span class="star">⭐</span>
                <span class="star">⭐</span>
            </div>
            <p class="testimonial-text">
                "HD Tickets saved me hundreds on Lakers tickets. The real-time alerts helped me snag courtside seats 
                for 40% less than face value. Game changer!"
            </p>
            <div class="testimonial-author">
                <div class="author-avatar">🏀</div>
                <div class="author-info">
                    <div class="author-name">Marcus Johnson</div>
                    <div class="author-role">Lakers Season Ticket Holder</div>
                </div>
            </div>
        </div>
        
        <div class="testimonial-card">
            <div class="testimonial-rating">
                <span class="star">⭐</span>
                <span class="star">⭐</span>
                <span class="star">⭐</span>
                <span class="star">⭐</span>
                <span class="star">⭐</span>
            </div>
            <p class="testimonial-text">
                "As a Manchester United fan living in the US, finding good deals on Premier League matches was impossible. 
                HD Tickets monitors everything automatically!"
            </p>
            <div class="testimonial-author">
                <div class="author-avatar">⚽</div>
                <div class="author-info">
                    <div class="author-name">Sarah Chen</div>
                    <div class="author-role">Premier League Fan</div>
                </div>
            </div>
        </div>
        
        <div class="testimonial-card">
            <div class="testimonial-rating">
                <span class="star">⭐</span>
                <span class="star">⭐</span>
                <span class="star">⭐</span>
                <span class="star">⭐</span>
                <span class="star">⭐</span>
            </div>
            <p class="testimonial-text">
                "The agent dashboard gives me everything I need to help my clients. Professional tools, unlimited access, 
                and the best customer support in the business."
            </p>
            <div class="testimonial-author">
                <div class="author-avatar">🏆</div>
                <div class="author-info">
                    <div class="author-name">David Rodriguez</div>
                    <div class="author-role">Sports Agent</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="trust-indicators">
        <div class="trust-item">
            <div class="trust-icon">🔒</div>
            <div class="trust-text">
                <div class="trust-title">SSL Secured</div>
                <div class="trust-subtitle">Bank-level encryption</div>
            </div>
        </div>
        
        <div class="trust-item">
            <div class="trust-icon">🛡️</div>
            <div class="trust-text">
                <div class="trust-title">GDPR Compliant</div>
                <div class="trust-subtitle">Privacy protected</div>
            </div>
        </div>
        
        <div class="trust-item">
            <div class="trust-icon">💳</div>
            <div class="trust-text">
                <div class="trust-title">Secure Payments</div>
                <div class="trust-subtitle">Stripe & PayPal</div>
            </div>
        </div>
        
        <div class="trust-item">
            <div class="trust-icon">📞</div>
            <div class="trust-text">
                <div class="trust-title">24/7 Support</div>
                <div class="trust-subtitle">Always here to help</div>
            </div>
        </div>
    </div>
</section>

<style>
.social-proof {
    margin: 100px 0;
    padding: 60px 0;
    background: rgba(255, 255, 255, 0.02);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-title {
    font-size: 48px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 16px;
    background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.section-subtitle {
    font-size: 18px;
    color: rgba(255, 255, 255, 0.7);
    max-width: 600px;
    margin: 0 auto;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 32px;
    margin-bottom: 60px;
}

.testimonial-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    padding: 32px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.testimonial-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6);
}

.testimonial-card:hover {
    transform: translateY(-8px);
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.25);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.testimonial-rating {
    margin-bottom: 20px;
}

.star {
    font-size: 18px;
    margin-right: 4px;
    animation: sparkle 2s ease-in-out infinite;
}

.star:nth-child(1) { animation-delay: 0s; }
.star:nth-child(2) { animation-delay: 0.2s; }
.star:nth-child(3) { animation-delay: 0.4s; }
.star:nth-child(4) { animation-delay: 0.6s; }
.star:nth-child(5) { animation-delay: 0.8s; }

.testimonial-text {
    font-size: 16px;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 24px;
    font-style: italic;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 16px;
}

.author-avatar {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.author-name {
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 4px;
}

.author-role {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
}

.trust-indicators {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
    margin-top: 40px;
}

.trust-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    transition: all 0.3s ease;
}

.trust-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-4px);
}

.trust-icon {
    font-size: 28px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
}

.trust-title {
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 4px;
}

.trust-subtitle {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
}

@keyframes sparkle {
    0%, 100% { 
        opacity: 1;
        transform: scale(1);
    }
    50% { 
        opacity: 0.7;
        transform: scale(1.1);
    }
}

@media (max-width: 768px) {
    .section-title {
        font-size: 36px;
    }
    
    .testimonials-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .testimonial-card {
        padding: 24px;
    }
    
    .trust-indicators {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .trust-item {
        padding: 16px;
    }
}
</style>