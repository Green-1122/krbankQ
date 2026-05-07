-- ============================================
-- KrBank Database Schema
-- Full MySQL schema for the banking platform
-- ============================================

CREATE DATABASE IF NOT EXISTS krbank_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE krbank_db;

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    pin VARCHAR(255) DEFAULT NULL,
    pin_active TINYINT(1) DEFAULT 0,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'suspended', 'pending', 'locked') DEFAULT 'pending',
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(64) DEFAULT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    profile_photo VARCHAR(255) DEFAULT NULL,
    address_line1 VARCHAR(255) DEFAULT NULL,
    address_line2 VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    state VARCHAR(100) DEFAULT NULL,
    zip_code VARCHAR(20) DEFAULT NULL,
    country VARCHAR(100) DEFAULT 'United States',
    date_of_birth DATE DEFAULT NULL,
    ssn_last4 VARCHAR(4) DEFAULT NULL,
    two_factor_enabled TINYINT(1) DEFAULT 0,
    two_factor_secret VARCHAR(255) DEFAULT NULL,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    dark_mode TINYINT(1) DEFAULT 0,
    notification_email TINYINT(1) DEFAULT 1,
    notification_sms TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- ============================================
-- ACCOUNTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_number VARCHAR(20) NOT NULL UNIQUE,
    account_type ENUM('checking', 'savings', 'investment') NOT NULL DEFAULT 'checking',
    account_name VARCHAR(100) DEFAULT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    available_balance DECIMAL(15,2) DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'USD',
    status ENUM('active', 'frozen', 'closed') DEFAULT 'active',
    is_primary TINYINT(1) DEFAULT 0,
    interest_rate DECIMAL(5,4) DEFAULT 0.0000,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_account_number (account_number)
) ENGINE=InnoDB;

-- ============================================
-- CARDS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    card_number VARCHAR(255) NOT NULL,
    card_number_masked VARCHAR(20) NOT NULL,
    card_type ENUM('virtual', 'physical') NOT NULL DEFAULT 'virtual',
    card_network ENUM('visa', 'mastercard', 'amex') NOT NULL DEFAULT 'visa',
    cardholder_name VARCHAR(200) NOT NULL,
    expiry_month TINYINT NOT NULL,
    expiry_year SMALLINT NOT NULL,
    cvv VARCHAR(255) NOT NULL,
    billing_address TEXT DEFAULT NULL,
    spending_limit DECIMAL(15,2) DEFAULT 5000.00,
    daily_limit DECIMAL(15,2) DEFAULT 2000.00,
    status ENUM('active', 'frozen', 'cancelled', 'expired') DEFAULT 'active',
    is_frozen TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================
-- TRANSACTIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    transaction_ref VARCHAR(30) NOT NULL UNIQUE,
    type ENUM('credit', 'debit') NOT NULL,
    category ENUM('transfer', 'deposit', 'withdrawal', 'payment', 'fee', 'interest', 'stock', 'loan', 'card', 'other') DEFAULT 'other',
    amount DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'USD',
    description TEXT DEFAULT NULL,
    recipient_name VARCHAR(200) DEFAULT NULL,
    recipient_account VARCHAR(50) DEFAULT NULL,
    recipient_bank VARCHAR(200) DEFAULT NULL,
    status ENUM('pending', 'completed', 'failed', 'reversed', 'on_hold') DEFAULT 'pending',
    metadata JSON DEFAULT NULL,
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_account (account_id),
    INDEX idx_ref (transaction_ref),
    INDEX idx_date (transaction_date),
    INDEX idx_type (type),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- TRANSFERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_id INT DEFAULT NULL,
    from_account_id INT NOT NULL,
    transfer_type ENUM('local', 'international', 'crypto') NOT NULL,
    recipient_name VARCHAR(200) NOT NULL,
    recipient_email VARCHAR(255) DEFAULT NULL,
    recipient_account VARCHAR(100) DEFAULT NULL,
    recipient_bank VARCHAR(200) DEFAULT NULL,
    recipient_bank_code VARCHAR(50) DEFAULT NULL,
    swift_code VARCHAR(20) DEFAULT NULL,
    routing_number VARCHAR(20) DEFAULT NULL,
    iban VARCHAR(50) DEFAULT NULL,
    wallet_address VARCHAR(255) DEFAULT NULL,
    crypto_type ENUM('BTC', 'ETH', 'USDT', 'BNB') DEFAULT NULL,
    amount DECIMAL(15,2) NOT NULL,
    fee DECIMAL(10,2) DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'USD',
    cot_code VARCHAR(100) DEFAULT NULL,
    imf_code VARCHAR(100) DEFAULT NULL,
    tax_code VARCHAR(100) DEFAULT NULL,
    require_cot TINYINT(1) DEFAULT 0,
    require_imf TINYINT(1) DEFAULT 0,
    require_tax TINYINT(1) DEFAULT 0,
    description TEXT DEFAULT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'awaiting_code') DEFAULT 'pending',
    scheduled_date DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (from_account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_type (transfer_type)
) ENGINE=InnoDB;

-- ============================================
-- DEPOSITS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    transaction_id INT DEFAULT NULL,
    deposit_method ENUM('bank_transfer', 'crypto', 'usdt', 'paypal') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    crypto_type VARCHAR(10) DEFAULT NULL,
    reference VARCHAR(100) DEFAULT NULL,
    proof_file VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- LOANS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    loan_type ENUM('personal', 'mortgage', 'auto', 'business', 'student', 'emergency') NOT NULL,
    loan_number VARCHAR(20) NOT NULL UNIQUE,
    amount_requested DECIMAL(15,2) NOT NULL,
    amount_approved DECIMAL(15,2) DEFAULT NULL,
    interest_rate DECIMAL(5,2) DEFAULT 5.50,
    term_months INT NOT NULL DEFAULT 12,
    monthly_payment DECIMAL(10,2) DEFAULT NULL,
    total_paid DECIMAL(15,2) DEFAULT 0.00,
    remaining_balance DECIMAL(15,2) DEFAULT NULL,
    purpose TEXT DEFAULT NULL,
    collateral TEXT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'active', 'paid', 'defaulted') DEFAULT 'pending',
    approved_at DATETIME DEFAULT NULL,
    next_payment_date DATE DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- LOAN PAYMENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS loan_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    principal DECIMAL(10,2) DEFAULT 0.00,
    interest DECIMAL(10,2) DEFAULT 0.00,
    payment_date DATE NOT NULL,
    status ENUM('pending', 'paid', 'late', 'missed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- INVESTMENTS / SAVINGS GOALS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS savings_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT NOT NULL,
    goal_name VARCHAR(200) NOT NULL,
    target_amount DECIMAL(15,2) NOT NULL,
    current_amount DECIMAL(15,2) DEFAULT 0.00,
    category ENUM('emergency', 'vacation', 'home', 'education', 'retirement', 'custom') DEFAULT 'custom',
    target_date DATE DEFAULT NULL,
    auto_save_amount DECIMAL(10,2) DEFAULT 0.00,
    auto_save_frequency ENUM('daily', 'weekly', 'monthly') DEFAULT 'monthly',
    status ENUM('active', 'completed', 'paused', 'cancelled') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================
-- STOCKS / PORTFOLIO TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS stocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    symbol VARCHAR(10) NOT NULL,
    company_name VARCHAR(200) NOT NULL,
    current_price DECIMAL(12,2) NOT NULL,
    previous_close DECIMAL(12,2) DEFAULT NULL,
    market_cap VARCHAR(20) DEFAULT NULL,
    sector VARCHAR(100) DEFAULT NULL,
    logo_url VARCHAR(255) DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_symbol (symbol)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_stocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stock_id INT NOT NULL,
    account_id INT NOT NULL,
    shares DECIMAL(12,6) NOT NULL,
    avg_buy_price DECIMAL(12,2) NOT NULL,
    total_invested DECIMAL(15,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS stock_trades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stock_id INT NOT NULL,
    account_id INT NOT NULL,
    trade_type ENUM('buy', 'sell') NOT NULL,
    shares DECIMAL(12,6) NOT NULL,
    price_per_share DECIMAL(12,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    fee DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================
-- SECURITY CODES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS security_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code_type ENUM('cot', 'imf', 'tax') NOT NULL,
    code_value VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE INDEX idx_user_code (user_id, code_type)
) ENGINE=InnoDB;

-- ============================================
-- FEATURE TOGGLES TABLE (Admin controlled)
-- ============================================
CREATE TABLE IF NOT EXISTS feature_toggles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    feature_name VARCHAR(100) NOT NULL,
    is_enabled TINYINT(1) DEFAULT 1,
    applies_to ENUM('global', 'user') DEFAULT 'global',
    updated_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_feature (feature_name),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================
-- NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'transaction') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read)
) ENGINE=InnoDB;

-- ============================================
-- AUDIT LOG TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(200) NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INT DEFAULT NULL,
    old_value TEXT DEFAULT NULL,
    new_value TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

-- ============================================
-- SITE SETTINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- SEED DATA
-- ============================================

-- Default admin user (password: Admin@123)
INSERT INTO users (uuid, first_name, last_name, email, password_hash, role, status, email_verified, created_at) VALUES
('550e8400-e29b-41d4-a716-446655440000', 'System', 'Admin', 'admin@krbank.com', '$2y$12$LJ3m4ys3Gzl0MBKxRJHNc.OqR1VFGwH2SqXjE1sWLq6Y4tMdGnVHy', 'admin', 'active', 1, NOW());

-- Sample stocks
INSERT INTO stocks (symbol, company_name, current_price, previous_close, market_cap, sector) VALUES
('AAPL', 'Apple Inc.', 189.84, 188.00, '2.95T', 'Technology'),
('GOOGL', 'Alphabet Inc.', 141.80, 140.50, '1.76T', 'Technology'),
('MSFT', 'Microsoft Corporation', 378.91, 377.00, '2.81T', 'Technology'),
('AMZN', 'Amazon.com Inc.', 178.25, 176.80, '1.85T', 'Consumer Cyclical'),
('TSLA', 'Tesla Inc.', 248.42, 245.00, '790B', 'Automotive'),
('JPM', 'JPMorgan Chase & Co.', 196.54, 195.00, '566B', 'Financial'),
('V', 'Visa Inc.', 279.88, 278.50, '573B', 'Financial'),
('NVDA', 'NVIDIA Corporation', 495.22, 490.00, '1.22T', 'Technology'),
('META', 'Meta Platforms Inc.', 355.67, 352.00, '913B', 'Technology'),
('BRK.B', 'Berkshire Hathaway', 362.45, 360.00, '795B', 'Financial');

-- Default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'KrBank', 'text'),
('maintenance_mode', '0', 'boolean'),
('require_cot_international', '1', 'boolean'),
('require_imf_international', '0', 'boolean'),
('require_tax_international', '0', 'boolean'),
('transfer_fee_local', '0.00', 'number'),
('transfer_fee_international', '25.00', 'number'),
('transfer_fee_crypto', '2.50', 'number'),
('max_transfer_local', '50000.00', 'number'),
('max_transfer_international', '100000.00', 'number'),
('crypto_wallet_btc', '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 'text'),
('crypto_wallet_eth', '0x742d35Cc6634C0532925a3b844Bc9e7595f2bD18', 'text'),
('crypto_wallet_usdt', 'TN2Ybc4th2izUCuA7FdkXGK3HqhS1rKgaw', 'text'),
('crypto_wallet_bnb', 'bnb1grpf0955h0ber44e6un24e9hr7xu9kcexxnm8d', 'text'),
('paypal_email', 'deposits@krbank.com', 'text');

-- Default feature toggles
INSERT INTO feature_toggles (feature_name, is_enabled, applies_to) VALUES
('cot_code', 1, 'global'),
('imf_code', 0, 'global'),
('tax_code', 0, 'global'),
('crypto_transfers', 1, 'global'),
('stock_trading', 1, 'global'),
('loan_applications', 1, 'global'),
('international_transfers', 1, 'global'),
('pin_protection', 1, 'global');
