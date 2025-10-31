#!/bin/bash

echo "🚀 Deploying E2E Tests to vidi-test branch..."

# Check current branch
CURRENT_BRANCH=$(git branch --show-current)
echo "📍 Current branch: $CURRENT_BRANCH"

# If not on vidi-test, checkout to it
if [ "$CURRENT_BRANCH" != "vidi-test" ]; then
    echo "🔄 Switching to vidi-test branch..."
    git checkout vidi-test || git checkout -b vidi-test
fi

# Add all test files and controllers
echo "📦 Adding files..."
git add .github/workflows/playwright.yml
git add e2e/admin/fasilitas.spec.ts
git add e2e/admin/user-management.spec.ts
git add app/Http/Controllers/Api/KelolaFasilitas.php
git add app/Http/Controllers/Api/KelolaPengguna.php

# Show status
echo ""
echo "📋 Files to be committed:"
git status --short

# Commit changes
echo ""
echo "💾 Committing changes..."
git commit -m "Add E2E tests for Fasilitas and User Management with GitHub Actions workflow

Features:
- Add Playwright E2E tests for Fasilitas management (create, edit, delete)
- Add Playwright E2E tests for User management (create, update role, delete)
- Update KelolaFasilitas controller with proper validation and error handling
- Update KelolaPengguna controller with role validation and prevent self-deletion
- Add GitHub Actions workflow with Laravel setup, MySQL service, and Playwright tests
- Configure workflow to run on main, master, and vidi-test branches

Test Coverage:
Fasilitas Tests (10 test cases):
  - Tambah: 3 tests (1 positive, 2 negative)
  - Edit: 3 tests (1 positive, 2 negative)
  - Hapus: 4 tests (2 positive, 2 negative)

User Management Tests (10 test cases):
  - Tambah Pengguna: 3 tests (1 positive, 2 negative)
  - Ganti Role: 3 tests (1 positive, 2 negative)
  - Hapus Pengguna: 4 tests (2 positive, 2 negative)

CI/CD:
- Automated testing on push/PR
- Laravel environment setup with MySQL
- Playwright test execution
- Test report artifacts"

# Push to remote
echo ""
echo "🔼 Pushing to remote vidi-test branch..."
git push origin vidi-test

echo ""
echo "✅ Done! Check your repository on GitHub:"
echo "   https://github.com/TMTMPST/PMPL/tree/vidi-test"
echo ""
echo "🔍 GitHub Actions will run automatically."
echo "   View progress at: https://github.com/TMTMPST/PMPL/actions"
