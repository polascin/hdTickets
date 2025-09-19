@extends('layouts.app-v2')

@section('title', 'HD Design System Style Guide')


@section('content')
<div class="hd-style-guide">
    <div class="hd-container">
        <!-- Header -->
        <div class="hd-page-header">
            <div class="hd-page-header__content">
                <h1 class="hd-page-header__title">HD Design System</h1>
                <p class="hd-page-header__subtitle">Comprehensive style guide for HD Tickets sports events platform</p>
            </div>
        </div>

        <div class="hd-grid hd-grid--4">
            <!-- Navigation -->
            <div>
                <nav class="hd-navigation">
                    <h3 style="margin-top: 0; margin-bottom: var(--hd-spacing-4);">Contents</h3>
                    <ul>
                        <li><a href="#colors">Colors</a></li>
                        <li><a href="#typography">Typography</a></li>
                        <li><a href="#spacing">Spacing</a></li>
                        <li><a href="#buttons">Buttons</a></li>
                        <li><a href="#forms">Forms</a></li>
                        <li><a href="#cards">Cards</a></li>
                        <li><a href="#layout">Layout</a></li>
                        <li><a href="#components">Components</a></li>
                        <li><a href="#utilities">Utilities</a></li>
                        <li><a href="#icons">Icons</a></li>
                    </ul>
                </nav>
            </div>

            <!-- Content -->
            <div style="grid-column: span 3;">
                <!-- Colors Section -->
                <section id="colors" class="hd-section">
                    <div class="hd-section__header">
                        <h2 class="hd-section__title">Color System</h2>
                        <p class="hd-section__subtitle">Primary colors, semantic colors, and neutral palette</p>
                    </div>
                    <div class="hd-section__body">
                        <!-- Primary Colors -->
                        <h3>Primary Colors</h3>
                        <div style="margin-bottom: var(--hd-spacing-6);">
                            <div class="hd-color-swatch" style="background: var(--hd-primary);">
                                <div class="hd-color-info">Primary<br>#2563eb</div>
                            </div>
                            <div class="hd-color-swatch" style="background: var(--hd-secondary);">
                                <div class="hd-color-info">Secondary<br>#7c3aed</div>
                            </div>
                            <div class="hd-color-swatch" style="background: var(--hd-accent);">
                                <div class="hd-color-info">Accent<br>#06b6d4</div>
                            </div>
                        </div>

                        <!-- Status Colors -->
                        <h3>Status Colors</h3>
                        <div style="margin-bottom: var(--hd-spacing-6);">
                            <div class="hd-color-swatch" style="background: var(--hd-success);">
                                <div class="hd-color-info">Success<br>#10b981</div>
                            </div>
                            <div class="hd-color-swatch" style="background: var(--hd-warning);">
                                <div class="hd-color-info">Warning<br>#f59e0b</div>
                            </div>
                            <div class="hd-color-swatch" style="background: var(--hd-error);">
                                <div class="hd-color-info">Error<br>#ef4444</div>
                            </div>
                            <div class="hd-color-swatch" style="background: var(--hd-info);">
                                <div class="hd-color-info">Info<br>#3b82f6</div>
                            </div>
                        </div>

                        <!-- Neutral Colors -->
                        <h3>Neutral Colors</h3>
                        <div style="margin-bottom: var(--hd-spacing-6);">
                            <div class="hd-color-swatch" style="background: var(--hd-gray-100);">
                                <div class="hd-color-info">Gray 100<br>#f3f4f6</div>
                            </div>
                            <div class="hd-color-swatch" style="background: var(--hd-gray-300);">
                                <div class="hd-color-info">Gray 300<br>#d1d5db</div>
                            </div>
                            <div class="hd-color-swatch" style="background: var(--hd-gray-500);">
                                <div class="hd-color-info">Gray 500<br>#6b7280</div>
                            </div>
                            <div class="hd-color-swatch" style="background: var(--hd-gray-700);">
                                <div class="hd-color-info">Gray 700<br>#374151</div>
                            </div>
                            <div class="hd-color-swatch" style="background: var(--hd-gray-900);">
                                <div class="hd-color-info">Gray 900<br>#111827</div>
                            </div>
                        </div>

                        <div class="hd-code-block">
/* Using HD Color Variables */
.my-element {
    background-color: var(--hd-primary);
    color: var(--hd-text-inverse);
    border: 1px solid var(--hd-border-primary);
}

/* Status colors for feedback */
.success-message {
    color: var(--hd-success);
    background-color: var(--hd-success-50);
}
                        </div>
                    </div>
                </section>

                <!-- Typography Section -->
                <section id="typography" class="hd-section">
                    <div class="hd-section__header">
                        <h2 class="hd-section__title">Typography</h2>
                        <p class="hd-section__subtitle">Font families, sizes, weights, and text styles</p>
                    </div>
                    <div class="hd-section__body">
                        <div class="hd-typography-demo">
                            <h1 style="font-size: var(--hd-text-4xl);">Heading 1 - Main Page Title</h1>
                            <h2 style="font-size: var(--hd-text-3xl);">Heading 2 - Section Title</h2>
                            <h3 style="font-size: var(--hd-text-2xl);">Heading 3 - Subsection</h3>
                            <h4 style="font-size: var(--hd-text-xl);">Heading 4 - Component Title</h4>
                            <h5 style="font-size: var(--hd-text-lg);">Heading 5 - Small Heading</h5>
                            <h6 style="font-size: var(--hd-text-base);">Heading 6 - Micro Heading</h6>
                        </div>

                        <div style="margin: var(--hd-spacing-6) 0;">
                            <p style="font-size: var(--hd-text-lg); font-weight: var(--hd-font-normal); color: var(--hd-text-primary);">
                                <strong>Body Large:</strong> This is the large body text style used for important content and introductory paragraphs.
                            </p>
                            <p style="font-size: var(--hd-text-base); font-weight: var(--hd-font-normal); color: var(--hd-text-primary);">
                                <strong>Body Regular:</strong> This is the default body text style used throughout the application for regular content.
                            </p>
                            <p style="font-size: var(--hd-text-sm); font-weight: var(--hd-font-normal); color: var(--hd-text-secondary);">
                                <strong>Body Small:</strong> This is the small body text style used for secondary information and captions.
                            </p>
                            <p style="font-size: var(--hd-text-xs); font-weight: var(--hd-font-medium); color: var(--hd-text-muted); text-transform: uppercase; letter-spacing: var(--hd-tracking-wide);">
                                <strong>Caption:</strong> This is the caption style used for labels and metadata.
                            </p>
                        </div>

                        <div class="hd-code-block">
/* Typography Classes */
.hd-text-4xl { font-size: var(--hd-text-4xl); } /* 36px */
.hd-text-3xl { font-size: var(--hd-text-3xl); } /* 30px */
.hd-text-2xl { font-size: var(--hd-text-2xl); } /* 24px */
.hd-text-xl  { font-size: var(--hd-text-xl); }  /* 20px */
.hd-text-lg  { font-size: var(--hd-text-lg); }  /* 18px */
.hd-text-base { font-size: var(--hd-text-base); } /* 16px */
.hd-text-sm  { font-size: var(--hd-text-sm); }  /* 14px */
.hd-text-xs  { font-size: var(--hd-text-xs); }  /* 12px */

/* Font weights */
.hd-font-light { font-weight: var(--hd-font-light); }     /* 300 */
.hd-font-normal { font-weight: var(--hd-font-normal); }   /* 400 */
.hd-font-medium { font-weight: var(--hd-font-medium); }   /* 500 */
.hd-font-semibold { font-weight: var(--hd-font-semibold); } /* 600 */
.hd-font-bold { font-weight: var(--hd-font-bold); }       /* 700 */
                        </div>
                    </div>
                </section>

                <!-- Spacing Section -->
                <section id="spacing" class="hd-section">
                    <div class="hd-section__header">
                        <h2 class="hd-section__title">Spacing System</h2>
                        <p class="hd-section__subtitle">Consistent spacing scale based on 4px grid</p>
                    </div>
                    <div class="hd-section__body">
                        <div class="hd-spacing-demo">
                            <div class="hd-spacing-bar" style="height: calc(var(--hd-spacing-1) * 4);">1<br>4px</div>
                            <div class="hd-spacing-bar" style="height: calc(var(--hd-spacing-2) * 4);">2<br>8px</div>
                            <div class="hd-spacing-bar" style="height: calc(var(--hd-spacing-3) * 4);">3<br>12px</div>
                            <div class="hd-spacing-bar" style="height: calc(var(--hd-spacing-4) * 4);">4<br>16px</div>
                            <div class="hd-spacing-bar" style="height: calc(var(--hd-spacing-5) * 4);">5<br>20px</div>
                            <div class="hd-spacing-bar" style="height: calc(var(--hd-spacing-6) * 4);">6<br>24px</div>
                            <div class="hd-spacing-bar" style="height: calc(var(--hd-spacing-8) * 4);">8<br>32px</div>
                            <div class="hd-spacing-bar" style="height: calc(var(--hd-spacing-10) * 4);">10<br>40px</div>
                            <div class="hd-spacing-bar" style="height: calc(var(--hd-spacing-12) * 4);">12<br>48px</div>
                        </div>

                        <div style="margin-top: var(--hd-spacing-6);">
                            <h4>Usage Guidelines</h4>
                            <ul style="color: var(--hd-text-secondary); line-height: var(--hd-leading-relaxed);">
                                <li><strong>--hd-spacing-1 to 3:</strong> Use for small gaps, padding inside buttons</li>
                                <li><strong>--hd-spacing-4 to 6:</strong> Use for component padding, small margins</li>
                                <li><strong>--hd-spacing-8 to 12:</strong> Use for section spacing, layout gaps</li>
                                <li><strong>--hd-spacing-16+:</strong> Use for page-level spacing, large layouts</li>
                            </ul>
                        </div>

                        <div class="hd-code-block">
/* Spacing Variables */
--hd-spacing-1: 0.25rem;   /* 4px */
--hd-spacing-2: 0.5rem;    /* 8px */
--hd-spacing-3: 0.75rem;   /* 12px */
--hd-spacing-4: 1rem;      /* 16px */
--hd-spacing-5: 1.25rem;   /* 20px */
--hd-spacing-6: 1.5rem;    /* 24px */

/* Usage Examples */
.hd-card {
    padding: var(--hd-card-padding); /* 24px */
    margin-bottom: var(--hd-spacing-6);
}

.hd-btn {
    padding: var(--hd-spacing-3) var(--hd-spacing-4);
}
                        </div>
                    </div>
                </section>

                <!-- Buttons Section -->
                <section id="buttons" class="hd-section">
                    <div class="hd-section__header">
                        <h2 class="hd-section__title">Button Components</h2>
                        <p class="hd-section__subtitle">Consistent button styles and interactive elements</p>
                    </div>
                    <div class="hd-section__body">
                        <!-- Primary Buttons -->
                        <h4>Primary Buttons</h4>
                        <div class="hd-component-showcase">
                            <div class="hd-flex hd-flex--wrap" style="gap: var(--hd-spacing-3); margin-bottom: var(--hd-spacing-4);">
                                <button class="hd-btn hd-btn--primary hd-btn--xs">Extra Small</button>
                                <button class="hd-btn hd-btn--primary hd-btn--sm">Small</button>
                                <button class="hd-btn hd-btn--primary">Default</button>
                                <button class="hd-btn hd-btn--primary hd-btn--lg">Large</button>
                                <button class="hd-btn hd-btn--primary hd-btn--xl">Extra Large</button>
                            </div>
                        </div>

                        <!-- Button Variants -->
                        <h4>Button Variants</h4>
                        <div class="hd-component-showcase">
                            <div class="hd-flex hd-flex--wrap" style="gap: var(--hd-spacing-3); margin-bottom: var(--hd-spacing-4);">
                                <button class="hd-btn hd-btn--primary">Primary</button>
                                <button class="hd-btn hd-btn--secondary">Secondary</button>
                                <button class="hd-btn hd-btn--outline">Outline</button>
                                <button class="hd-btn hd-btn--ghost">Ghost</button>
                            </div>
                            <div class="hd-flex hd-flex--wrap" style="gap: var(--hd-spacing-3);">
                                <button class="hd-btn hd-btn--success">Success</button>
                                <button class="hd-btn hd-btn--warning">Warning</button>
                                <button class="hd-btn hd-btn--danger">Danger</button>
                                <button class="hd-btn hd-btn--primary" disabled>Disabled</button>
                            </div>
                        </div>

                        <!-- Button States -->
                        <h4>Button States</h4>
                        <div class="hd-component-showcase">
                            <div class="hd-flex hd-flex--wrap" style="gap: var(--hd-spacing-3);">
                                <button class="hd-btn hd-btn--primary hd-btn--loading">
                                    <span class="hd-btn__text">Loading</span>
                                </button>
                                <button class="hd-btn hd-btn--primary hd-btn--icon">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <button class="hd-btn hd-btn--primary">
                                    <i class="fas fa-download"></i>
                                    Download
                                </button>
                            </div>
                        </div>

                        <div class="hd-code-block">
<!-- Button Examples -->
<button class="hd-btn hd-btn--primary">Primary Button</button>
<button class="hd-btn hd-btn--secondary hd-btn--lg">Large Secondary</button>
<button class="hd-btn hd-btn--outline hd-btn--sm">Small Outline</button>

<!-- Button with Icon -->
<button class="hd-btn hd-btn--primary">
    <i class="fas fa-download"></i>
    Download
</button>

<!-- Loading Button -->
<button class="hd-btn hd-btn--primary hd-btn--loading">
    <span class="hd-btn__text">Processing</span>
</button>
                        </div>
                    </div>
                </section>

                <!-- Forms Section -->
                <section id="forms" class="hd-section">
                    <div class="hd-section__header">
                        <h2 class="hd-section__title">Form Components</h2>
                        <p class="hd-section__subtitle">Input fields, selects, and form layouts</p>
                    </div>
                    <div class="hd-section__body">
                        <div class="hd-component-showcase">
                            <div class="hd-form">
                                <div class="hd-form-group">
                                    <label class="hd-label hd-label--required">Event Name</label>
                                    <input type="text" class="hd-input" placeholder="Enter sports event name">
                                    <div class="hd-form-help">Choose a descriptive name for the sports event</div>
                                </div>

                                <div class="hd-form-group">
                                    <label class="hd-label hd-label--optional">Event Description</label>
                                    <textarea class="hd-input hd-textarea" placeholder="Describe the event..."></textarea>
                                </div>

                                <div class="hd-form-group">
                                    <label class="hd-label">Sport Category</label>
                                    <select class="hd-input hd-select">
                                        <option>Select sport category</option>
                                        <option>Football</option>
                                        <option>Rugby</option>
                                        <option>Cricket</option>
                                        <option>Tennis</option>
                                    </select>
                                </div>

                                <div class="hd-form-group">
                                    <div class="hd-form-control">
                                        <input type="checkbox" class="hd-checkbox" id="premium">
                                        <label class="hd-label" for="premium">Premium Event</label>
                                    </div>
                                </div>

                                <div class="hd-form-group">
                                    <div class="hd-form-control">
                                        <input type="radio" class="hd-radio" id="public" name="visibility">
                                        <label class="hd-label" for="public">Public Event</label>
                                    </div>
                                    <div class="hd-form-control">
                                        <input type="radio" class="hd-radio" id="private" name="visibility">
                                        <label class="hd-label" for="private">Private Event</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form States -->
                        <h4>Form States</h4>
                        <div class="hd-component-showcase">
                            <div class="hd-form hd-grid hd-grid--2">
                                <div class="hd-form-group">
                                    <label class="hd-label">Success State</label>
                                    <input type="text" class="hd-input hd-input--success" value="Valid input">
                                    <div class="hd-form-success">
                                        <i class="fas fa-check-circle"></i>
                                        Input is valid
                                    </div>
                                </div>

                                <div class="hd-form-group">
                                    <label class="hd-label">Error State</label>
                                    <input type="text" class="hd-input hd-input--error" value="Invalid input">
                                    <div class="hd-form-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        This field is required
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hd-code-block">
<!-- Basic Form Elements -->
<div class="hd-form-group">
    <label class="hd-label hd-label--required">Label</label>
    <input type="text" class="hd-input" placeholder="Placeholder">
    <div class="hd-form-help">Help text</div>
</div>

<!-- Select -->
<select class="hd-input hd-select">
    <option>Choose option</option>
</select>

<!-- Checkbox -->
<div class="hd-form-control">
    <input type="checkbox" class="hd-checkbox" id="check1">
    <label class="hd-label" for="check1">Checkbox Label</label>
</div>

<!-- Error State -->
<input type="text" class="hd-input hd-input--error">
<div class="hd-form-error">Error message</div>
                        </div>
                    </div>
                </section>

                <!-- Cards Section -->
                <section id="cards" class="hd-section">
                    <div class="hd-section__header">
                        <h2 class="hd-section__title">Card Components</h2>
                        <p class="hd-section__subtitle">Content containers and data presentation</p>
                    </div>
                    <div class="hd-section__body">
                        <!-- Basic Cards -->
                        <h4>Basic Cards</h4>
                        <div class="hd-card-grid hd-card-grid--compact">
                            <div class="hd-card">
                                <div class="hd-card__header">
                                    <h3 class="hd-card__title">Default Card</h3>
                                    <p class="hd-card__subtitle">Basic card layout</p>
                                </div>
                                <div class="hd-card__body">
                                    <p class="hd-card__description">This is a basic card component with header and body sections.</p>
                                </div>
                            </div>

                            <div class="hd-card hd-card--elevated">
                                <div class="hd-card__body">
                                    <h3 class="hd-card__title">Elevated Card</h3>
                                    <p class="hd-card__description">This card has enhanced shadow for emphasis.</p>
                                </div>
                            </div>

                            <div class="hd-card hd-card--interactive">
                                <div class="hd-card__body">
                                    <h3 class="hd-card__title">Interactive Card</h3>
                                    <p class="hd-card__description">This card has hover effects and is clickable.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Cards -->
                        <h4>Sports Event Ticket Cards</h4>
                        <div class="hd-card-grid">
                            <div class="hd-card hd-ticket-card hd-card--interactive">
                                <div class="hd-card__body">
                                    <h3 class="hd-card__title">Premier League Final</h3>
                                    <p class="hd-card__subtitle">Wembley Stadium</p>
                                    <p class="hd-card__description">Experience the most anticipated match of the season.</p>
                                    <div class="hd-card__actions hd-card__actions--between">
                                        <div style="color: var(--hd-success); font-weight: var(--hd-font-semibold);">
                                            £150.00
                                        </div>
                                        <button class="hd-btn hd-btn--primary hd-btn--sm">View Details</button>
                                    </div>
                                </div>
                            </div>

                            <div class="hd-card hd-stat-card">
                                <div class="hd-card__body">
                                    <h3 class="hd-card__title">2,547</h3>
                                    <p class="hd-card__subtitle">Active Tickets</p>
                                </div>
                            </div>
                        </div>

                        <div class="hd-code-block">
<!-- Basic Card -->
<div class="hd-card">
    <div class="hd-card__header">
        <h3 class="hd-card__title">Card Title</h3>
        <p class="hd-card__subtitle">Subtitle</p>
    </div>
    <div class="hd-card__body">
        <p class="hd-card__description">Content goes here</p>
    </div>
    <div class="hd-card__footer">
        <div class="hd-card__actions">
            <button class="hd-btn hd-btn--primary">Action</button>
        </div>
    </div>
</div>

<!-- Card Variants -->
<div class="hd-card hd-card--elevated">...</div>
<div class="hd-card hd-card--interactive">...</div>
<div class="hd-card hd-card--outline">...</div>
<div class="hd-card hd-ticket-card">...</div>
<div class="hd-card hd-stat-card">...</div>
                        </div>
                    </div>
                </section>

                <!-- Layout Section -->
                <section id="layout" class="hd-section">
                    <div class="hd-section__header">
                        <h2 class="hd-section__title">Layout System</h2>
                        <p class="hd-section__subtitle">Grid system, containers, and responsive layouts</p>
                    </div>
                    <div class="hd-section__body">
                        <h4>Grid System</h4>
                        <div style="margin-bottom: var(--hd-spacing-6);">
                            <div class="hd-grid hd-grid--4" style="gap: var(--hd-spacing-2);">
                                <div style="background: var(--hd-primary-100); padding: var(--hd-spacing-4); border-radius: var(--hd-radius); text-align: center;">1/4</div>
                                <div style="background: var(--hd-primary-100); padding: var(--hd-spacing-4); border-radius: var(--hd-radius); text-align: center;">1/4</div>
                                <div style="background: var(--hd-primary-100); padding: var(--hd-spacing-4); border-radius: var(--hd-radius); text-align: center;">1/4</div>
                                <div style="background: var(--hd-primary-100); padding: var(--hd-spacing-4); border-radius: var(--hd-radius); text-align: center;">1/4</div>
                            </div>
                        </div>

                        <div style="margin-bottom: var(--hd-spacing-6);">
                            <div class="hd-grid hd-grid--3" style="gap: var(--hd-spacing-2);">
                                <div style="background: var(--hd-secondary-100); padding: var(--hd-spacing-4); border-radius: var(--hd-radius); text-align: center;">1/3</div>
                                <div style="background: var(--hd-secondary-100); padding: var(--hd-spacing-4); border-radius: var(--hd-radius); text-align: center;">1/3</div>
                                <div style="background: var(--hd-secondary-100); padding: var(--hd-spacing-4); border-radius: var(--hd-radius); text-align: center;">1/3</div>
                            </div>
                        </div>

                        <h4>Flexbox Utilities</h4>
                        <div style="margin-bottom: var(--hd-spacing-6);">
                            <div class="hd-flex hd-flex--between" style="background: var(--hd-accent-50); padding: var(--hd-spacing-4); border-radius: var(--hd-radius); margin-bottom: var(--hd-spacing-2);">
                                <div>Space Between</div>
                                <div>Content</div>
                            </div>
                            <div class="hd-flex hd-flex--center" style="background: var(--hd-accent-50); padding: var(--hd-spacing-4); border-radius: var(--hd-radius);">
                                <div>Centered Content</div>
                            </div>
                        </div>

                        <div class="hd-code-block">
<!-- Grid Layouts -->
<div class="hd-grid hd-grid--2">...</div>
<div class="hd-grid hd-grid--3">...</div>
<div class="hd-grid hd-grid--4">...</div>
<div class="hd-grid hd-grid--auto-fill">...</div>

<!-- Flexbox Layouts -->
<div class="hd-flex hd-flex--column">...</div>
<div class="hd-flex hd-flex--between">...</div>
<div class="hd-flex hd-flex--center">...</div>
<div class="hd-flex hd-flex--wrap">...</div>

<!-- Container -->
<div class="hd-container">...</div>

<!-- Layout Sections -->
<div class="hd-section">
    <div class="hd-section__header">...</div>
    <div class="hd-section__body">...</div>
    <div class="hd-section__footer">...</div>
</div>
                        </div>
                    </div>
                </section>

                <!-- Utilities Section -->
                <section id="utilities" class="hd-section">
                    <div class="hd-section__header">
                        <h2 class="hd-section__title">Utility Classes</h2>
                        <p class="hd-section__subtitle">Helper classes for common styling needs</p>
                    </div>
                    <div class="hd-section__body">
                        <h4>Text Utilities</h4>
                        <div class="hd-component-showcase" style="margin-bottom: var(--hd-spacing-4);">
                            <p class="hd-text-left">Left aligned text</p>
                            <p class="hd-text-center">Center aligned text</p>
                            <p class="hd-text-right">Right aligned text</p>
                            <p class="hd-text-primary">Primary text color</p>
                            <p class="hd-text-secondary">Secondary text color</p>
                            <p class="hd-text-muted">Muted text color</p>
                        </div>

                        <h4>Spacing Utilities</h4>
                        <div class="hd-component-showcase">
                            <div style="background: var(--hd-gray-100); border-radius: var(--hd-radius);">
                                <div class="hd-p-4" style="background: var(--hd-primary-100); border-radius: var(--hd-radius);">
                                    .hd-p-4 (padding: 16px)
                                </div>
                            </div>
                        </div>

                        <div class="hd-code-block">
<!-- Text Utilities -->
.hd-text-left, .hd-text-center, .hd-text-right
.hd-text-primary, .hd-text-secondary, .hd-text-muted
.hd-text-xs, .hd-text-sm, .hd-text-base, .hd-text-lg
.hd-font-light, .hd-font-normal, .hd-font-medium, .hd-font-semibold, .hd-font-bold

<!-- Spacing Utilities -->
.hd-m-0, .hd-m-1, .hd-m-2, .hd-m-3, .hd-m-4, .hd-m-5, .hd-m-6
.hd-p-0, .hd-p-1, .hd-p-2, .hd-p-3, .hd-p-4, .hd-p-5, .hd-p-6
.hd-mx-auto, .hd-my-4

<!-- Visibility Utilities -->
.hd-hidden, .hd-sr-only
.hd-hidden-sm-down, .hd-hidden-md-up
.hd-print-hidden

<!-- Performance Utilities -->
.hd-contain-strict, .hd-contain-layout, .hd-contain-paint
.hd-content-visibility-auto
.hd-gpu-accelerate
                        </div>
                    </div>
                </section>

                <!-- Icons Section -->
                <section id="icons" class="hd-section">
                    <div class="hd-section__header">
                        <h2 class="hd-section__title">Icons & Graphics</h2>
                        <p class="hd-section__subtitle">Icon usage and sports-specific graphics</p>
                    </div>
                    <div class="hd-section__body">
                        <h4>Sports Icons</h4>
                        <div class="hd-component-showcase" style="font-size: var(--hd-text-2xl); color: var(--hd-primary);">
                            <i class="fas fa-football-ball" title="Football"></i>
                            <i class="fas fa-baseball-ball" title="Cricket"></i>
                            <i class="fas fa-table-tennis" title="Tennis"></i>
                            <i class="fas fa-trophy" title="Championship"></i>
                            <i class="fas fa-ticket-alt" title="Ticket"></i>
                            <i class="fas fa-calendar" title="Event Date"></i>
                            <i class="fas fa-map-marker-alt" title="Venue"></i>
                            <i class="fas fa-users" title="Capacity"></i>
                        </div>

                        <h4>UI Icons</h4>
                        <div class="hd-component-showcase" style="font-size: var(--hd-text-lg); color: var(--hd-text-secondary);">
                            <i class="fas fa-search" title="Search"></i>
                            <i class="fas fa-filter" title="Filter"></i>
                            <i class="fas fa-sort" title="Sort"></i>
                            <i class="fas fa-heart" title="Favorite"></i>
                            <i class="fas fa-bell" title="Notifications"></i>
                            <i class="fas fa-user" title="User"></i>
                            <i class="fas fa-cog" title="Settings"></i>
                            <i class="fas fa-download" title="Download"></i>
                            <i class="fas fa-share-alt" title="Share"></i>
                            <i class="fas fa-eye" title="View"></i>
                            <i class="fas fa-edit" title="Edit"></i>
                            <i class="fas fa-trash" title="Delete"></i>
                        </div>

                        <div class="hd-code-block">
<!-- Sports Event Icons -->
<i class="fas fa-football-ball"></i>  <!-- Football -->
<i class="fas fa-baseball-ball"></i>  <!-- Cricket -->
<i class="fas fa-table-tennis"></i>   <!-- Tennis -->
<i class="fas fa-trophy"></i>         <!-- Championship -->

<!-- Status Icons -->
<i class="fas fa-check-circle"></i>   <!-- Success -->
<i class="fas fa-exclamation-triangle"></i> <!-- Warning -->
<i class="fas fa-times-circle"></i>   <!-- Error -->
<i class="fas fa-clock"></i>          <!-- Pending -->

<!-- Interactive Icons -->
<i class="fas fa-heart"></i>          <!-- Favorite -->
<i class="fas fa-bell"></i>           <!-- Alert -->
<i class="fas fa-share-alt"></i>      <!-- Share -->
<i class="fas fa-download"></i>       <!-- Download -->
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Smooth scrolling for navigation links
document.querySelectorAll('.hd-navigation a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        const targetSection = document.querySelector(targetId);
        
        if (targetSection) {
            targetSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            
            // Update active state
            document.querySelectorAll('.hd-navigation a').forEach(link => {
                link.style.background = '';
                link.style.color = 'var(--hd-text-secondary)';
            });
            
            this.style.background = 'var(--hd-primary-50)';
            this.style.color = 'var(--hd-primary)';
        }
    });
});

// Copy color values to clipboard
document.querySelectorAll('.hd-color-swatch').forEach(swatch => {
    swatch.addEventListener('click', function() {
        const colorInfo = this.querySelector('.hd-color-info');
        if (colorInfo) {
            const colorValue = colorInfo.textContent.split('\n')[1];
            if (navigator.clipboard) {
                navigator.clipboard.writeText(colorValue);
                
                // Show feedback
                const originalText = colorInfo.innerHTML;
                colorInfo.innerHTML = 'Copied!<br>' + colorValue;
                setTimeout(() => {
                    colorInfo.innerHTML = originalText;
                }, 1000);
            }
        }
    });
});
</script>
@endpush
