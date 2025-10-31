#!/bin/bash

# E2E Test Runner for Fasilitas
# This script helps run E2E tests with proper setup

echo "🚀 E2E Test Runner for Tambah Fasilitas"
echo "========================================"
echo ""

# Check if Laravel is running
echo "📡 Checking if Laravel is running..."
if curl -s http://localhost:8000 > /dev/null; then
    echo "✅ Laravel is running on http://localhost:8000"
else
    echo "❌ Laravel is not running!"
    echo "Please start Laravel with: php artisan serve"
    exit 1
fi

echo ""
echo "🧪 Running E2E Tests..."
echo ""

# Parse command line arguments
if [ "$1" == "positive" ]; then
    echo "Running POSITIVE test cases only..."
    npx playwright test e2e/fasilitas.spec.ts -g "Positive Test Cases" --reporter=list
elif [ "$1" == "negative" ]; then
    echo "Running NEGATIVE test cases only..."
    npx playwright test e2e/fasilitas.spec.ts -g "Negative Test Cases" --reporter=list
elif [ "$1" == "boundary" ]; then
    echo "Running BOUNDARY test cases only..."
    npx playwright test e2e/fasilitas.spec.ts -g "Boundary Test Cases" --reporter=list
elif [ "$1" == "ui" ]; then
    echo "Running tests in UI mode..."
    npx playwright test e2e/fasilitas.spec.ts --ui
elif [ "$1" == "debug" ]; then
    echo "Running tests in DEBUG mode..."
    npx playwright test e2e/fasilitas.spec.ts --debug
elif [ "$1" == "headed" ]; then
    echo "Running tests with browser visible..."
    npx playwright test e2e/fasilitas.spec.ts --headed --reporter=list
else
    echo "Running ALL test cases..."
    npx playwright test e2e/fasilitas.spec.ts --reporter=list
fi

echo ""
echo "✅ Test run completed!"
echo ""
echo "📊 To view detailed HTML report, run:"
echo "   npx playwright show-report"
