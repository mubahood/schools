#!/bin/bash

echo "🧪 Testing Support Form Implementation..."
echo ""

# Test 1: Check if CAPTCHA generates
echo "1. Testing CAPTCHA generation..."
CAPTCHA_RESPONSE=$(curl -s -I http://localhost:8888/schools/auth/captcha | head -n 1)
if [[ $CAPTCHA_RESPONSE == *"200 OK"* ]]; then
    echo "   ✅ CAPTCHA endpoint working"
else
    echo "   ❌ CAPTCHA endpoint failed: $CAPTCHA_RESPONSE"
fi

# Test 2: Check if support page loads
echo ""
echo "2. Testing support page..."
SUPPORT_RESPONSE=$(curl -s -I http://localhost:8888/schools/auth/support | head -n 1)
if [[ $SUPPORT_RESPONSE == *"200 OK"* ]]; then
    echo "   ✅ Support page loads successfully"
else
    echo "   ❌ Support page failed: $SUPPORT_RESPONSE"
fi

# Test 3: Check database table
echo ""
echo "3. Testing database setup..."
php artisan tinker --execute="
\$count = \App\Models\SupportMessage::count();
echo 'Support messages table has: ' . \$count . ' records';
"

echo ""
echo "4. Testing model functionality..."
php artisan tinker --execute="
try {
    \$test = new \App\Models\SupportMessage();
    echo '✅ SupportMessage model loads correctly';
} catch (Exception \$e) {
    echo '❌ Model error: ' . \$e->getMessage();
}
"

echo ""
echo "🎯 Implementation test completed!"
echo ""
echo "📝 Manual testing steps:"
echo "   1. Visit: http://localhost:8888/schools/auth/support"
echo "   2. Fill out the form with valid data"
echo "   3. Enter the CAPTCHA number correctly"
echo "   4. Submit and check for success message"
echo "   5. Check database: select * from support_messages;"
echo ""