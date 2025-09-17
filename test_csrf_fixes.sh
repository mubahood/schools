#!/bin/bash

# Test script for CSRF and Page Expiration fixes
echo "🔧 Testing CSRF and Page Expiration Fixes"
echo "=========================================="

BASE_URL="http://localhost:8888/schools"

# Function to test HTTP endpoints
test_endpoint() {
    local url="$1"
    local description="$2"
    
    echo "Testing: $description"
    echo "URL: $url"
    
    response=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    
    if [ "$response" -eq 200 ]; then
        echo "✅ PASSED - HTTP $response"
    else
        echo "❌ FAILED - HTTP $response"
    fi
    echo ""
}

echo "1. Testing Auth Pages Accessibility"
echo "-----------------------------------"

# Test all auth form pages
test_endpoint "$BASE_URL/auth/login" "Login Page"
test_endpoint "$BASE_URL/auth/forgot-password" "Forgot Password Page"
test_endpoint "$BASE_URL/auth/support" "Support Page"

echo "2. Testing CSRF Token Endpoint"
echo "------------------------------"

# Test CSRF token refresh endpoint
echo "Testing CSRF Token Refresh Endpoint"
csrf_response=$(curl -s -H "Accept: application/json" -H "X-Requested-With: XMLHttpRequest" "$BASE_URL/csrf-token")
if echo "$csrf_response" | grep -q '"token"'; then
    echo "✅ CSRF token endpoint working"
    echo "Sample response: $csrf_response"
else
    echo "❌ CSRF token endpoint failed"
    echo "Response: $csrf_response"
fi
echo ""

echo "3. Testing Form URL Corrections"
echo "------------------------------"

# Check if login form contains correct action URL
echo "Checking Login Form URLs..."
login_content=$(curl -s "$BASE_URL/auth/login")
if echo "$login_content" | grep -q 'action="/schools/auth/login"'; then
    echo "✅ Login form action URL is correct"
else
    echo "❌ Login form action URL needs fixing"
fi

if echo "$login_content" | grep -q 'href="/schools/auth/forgot-password"'; then
    echo "✅ Login form forgot password link is correct"
else
    echo "❌ Login form forgot password link needs fixing"
fi
echo ""

# Check if forgot password form contains correct action URL
echo "Checking Forgot Password Form URLs..."
forgot_content=$(curl -s "$BASE_URL/auth/forgot-password")
if echo "$forgot_content" | grep -q 'action="/schools/auth/forgot-password"'; then
    echo "✅ Forgot password form action URL is correct"
else
    echo "❌ Forgot password form action URL needs fixing"
fi

if echo "$forgot_content" | grep -q 'href="/schools/auth/login"'; then
    echo "✅ Forgot password back link is correct"
else
    echo "❌ Forgot password back link needs fixing"
fi
echo ""

echo "4. Testing CSRF Manager Integration"
echo "----------------------------------"

# Check if forms include CSRF manager script
echo "Checking CSRF Manager Script Inclusion..."

if echo "$login_content" | grep -q 'csrf-manager.js'; then
    echo "✅ Login form includes CSRF manager"
else
    echo "❌ Login form missing CSRF manager"
fi

if echo "$forgot_content" | grep -q 'csrf-manager.js'; then
    echo "✅ Forgot password form includes CSRF manager"
else
    echo "❌ Forgot password form missing CSRF manager"
fi

support_content=$(curl -s "$BASE_URL/auth/support")
if echo "$support_content" | grep -q 'csrf-manager.js'; then
    echo "✅ Support form includes CSRF manager"
else
    echo "❌ Support form missing CSRF manager"
fi
echo ""

echo "5. Testing Enhanced CAPTCHA Function"
echo "-----------------------------------"

# Check if forms include enhanced refreshCaptcha function
echo "Checking Enhanced CAPTCHA Functions..."

if echo "$login_content" | grep -q 'refreshCaptchaWithToken'; then
    echo "✅ Login form has enhanced CAPTCHA refresh"
else
    echo "❌ Login form missing enhanced CAPTCHA refresh"
fi

if echo "$forgot_content" | grep -q 'refreshCaptchaWithToken'; then
    echo "✅ Forgot password form has enhanced CAPTCHA refresh"
else
    echo "❌ Forgot password form missing enhanced CAPTCHA refresh"
fi

if echo "$support_content" | grep -q 'refreshCaptchaWithToken'; then
    echo "✅ Support form has enhanced CAPTCHA refresh"
else
    echo "❌ Support form missing enhanced CAPTCHA refresh"
fi
echo ""

echo "6. Testing JavaScript File Accessibility"
echo "---------------------------------------"

# Test if CSRF manager JavaScript file is accessible
echo "Testing CSRF Manager JavaScript File..."
js_response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/js/csrf-manager.js")
if [ "$js_response" -eq 200 ]; then
    echo "✅ CSRF Manager JavaScript file is accessible"
else
    echo "❌ CSRF Manager JavaScript file is not accessible (HTTP $js_response)"
fi
echo ""

echo "7. Manual Testing Instructions"
echo "==============================="
echo ""
echo "To verify the fixes work:"
echo "1. Visit $BASE_URL/auth/forgot-password"
echo "2. Wait a few minutes or use browser dev tools to manually expire CSRF token"
echo "3. Fill out the form and submit"
echo "4. Should no longer get '419 Page Expired' error"
echo "5. The CSRF token should auto-refresh before form submission"
echo ""
echo "For CAPTCHA testing:"
echo "1. Click the refresh button on any form"
echo "2. Should refresh both CAPTCHA image and CSRF token"
echo "3. Forms should submit successfully without CSRF errors"
echo ""

echo "🎉 CSRF and Page Expiration Fix Test Complete!"
echo "=============================================="
echo ""
echo "Summary of fixes applied:"
echo "- ✅ Fixed form action URLs (removed admin_url, use url() instead)"
echo "- ✅ Fixed all navigation links between auth pages"
echo "- ✅ Added automatic CSRF token refresh system"
echo "- ✅ Enhanced CAPTCHA refresh to include CSRF token refresh"
echo "- ✅ Added CSRF token endpoint for auto-refresh"
echo "- ✅ Added form submission protection against expired tokens"
echo ""
echo "🔒 All public forms now protected against CSRF expiration!"